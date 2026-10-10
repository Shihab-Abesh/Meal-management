<?php

namespace App\Http\Controllers;

use App\Http\Resources\BillResource;
use App\Http\Resources\PaymentResource;
use App\Models\Bill;
use App\Models\Client;
use App\Services\ClientStatement;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Monthly statement for one client: meals and amount per day, the bills
 * covering the month and the payments made in it. Client accounts always
 * see their own organization; vendor users need financial access.
 */
class StatementController extends Controller
{
    public function __invoke(Request $request, ClientStatement $statements): Response
    {
        $user = $request->user();

        $validated = $request->validate([
            'client' => ['nullable', 'integer'],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        // Never trust a client id from a client account.
        $clientId = $user->isClientAdmin() ? $user->client_id : ($validated['client'] ?? null);
        $client = $clientId ? Client::withTrashed()->find($clientId) : null;
        $month = CarbonImmutable::createFromFormat('!Y-m', $validated['month'] ?? now()->format('Y-m'));

        if ($client) {
            $this->authorize('viewFor', [Bill::class, $client]);
        } else {
            $this->authorize('viewAny', Bill::class);
        }

        $statement = $client ? $statements->month($client, $month) : null;

        return Inertia::render('billing/statement', [
            'client' => $client ? ['id' => $client->id, 'name' => $client->name, 'address' => $client->address] : null,
            'month' => $month->format('Y-m'),
            'today' => now()->toDateString(),
            'statement' => $statement ? [
                'days' => $statement['days'],
                'totals' => $statement['totals'],
                'bills' => BillResource::collection($statement['bills'])->resolve($request),
                'payments' => PaymentResource::collection($statement['payments'])->resolve($request),
            ] : null,
            'balance' => $client ? $statements->balance($client) : null,
            'clients' => $user->isVendorUser()
                ? Client::query()->orderBy('name')->get(['id', 'name'])->map(fn (Client $c) => ['value' => (string) $c->id, 'label' => $c->name])
                : null,
        ]);
    }
}
