<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use App\Notifications\Vendor\BillsOverdue;
use App\Notifications\Vendor\ClientOrderCancelled;
use App\Notifications\Vendor\ClientOrderChanged;
use App\Notifications\Vendor\ClientOrderPlaced;
use App\Notifications\Vendor\DeliveryCompleted;
use App\Notifications\Vendor\VendorAlert;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

/**
 * In-app alerts for the vendor team: client orders, completed deliveries
 * and overdue bills. Each alert goes to the active vendor users who can act
 * on it and have not switched that category off; nobody is alerted about
 * their own action.
 *
 * Alerts about one order work like a conversation thread: while the alert
 * is unread, a newer change replaces it, so a client who edits the sheet
 * five times produces one alert that tells the whole story ("22 → 25").
 */
class VendorNotifier
{
    private const ORDER_ALERTS = [ClientOrderPlaced::class, ClientOrderChanged::class, ClientOrderCancelled::class];

    /**
     * A client confirmed, changed or removed the meals behind an order.
     * Called inside the meal-confirmation transaction.
     *
     * @param  array{quantity: int, status: OrderStatus}|null  $before  null for a new order
     */
    public function clientOrder(Order $order, ?array $before, User $actor): void
    {
        if (! $actor->isClientAdmin()) {
            return;
        }

        $event = $this->orderEvent($order, $before);

        if ($event === null) {
            return;
        }

        $order->loadMissing('client:id,name');

        foreach ($this->recipients([Permission::ManageOrders], 'orders', $actor) as $user) {
            $this->replaceOrderAlert($user, $order, $event, $before['quantity'] ?? 0, $actor);
        }
    }

    public function deliveryCompleted(Order $order, User $actor): void
    {
        $order->loadMissing(['client:id,name', 'delivery.rider:id,name']);
        $rider = $order->delivery?->rider?->name;

        $alert = new DeliveryCompleted(
            "Delivered: {$order->client->name} {$this->meal($order)}",
            trim("{$order->quantity} meals".($rider ? " delivered by {$rider}" : '').' at '.now()->format('g:i A')),
            route('orders.show', $order, false),
            ['order_id' => $order->id, 'client' => $order->client->name, 'quantity' => $order->quantity],
        );

        foreach ($this->recipients([Permission::ManageDeliveries], 'deliveries', $actor) as $user) {
            $this->send($user, $alert);
        }
    }

    /**
     * Bills that just became overdue, as one summary alert.
     */
    public function billsOverdue(int $count, string $due): void
    {
        if ($count === 0) {
            return;
        }

        $alert = new BillsOverdue(
            $count === 1 ? 'A bill is now overdue' : "{$count} bills are now overdue",
            config('catering.currency.symbol').number_format((float) $due, 2).' is still owed. Follow up with the clients.',
            route('bills.index', ['status' => 'overdue'], false),
            ['count' => $count, 'due' => $due],
        );

        foreach ($this->recipients([Permission::ManageBilling, Permission::ViewFinancials], 'billing') as $user) {
            $this->send($user, $alert);
        }
    }

    /**
     * @param  array{quantity: int, status: OrderStatus}|null  $before
     * @return 'placed'|'changed'|'cancelled'|null
     */
    private function orderEvent(Order $order, ?array $before): ?string
    {
        $cancelled = $order->status === OrderStatus::Cancelled;

        return match (true) {
            $before === null => $cancelled ? null : 'placed',
            $before['status'] === OrderStatus::Cancelled && ! $cancelled => 'placed',
            $cancelled && $before['status'] !== OrderStatus::Cancelled => 'cancelled',
            ! $cancelled && $before['quantity'] !== $order->quantity => 'changed',
            default => null,
        };
    }

    /**
     * Fold the new event into the user's unread alert about the same order.
     */
    private function replaceOrderAlert(User $user, Order $order, string $event, int $from, User $actor): void
    {
        $existing = $this->unreadOrderAlert($user, $order);

        if ($existing) {
            $earlier = (int) ($existing->data['from_quantity'] ?? $existing->data['quantity'] ?? $from);

            [$event, $from] = match ([$existing->type, $event]) {
                // The vendor has not seen the order yet: it is still simply a new order.
                [ClientOrderPlaced::class, 'changed'] => ['placed', 0],
                [ClientOrderPlaced::class, 'cancelled'] => ['cancelled', $earlier],
                // Keep the quantity the vendor last knew about.
                [ClientOrderChanged::class, 'changed'] => ['changed', $earlier],
                [ClientOrderChanged::class, 'cancelled'] => ['cancelled', $earlier],
                [ClientOrderCancelled::class, 'placed'] => ['changed', $earlier],
                default => [$event, $from],
            };

            $existing->delete();
        }

        // Changed back to what the vendor already knew: nothing to tell.
        if ($event === 'changed' && $from === $order->quantity) {
            return;
        }

        $this->send($user, $this->orderAlert($order, $event, $from, $actor));
    }

    /**
     * Each recipient gets their own copy with a time-ordered id, so alerts
     * created in the same second still list newest first.
     */
    private function send(User $user, VendorAlert $alert): void
    {
        $copy = clone $alert;
        $copy->id = (string) Str::uuid7();

        $user->notify($copy);
    }

    private function unreadOrderAlert(User $user, Order $order): ?DatabaseNotification
    {
        return $user->unreadNotifications()
            ->whereIn('type', self::ORDER_ALERTS)
            ->where('created_at', '>=', now()->subDays(3))
            ->get()
            ->first(fn (DatabaseNotification $notification) => ($notification->data['order_id'] ?? null) === $order->id);
    }

    private function orderAlert(Order $order, string $event, int $from, User $actor): VendorAlert
    {
        $client = $order->client->name;
        $meal = $this->meal($order);
        $day = $order->order_date->format('D, d M');
        $by = "{$order->order_number} · by {$actor->name}";
        $url = route('orders.show', $order, false);
        $meta = [
            'order_id' => $order->id,
            'client_id' => $order->client_id,
            'client' => $client,
            'meal_type' => $order->meal_type->value,
            'date' => $order->order_date->toDateString(),
            'quantity' => $order->quantity,
            'from_quantity' => $event === 'placed' ? null : $from,
        ];

        return match ($event) {
            'placed' => new ClientOrderPlaced("New {$meal} order from {$client}", "{$order->quantity} meals for {$day} · {$by}", $url, $meta),
            'changed' => new ClientOrderChanged("{$client} changed their {$meal} order", "{$from} → {$order->quantity} meals for {$day} · {$by}", $url, $meta),
            'cancelled' => new ClientOrderCancelled("{$client} cancelled their {$meal} order", "{$from} meals for {$day} were removed · {$by}", $url, $meta),
        };
    }

    /**
     * Active vendor users holding any of the permissions who want the
     * category, except the person who caused the event.
     *
     * @param  list<Permission>  $permissions
     * @return Collection<int, User>
     */
    private function recipients(array $permissions, string $category, ?User $except = null): Collection
    {
        return User::query()
            ->whereIn('role', [UserRole::VendorAdmin, UserRole::VendorStaff])
            ->where('status', RecordStatus::Active)
            ->get()
            ->filter(fn (User $user) => ! $user->is($except)
                && $user->wantsAlerts($category)
                && collect($permissions)->contains(fn (Permission $permission) => $user->hasPermission($permission)))
            ->values();
    }

    private function meal(Order $order): string
    {
        return mb_strtolower($order->meal_type->label());
    }
}
