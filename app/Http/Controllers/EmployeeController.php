<?php

namespace App\Http\Controllers;

use App\Enums\MealPreference;
use App\Enums\MealType;
use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\MealEntryResource;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Employee;
use App\Models\MealEntry;
use App\Models\User;
use App\Services\EmployeeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Employees, shared by vendor users (all clients) and client accounts
 * (their own organization only, enforced by EmployeePolicy and the
 * visibleTo scope).
 */
class EmployeeController extends Controller
{
    private const SORTS = ['name', 'employee_code', 'department', 'joining_date', 'created_at'];

    public function __construct(private readonly EmployeeService $employees) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Employee::class);

        $user = $request->user();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'client' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::enum(RecordStatus::class)],
            'department' => ['nullable', 'string', 'max:100'],
            'meal_preference' => ['nullable', Rule::enum(MealPreference::class)],
            'meal' => ['nullable', Rule::enum(MealType::class)],
            'sort' => ['nullable', Rule::in(self::SORTS)],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $sort = $filters['sort'] ?? 'name';
        $direction = $filters['direction'] ?? ($sort === 'created_at' ? 'desc' : 'asc');
        // Client accounts are always limited to their own organization by visibleTo().
        $clientFilter = $user->isVendorUser() ? ($filters['client'] ?? null) : null;

        $employees = Employee::query()
            ->visibleTo($user)
            ->when($user->isVendorUser(), fn (Builder $query) => $query->with('client:id,name'))
            ->when($clientFilter, fn (Builder $query, int $clientId) => $query->where('client_id', $clientId))
            ->search($filters['search'] ?? null)
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['department'] ?? null, fn (Builder $query, string $department) => $query->where('department', $department))
            ->when($filters['meal_preference'] ?? null, fn (Builder $query, string $preference) => $query->where('meal_preference', $preference))
            ->when($filters['meal'] ?? null, fn (Builder $query, string $meal) => $query->takes(MealType::from($meal)))
            ->orderBy($sort, $direction)
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('employees/index', [
            'employees' => EmployeeResource::collection($employees),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'client' => $clientFilter ? (string) $clientFilter : '',
                'status' => $filters['status'] ?? '',
                'department' => $filters['department'] ?? '',
                'meal_preference' => $filters['meal_preference'] ?? '',
                'meal' => $filters['meal'] ?? '',
                'sort' => $sort,
                'direction' => $direction,
            ],
            'clients' => $user->isVendorUser() ? $this->clientOptions(includeInactive: true) : null,
            'departments' => Employee::query()
                ->visibleTo($user)
                ->when($clientFilter, fn (Builder $query, int $clientId) => $query->where('client_id', $clientId))
                ->whereNotNull('department')
                ->distinct()
                ->orderBy('department')
                ->limit(200)
                ->pluck('department'),
            'mealPreferences' => MealPreference::options(),
            'mealTypes' => MealType::options(),
            'can' => ['create' => $user->can('create', Employee::class)],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Employee::class);

        $user = $request->user();

        return Inertia::render('employees/create', [
            ...$this->formOptions($user),
            'selectedClient' => $user->isVendorUser() ? $request->integer('client') ?: null : $user->client_id,
        ]);
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        $client = $request->client();
        $this->authorize('createFor', [Employee::class, $client]);

        $employee = $this->employees->create($client, $request->employeeAttributes(), $request->user());

        $this->toast("{$employee->name} has been added.");

        return $request->boolean('add_another')
            ? to_route('employees.create', ['client' => $client->id])
            : to_route('employees.show', $employee);
    }

    public function show(Request $request, Employee $employee): Response
    {
        $this->authorize('view', $employee);

        $employee->load('client:id,name');

        return Inertia::render('employees/show', [
            'employee' => [
                ...EmployeeResource::make($employee)->resolve($request),
                'notes' => $employee->notes,
                'created_at' => $employee->created_at?->toIso8601String(),
                'updated_at' => $employee->updated_at?->toIso8601String(),
            ],
            'history' => $this->history($employee),
            'meals' => MealEntryResource::collection(
                MealEntry::query()->where('employee_id', $employee->id)->latest('meal_date')->orderBy('meal_type')->limit(15)->get(),
            )->resolve($request),
            'mealsThisMonth' => MealEntry::query()->confirmed()
                ->where('employee_id', $employee->id)
                ->whereBetween('meal_date', [now()->startOfMonth()->toDateString(), now()->toDateString()])
                ->count(),
            'can' => [
                'update' => $request->user()->can('update', $employee),
                'meals' => $request->user()->hasPermission(Permission::ManageMeals) || $request->user()->isClientAdmin(),
            ],
        ]);
    }

    public function edit(Request $request, Employee $employee): Response
    {
        $this->authorize('update', $employee);

        $employee->load('client:id,name');

        return Inertia::render('employees/edit', [
            ...$this->formOptions($request->user()),
            'employee' => [...EmployeeResource::make($employee)->resolve($request), 'notes' => $employee->notes],
        ]);
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->employees->update($employee, $request->employeeAttributes());

        $this->toast('Employee details saved.');

        return to_route('employees.show', $employee);
    }

    public function updateStatus(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $validated = $request->validate(['status' => ['required', Rule::enum(RecordStatus::class)]]);
        $status = RecordStatus::from($validated['status']);

        $this->employees->setStatus($employee, $status);

        $this->toast($status === RecordStatus::Active ? "{$employee->name} is active." : "{$employee->name} has been deactivated.");

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(User $user): array
    {
        return [
            'clients' => $user->isVendorUser() ? $this->clientOptions() : null,
            'mealPreferences' => MealPreference::options(),
            'statuses' => RecordStatus::options(),
        ];
    }

    /**
     * @return Collection<int, array{value: string, label: string}>
     */
    private function clientOptions(bool $includeInactive = false): Collection
    {
        return Client::query()
            ->when(! $includeInactive, fn (Builder $query) => $query->active())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Client $client) => ['value' => (string) $client->id, 'label' => $client->name]);
    }

    /**
     * Changes made to the employee record, newest first.
     *
     * @return list<array<string, mixed>>
     */
    private function history(Employee $employee): array
    {
        return ActivityLog::query()
            ->with('user:id,name')
            ->where('subject_type', $employee->getMorphClass())
            ->where('subject_id', $employee->id)
            ->latest('id')
            ->limit(20)
            ->get()
            ->map(fn (ActivityLog $log) => [
                'id' => $log->id,
                'description' => $log->description,
                'action' => $log->action,
                'user' => $log->user?->name,
                'created_at' => $log->created_at?->toIso8601String(),
            ])
            ->all();
    }
}
