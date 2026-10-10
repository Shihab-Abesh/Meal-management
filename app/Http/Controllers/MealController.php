<?php

namespace App\Http\Controllers;

use App\Enums\MealEntryStatus;
use App\Enums\MealType;
use App\Enums\Permission;
use App\Http\Requests\MealSheetRequest;
use App\Http\Resources\MealEntryResource;
use App\Models\Client;
use App\Models\Employee;
use App\Models\MealEntry;
use App\Models\User;
use App\Services\MealEntryService;
use App\Services\MealSheetService;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Daily meals: the day overview, the meal sheet, the calendar and history.
 * Vendor users need the meals permission; client accounts only ever see and
 * change their own organization.
 */
class MealController extends Controller
{
    /**
     * Vendor users without a client chosen see the day overview; everyone
     * else sees the meal sheet.
     */
    public function index(Request $request, MealSheetService $sheets): Response
    {
        $user = $request->user();
        $this->authorizeMeals($user);

        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'client' => ['nullable', 'integer'],
        ]);

        $date = $validated['date'] ?? now()->toDateString();
        $today = now()->toDateString();
        $maxDate = now()->addDays(MealSheetRequest::MAX_DAYS_AHEAD)->toDateString();
        $clientId = $user->isClientAdmin() ? $user->client_id : ($validated['client'] ?? null);

        if (! $clientId) {
            return Inertia::render('meals/overview', [
                'date' => $date,
                'today' => $today,
                'maxDate' => $maxDate,
                'clients' => $sheets->overview($date, $user),
            ]);
        }

        $client = Client::query()->findOrFail($clientId);
        $this->authorize('manageMeals', $client);

        return Inertia::render('meals/sheet', [
            ...$sheets->sheet($client, $date, $user),
            'today' => $today,
            'maxDate' => $maxDate,
            'clients' => $user->isVendorUser()
                ? Client::active()->orderBy('name')->get(['id', 'name'])->map(fn (Client $c) => ['value' => (string) $c->id, 'label' => $c->name])
                : null,
        ]);
    }

    public function store(MealSheetRequest $request, MealEntryService $meals): RedirectResponse
    {
        $client = $request->client();
        $date = $request->validated('date');

        $result = $meals->saveSheet($client, $date, $request->selections(), $request->user(), $request->boolean('override'));

        $changed = collect($result)->sum(fn (array $type) => $type['added'] + $type['removed']);
        $summary = collect($result)
            ->map(fn (array $type, string $key) => MealType::from($key)->label().' '.$type['total'])
            ->implode(', ');

        $this->toast($changed > 0 ? "Meals confirmed: {$summary}." : "No changes. {$summary}.", $changed > 0 ? 'success' : 'info');

        return to_route('meals.index', $request->user()->isVendorUser() ? ['client' => $client->id, 'date' => $date] : ['date' => $date]);
    }

    /**
     * Month view of confirmed meals per day.
     */
    public function calendar(Request $request): Response
    {
        $user = $request->user();
        $this->authorizeMeals($user);

        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'client' => ['nullable', 'integer'],
        ]);

        $month = CarbonImmutable::createFromFormat('!Y-m', $validated['month'] ?? now()->format('Y-m'));
        $clientId = $user->isClientAdmin() ? $user->client_id : ($validated['client'] ?? null);

        $rows = MealEntry::query()
            ->visibleTo($user)
            ->confirmed()
            ->when($clientId, fn (Builder $query) => $query->where('client_id', $clientId))
            ->whereBetween('meal_date', [$month->startOfMonth()->toDateString(), $month->endOfMonth()->toDateString()])
            ->selectRaw('meal_date, meal_type, COUNT(*) as meals')
            ->groupBy('meal_date', 'meal_type')
            ->toBase()
            ->get();

        $days = [];

        foreach ($rows as $row) {
            $day = substr((string) $row->meal_date, 0, 10);
            $days[$day] ??= ['lunch' => 0, 'dinner' => 0];
            $days[$day][$row->meal_type] = (int) $row->meals;
        }

        return Inertia::render('meals/calendar', [
            'month' => $month->format('Y-m'),
            'today' => now()->toDateString(),
            'days' => (object) $days,
            'totals' => [
                'lunch' => array_sum(array_column($days, 'lunch')),
                'dinner' => array_sum(array_column($days, 'dinner')),
            ],
            'client' => $clientId ? (string) $clientId : '',
            'clients' => $user->isVendorUser() ? $this->clientOptions() : null,
        ]);
    }

    /**
     * Every meal entry, filterable by client, employee, date, meal and status.
     */
    public function history(Request $request): Response
    {
        $user = $request->user();
        $this->authorizeMeals($user);

        $filters = $request->validate([
            'client' => ['nullable', 'integer'],
            'employee' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'meal_type' => ['nullable', Rule::enum(MealType::class)],
            'status' => ['nullable', Rule::enum(MealEntryStatus::class)],
        ]);

        $from = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? now()->toDateString();
        $clientId = $user->isVendorUser() ? ($filters['client'] ?? null) : null;
        $showAmounts = $user->isClientAdmin() || $user->hasPermission(Permission::ViewFinancials);

        $query = MealEntry::query()
            ->visibleTo($user)
            ->when($clientId, fn (Builder $query, int $id) => $query->where('client_id', $id))
            ->when($filters['employee'] ?? null, fn (Builder $query, int $id) => $query->where('employee_id', $id))
            ->when($filters['search'] ?? null, fn (Builder $query, string $term) => $query->whereHas(
                'employee',
                fn (Builder $employees) => $employees->search($term),
            ))
            ->whereBetween('meal_date', [$from, $to])
            ->when($filters['meal_type'] ?? null, fn (Builder $query, string $type) => $query->where('meal_type', $type))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));

        $summary = (clone $query)->confirmed()
            ->selectRaw('COUNT(*) as meals, COALESCE(SUM(unit_price), 0) as amount')
            ->first();

        $entries = $query
            ->with(['employee:id,name,employee_code', 'client:id,name', 'confirmer:id,name'])
            ->orderByDesc('meal_date')
            ->orderBy('meal_type')
            ->orderBy('id')
            ->paginate(30)
            ->withQueryString();

        $selectedEmployee = isset($filters['employee'])
            ? Employee::query()->visibleTo($user)->whereKey($filters['employee'])->first(['id', 'name', 'employee_code'])
            : null;

        return Inertia::render('meals/history', [
            'entries' => MealEntryResource::collection($entries),
            'summary' => [
                'meals' => (int) ($summary->meals ?? 0),
                'amount' => $showAmounts ? Money::of($summary->amount ?? 0) : null,
            ],
            'filters' => [
                'client' => $clientId ? (string) $clientId : '',
                'employee' => $selectedEmployee ? (string) $selectedEmployee->id : '',
                'search' => $filters['search'] ?? '',
                'from' => $from,
                'to' => $to,
                'meal_type' => $filters['meal_type'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
            'employee' => $selectedEmployee ? ['id' => $selectedEmployee->id, 'name' => $selectedEmployee->name, 'code' => $selectedEmployee->employee_code] : null,
            'clients' => $user->isVendorUser() ? $this->clientOptions() : null,
            'mealTypes' => MealType::options(),
            'statuses' => MealEntryStatus::options(),
        ]);
    }

    private function authorizeMeals(User $user): void
    {
        abort_unless($user->hasPermission(Permission::ManageMeals) || $user->isClientAdmin(), 403);
    }

    /**
     * @return Collection<int, array{value: string, label: string}>
     */
    private function clientOptions(): Collection
    {
        return Client::query()->orderBy('name')->get(['id', 'name'])
            ->map(fn (Client $client) => ['value' => (string) $client->id, 'label' => $client->name]);
    }
}
