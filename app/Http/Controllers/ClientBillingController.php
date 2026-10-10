<?php

namespace App\Http\Controllers;

use App\Http\Resources\BillResource;
use App\Http\Resources\PaymentResource;
use App\Models\Bill;
use App\Models\Payment;
use App\Services\ClientStatement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * A client account's billing overview: balance, bills, payment history and
 * meals not billed yet. Always scoped to the account's own organization.
 */
class ClientBillingController extends Controller
{
    public function __invoke(Request $request, ClientStatement $statements): Response
    {
        $user = $request->user();
        $client = $user->loadMissing('client')->client;
        abort_if($client === null, 403);

        $bills = Bill::query()
            ->visibleTo($user)
            ->latest('period_start')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('billing/index', [
            'client' => ['id' => $client->id, 'name' => $client->name],
            'balance' => $statements->balance($client),
            'unbilled' => $statements->unbilled($client),
            'bills' => BillResource::collection($bills),
            'payments' => PaymentResource::collection(
                Payment::query()->visibleTo($user)->with('bill:id,bill_number')->latest('payment_date')->latest('id')->limit(10)->get(),
            )->resolve($request),
            'month' => now()->format('Y-m'),
        ]);
    }
}
