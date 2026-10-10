<?php

namespace App\Services;

use App\Enums\Permission;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\MealEntry;
use App\Models\Order;
use App\Models\User;

/**
 * Figures and recent activity for the client detail page.
 */
class ClientOverview
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly ClientStatement $statements,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function for(Client $client, User $viewer): array
    {
        $seesPrices = $viewer->hasPermission(Permission::ViewFinancials) || $viewer->hasPermission(Permission::ManagePricing);

        return [
            'prices' => $seesPrices ? $this->currentPrices($client) : null,
            'stats' => [
                'employees' => $client->employees()->active()->count(),
                'todays_meals' => MealEntry::query()->confirmed()
                    ->where('client_id', $client->id)
                    ->where('meal_date', now()->toDateString())
                    ->count(),
                'monthly_meals' => MealEntry::query()->confirmed()
                    ->where('client_id', $client->id)
                    ->whereBetween('meal_date', [now()->startOfMonth()->toDateString(), now()->toDateString()])
                    ->count(),
                // Financial figures only for users allowed to see them.
                'outstanding_balance' => $viewer->hasPermission(Permission::ViewFinancials) ? $this->statements->balance($client)['due'] : null,
            ],
            'recent_orders' => $this->recentOrders($client, $viewer),
            'recent_activity' => $this->recentActivity($client),
        ];
    }

    /**
     * The latest orders, with amounts only for users with financial access.
     *
     * @return list<array<string, mixed>>
     */
    private function recentOrders(Client $client, User $viewer): array
    {
        $showAmounts = $viewer->hasPermission(Permission::ViewFinancials);

        return Order::query()
            ->where('client_id', $client->id)
            ->latest('order_date')
            ->orderByRaw("CASE meal_type WHEN 'lunch' THEN 0 ELSE 1 END")
            ->limit(6)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'order_date' => $order->order_date->toDateString(),
                'meal_type' => $order->meal_type->value,
                'meal_type_label' => $order->meal_type->label(),
                'quantity' => $order->quantity,
                'amount' => $showAmounts ? $order->amount : null,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
            ])
            ->all();
    }

    /**
     * Price per meal in effect today for each meal the client receives.
     *
     * @return array<string, array{price: string, source: string}>
     */
    private function currentPrices(Client $client): array
    {
        $today = now()->toDateString();
        $prices = [];

        foreach ($client->mealTypes() as $type) {
            $prices[$type->value] = $this->pricing->priceFor($client, $type, $today);
        }

        return $prices;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentActivity(Client $client): array
    {
        return ActivityLog::query()
            ->with('user:id,name')
            ->where('client_id', $client->id)
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (ActivityLog $log) => [
                'id' => $log->id,
                'description' => $log->description,
                'module' => $log->module,
                'user' => $log->user?->name,
                'created_at' => $log->created_at?->toIso8601String(),
            ])
            ->all();
    }
}
