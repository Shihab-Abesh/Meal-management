<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Http\Requests\PaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Bill;
use App\Models\Client;
use App\Models\Payment;
use App\Services\BillingService;
use App\Support\Money;
use App\Support\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Payments received from clients.
 */
class PaymentController extends Controller
{
    public function __construct(private readonly BillingService $billing) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Bill::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'client' => ['nullable', 'integer'],
            'method' => ['nullable', Rule::enum(PaymentMethod::class)],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $from = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? now()->toDateString();

        $query = Payment::query()
            ->whereBetween('payment_date', [$from, $to])
            ->when($filters['client'] ?? null, fn (Builder $query, int $id) => $query->where('client_id', $id))
            ->when($filters['method'] ?? null, fn (Builder $query, string $method) => $query->where('method', $method))
            ->when($filters['search'] ?? null, fn (Builder $query, string $term) => $query->where(function (Builder $query) use ($term) {
                Search::apply($query, $term, ['reference', 'note']);
                $query->orWhereHas('bill', fn (Builder $bills) => Search::apply($bills, $term, ['bill_number']));
            }));

        $summary = (clone $query)->selectRaw('COUNT(*) as payments, COALESCE(SUM(amount), 0) as amount')->toBase()->first();
        $byMethod = (clone $query)->selectRaw('method, COALESCE(SUM(amount), 0) as amount')->groupBy('method')->toBase()->pluck('amount', 'method');

        $payments = $query
            ->with(['bill:id,bill_number', 'client:id,name', 'receiver:id,name'])
            ->latest('payment_date')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('payments/index', [
            'payments' => PaymentResource::collection($payments),
            'summary' => [
                'payments' => (int) $summary->payments,
                'amount' => Money::of($summary->amount),
                'by_method' => collect(PaymentMethod::cases())
                    ->filter(fn (PaymentMethod $method) => isset($byMethod[$method->value]))
                    ->map(fn (PaymentMethod $method) => ['method' => $method->value, 'label' => $method->label(), 'amount' => Money::of($byMethod[$method->value])])
                    ->values(),
            ],
            'filters' => [
                'search' => $filters['search'] ?? '',
                'client' => isset($filters['client']) ? (string) $filters['client'] : '',
                'method' => $filters['method'] ?? '',
                'from' => $from,
                'to' => $to,
            ],
            'clients' => Client::query()->orderBy('name')->get(['id', 'name'])
                ->map(fn (Client $client) => ['value' => (string) $client->id, 'label' => $client->name]),
            'methods' => PaymentMethod::options(),
            'canManage' => $request->user()->can('create', Bill::class),
        ]);
    }

    public function store(PaymentRequest $request, Bill $bill): RedirectResponse
    {
        $payment = $this->billing->recordPayment($bill, $request->validated(), $request->user());

        $this->toast('Payment of '.config('catering.currency.symbol').number_format((float) $payment->amount, 2).' recorded.');

        return back();
    }

    public function destroy(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('pay', $payment->bill);

        $this->billing->removePayment($payment, $request->user());

        $this->toast('Payment removed. The amount is due again.');

        return back();
    }
}
