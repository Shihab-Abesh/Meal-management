<?php

namespace App\Http\Controllers;

use App\Enums\BillStatus;
use App\Enums\PaymentMethod;
use App\Http\Requests\BillRequest;
use App\Http\Resources\BillResource;
use App\Http\Resources\PaymentResource;
use App\Models\Bill;
use App\Models\Client;
use App\Services\BillingService;
use App\Services\Settings;
use App\Support\Money;
use App\Support\Search;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Vendor billing: generate bills from confirmed meals, follow what is paid
 * and due. Viewing needs financial or billing access; changes need billing.
 */
class BillController extends Controller
{
    public function __construct(private readonly BillingService $billing) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Bill::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'client' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::enum(BillStatus::class)],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);
        $today = now()->toDateString();

        $query = Bill::query()
            ->when($filters['search'] ?? null, fn (Builder $query, string $term) => $query->where(function (Builder $query) use ($term) {
                Search::apply($query, $term, ['bill_number']);
                $query->orWhereHas('client', fn (Builder $clients) => Search::apply($clients, $term, ['name']));
            }))
            ->when($filters['client'] ?? null, fn (Builder $query, int $id) => $query->where('client_id', $id))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->withStatus(BillStatus::from($status), $today))
            ->when($filters['month'] ?? null, function (Builder $query, string $month) {
                $start = CarbonImmutable::createFromFormat('!Y-m', $month);
                $query->where('period_start', '<=', $start->endOfMonth()->toDateString())
                    ->where('period_end', '>=', $start->toDateString());
            });

        $totals = (clone $query)
            ->selectRaw('COUNT(*) as bills, COALESCE(SUM(total_amount), 0) as billed, COALESCE(SUM(paid_amount), 0) as paid, COALESCE(SUM(due_amount), 0) as due')
            ->toBase()
            ->first();

        $bills = $query
            ->with('client:id,name')
            ->latest('period_start')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('bills/index', [
            'bills' => BillResource::collection($bills),
            'totals' => [
                'bills' => (int) $totals->bills,
                'billed' => Money::of($totals->billed),
                'paid' => Money::of($totals->paid),
                'due' => Money::of($totals->due),
            ],
            'filters' => [
                'search' => $filters['search'] ?? '',
                'client' => isset($filters['client']) ? (string) $filters['client'] : '',
                'status' => $filters['status'] ?? '',
                'month' => $filters['month'] ?? '',
            ],
            'clients' => $this->clientOptions(),
            'statuses' => BillStatus::options(),
            'can' => ['create' => $request->user()->can('create', Bill::class)],
        ]);
    }

    /**
     * The form previews the bill (meals and amount) for the chosen client
     * and period before it is generated.
     */
    public function create(Request $request, Settings $settings): Response
    {
        $this->authorize('create', Bill::class);

        $validated = $request->validate([
            'client' => ['nullable', 'integer'],
            'start' => ['nullable', 'date_format:Y-m-d'],
            'end' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $client = isset($validated['client']) ? Client::query()->find($validated['client']) : null;
        $lastMonth = now()->subMonthNoOverflow();
        $start = $validated['start'] ?? ($client ? $this->billing->nextPeriodStart($client) : null) ?? $lastMonth->startOfMonth()->toDateString();
        $end = $validated['end'] ?? CarbonImmutable::parse($start)->endOfMonth()->toDateString();

        $preview = null;

        if ($client && $start <= $end) {
            $overlap = Bill::query()
                ->where('client_id', $client->id)
                ->where('period_start', '<=', $end)
                ->where('period_end', '>=', $start)
                ->value('bill_number');

            $preview = [...$this->billing->calculate($client->id, $start, $end), 'overlaps' => $overlap];
        }

        return Inertia::render('bills/create', [
            'clients' => $this->clientOptions(),
            'values' => [
                'client_id' => $client ? (string) $client->id : '',
                'period_start' => $start,
                'period_end' => $end,
            ],
            'preview' => $preview,
            'lastBill' => $client ? $client->bills()->latest('period_end')->first(['bill_number', 'period_start', 'period_end']) : null,
            'dueDays' => $settings->billDueDays(),
            'today' => now()->toDateString(),
        ]);
    }

    public function store(BillRequest $request): RedirectResponse
    {
        $bill = $this->billing->generate(
            $request->client(),
            $request->validated('period_start'),
            $request->validated('period_end'),
            $request->user(),
            $request->validated('notes'),
        );

        $this->toast("Bill {$bill->bill_number} created.");

        return to_route('bills.show', $bill);
    }

    public function show(Request $request, Bill $bill): Response
    {
        $this->authorize('view', $bill);

        $bill->load(['client:id,name,contact_person,phone,email,address', 'payments.receiver:id,name', 'creator:id,name']);
        $user = $request->user();
        $current = $this->billing->calculate($bill->client_id, $bill->period_start->toDateString(), $bill->period_end->toDateString());

        return Inertia::render('bills/show', [
            'bill' => [
                ...BillResource::make($bill)->resolve($request),
                'client_details' => [
                    'contact_person' => $bill->client->contact_person,
                    'phone' => $bill->client->phone,
                    'email' => $bill->client->email,
                    'address' => $bill->client->address,
                ],
                'created_by' => $user->isVendorUser() ? $bill->creator?->name : null,
            ],
            'payments' => PaymentResource::collection($bill->payments)->resolve($request),
            // Meals changed since the bill was made (e.g. a cutoff override).
            'outOfDate' => $user->isVendorUser()
                && ($current['total_meals'] !== $bill->total_meals || Money::compare($current['total_amount'], $bill->total_amount) !== 0),
            'paymentMethods' => PaymentMethod::options(),
            'today' => now()->toDateString(),
            'can' => [
                'pay' => $user->can('pay', $bill),
                'update' => $user->can('update', $bill),
                'delete' => $user->can('delete', $bill) && $this->billing->canDelete($bill),
            ],
        ]);
    }

    public function recalculate(Request $request, Bill $bill): RedirectResponse
    {
        $this->authorize('update', $bill);

        $bill = $this->billing->recalculate($bill, $request->user());

        $this->toast("Bill {$bill->bill_number} recalculated.");

        return back();
    }

    public function destroy(Request $request, Bill $bill): RedirectResponse
    {
        $this->authorize('delete', $bill);

        $this->billing->delete($bill, $request->user());

        $this->toast("Bill {$bill->bill_number} deleted.");

        return to_route('bills.index');
    }

    /**
     * @return Collection<int, array{value: string, label: string}>
     */
    private function clientOptions()
    {
        return Client::query()->orderBy('name')->get(['id', 'name'])
            ->map(fn (Client $client) => ['value' => (string) $client->id, 'label' => $client->name]);
    }
}
