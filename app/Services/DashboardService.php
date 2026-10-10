<?php

namespace App\Services;

use App\Enums\MealType;
use App\Enums\OrderStatus;
use App\Enums\Permission;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\MealEntry;
use App\Models\Order;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;

class DashboardService
{
    public function __construct(private readonly ClientStatement $statements) {}

    /**
     * Summary cards for the signed-in user's dashboard.
     *
     * Each role only receives the figures it is allowed to see; revenue is
     * never sent to riders, clients or staff without financial access.
     * Values are wired to live data as each module is built.
     *
     * @return list<array{key: string, label: string, value: int|float|string, format: string, icon: string, hint: string}>
     */
    public function statsFor(User $user): array
    {
        return match ($user->role) {
            UserRole::VendorAdmin, UserRole::VendorStaff => $this->vendorStats($user),
            UserRole::ClientAdmin => $this->clientStats($user),
            UserRole::Rider => $this->riderStats($user),
        };
    }

    /**
     * Today's orders for the vendor dashboard, or null when the user cannot
     * see orders.
     *
     * @return list<array<string, mixed>>|null
     */
    public function todaysOrders(User $user): ?array
    {
        if (! $user->hasPermission(Permission::ManageOrders) && ! $user->hasPermission(Permission::ManageDeliveries)) {
            return null;
        }

        return Order::query()
            ->with(['client:id,name', 'delivery.rider:id,name'])
            ->where('order_date', now()->toDateString())
            ->where('status', '!=', OrderStatus::Cancelled)
            ->orderByRaw("CASE meal_type WHEN 'lunch' THEN 0 ELSE 1 END")
            ->orderBy('delivery_time')
            ->orderBy('client_id')
            ->limit(10)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'client' => $order->client->name,
                'meal_type' => $order->meal_type->value,
                'quantity' => $order->quantity,
                'rider' => $order->delivery?->rider?->name,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
            ])
            ->all();
    }

    /**
     * Today's meals for a client account: count, cutoff and order progress
     * for each meal the organization receives.
     *
     * @return list<array<string, mixed>>
     */
    public function clientToday(User $user, MealCutoff $cutoff): array
    {
        $client = $user->loadMissing('client')->client;

        if (! $client) {
            return [];
        }

        $today = now()->toDateString();
        $counts = $this->todaysMeals()->where('client_id', $client->id)
            ->selectRaw('meal_type, COUNT(*) as meals')
            ->groupBy('meal_type')
            ->toBase()
            ->pluck('meals', 'meal_type');
        $orders = Order::query()->where('client_id', $client->id)->where('order_date', $today)->get()
            ->keyBy(fn (Order $order) => $order->meal_type->value);

        return array_map(fn (MealType $type) => [
            'meal_type' => $type->value,
            'label' => $type->label(),
            'count' => (int) ($counts[$type->value] ?? 0),
            'cutoff' => $cutoff->time($client, $type),
            'closed' => $cutoff->isClosed($client, $type, $today),
            'order_status' => $orders->get($type->value)?->status->value,
            'order_status_label' => $orders->get($type->value)?->status->label(),
        ], $client->mealTypes());
    }

    /**
     * @return list<array{key: string, label: string, value: int|float|string, format: string, icon: string, hint: string}>
     */
    private function vendorStats(User $user): array
    {
        $stats = [
            $this->stat('total_clients', 'Total Clients', Client::active()->count(), 'building', 'Active client organizations'),
            $this->stat(
                'total_employees',
                'Total Employees',
                Employee::active()->whereIn('client_id', Client::active()->select('id'))->count(),
                'users',
                'Across active clients',
            ),
            $this->stat('todays_meals', "Today's Meals", $this->todaysMeals()->count(), 'utensils', 'Lunch and dinner combined'),
        ];

        if ($user->hasPermission(Permission::ViewFinancials)) {
            $revenue = Money::of($this->todaysMeals()->sum('unit_price'));
            $stats[] = $this->stat('todays_revenue', "Today's Revenue", $revenue, 'banknote', 'From confirmed meals', 'currency');
        }

        $pending = Order::query()->open()->where('order_date', now()->toDateString())->count();
        $stats[] = $this->stat('pending_deliveries', 'Pending Deliveries', $pending, 'truck', "Today's orders not delivered yet");

        return $stats;
    }

    /**
     * @return list<array{key: string, label: string, value: int|float|string, format: string, icon: string, hint: string}>
     */
    private function clientStats(User $user): array
    {
        $client = $user->loadMissing('client')->client;

        return [
            $this->stat('total_employees', 'Employees', Employee::active()->where('client_id', $user->client_id)->count(), 'users', 'Active employees'),
            $this->stat('todays_meals', "Today's Meals", $this->todaysMeals()->where('client_id', $user->client_id)->count(), 'utensils', 'Lunch and dinner combined'),
            $this->stat(
                'monthly_meals',
                'This Month',
                MealEntry::query()
                    ->confirmed()
                    ->where('client_id', $user->client_id)
                    ->whereBetween('meal_date', [now()->startOfMonth()->toDateString(), now()->toDateString()])
                    ->count(),
                'calendar',
                'Meals so far this month',
            ),
            $this->stat(
                'balance_due',
                'Balance Due',
                $client ? $this->statements->balance($client)['due'] : '0.00',
                'wallet',
                'Unpaid bills',
                'currency',
            ),
        ];
    }

    /**
     * @return Builder<MealEntry>
     */
    private function todaysMeals(): Builder
    {
        return MealEntry::query()->confirmed()->where('meal_date', now()->toDateString());
    }

    /**
     * @return list<array{key: string, label: string, value: int|float|string, format: string, icon: string, hint: string}>
     */
    private function riderStats(User $user): array
    {
        $riderId = $user->loadMissing('rider')->rider?->id ?? 0;
        $today = Delivery::query()
            ->where('rider_id', $riderId)
            ->whereHas('order', fn (Builder $query) => $query->where('order_date', now()->toDateString()));

        return [
            $this->stat('todays_deliveries', "Today's Deliveries", (clone $today)->count(), 'truck', 'Assigned to you today'),
            $this->stat(
                'pending_deliveries',
                'Pending',
                Delivery::query()->where('rider_id', $riderId)->whereHas('order', fn (Builder $query) => $query->whereIn('status', [
                    OrderStatus::Assigned, OrderStatus::PickedUp, OrderStatus::OnTheWay,
                ]))->count(),
                'clock',
                'Not delivered yet',
            ),
            $this->stat(
                'completed_deliveries',
                'Delivered',
                (clone $today)->whereHas('order', fn (Builder $query) => $query->where('status', OrderStatus::Delivered))->count(),
                'circle-check',
                'Completed today',
            ),
        ];
    }

    /**
     * @return array{key: string, label: string, value: int|float|string, format: string, icon: string, hint: string}
     */
    private function stat(string $key, string $label, int|float|string $value, string $icon, string $hint, string $format = 'number'): array
    {
        return compact('key', 'label', 'value', 'format', 'icon', 'hint');
    }
}
