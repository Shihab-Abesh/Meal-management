<?php

namespace App\Services;

use App\Enums\MealType;
use App\Enums\OrderStatus;
use App\Models\Client;
use App\Models\DailyMenu;
use App\Models\MealEntry;
use App\Models\Order;
use App\Models\User;
use App\Support\Money;
use Carbon\CarbonImmutable;

/**
 * Keeps the order for a client, date and meal type equal to its confirmed
 * meals. Called by MealEntryService inside the meal confirmation
 * transaction, so meals and order can never disagree.
 */
class OrderSync
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly VendorNotifier $vendorAlerts,
    ) {}

    public function syncFromMeals(Client $client, string $date, MealType $type, User $user): ?Order
    {
        $meals = MealEntry::query()
            ->confirmed()
            ->where('client_id', $client->id)
            ->where('meal_date', $date)
            ->where('meal_type', $type)
            ->selectRaw('COUNT(*) as quantity, COALESCE(SUM(unit_price), 0) as amount')
            ->toBase()
            ->first();

        $quantity = (int) $meals->quantity;
        $amount = Money::of($meals->amount);

        $order = Order::query()
            ->where('client_id', $client->id)
            ->where('order_date', $date)
            ->where('meal_type', $type)
            ->lockForUpdate()
            ->first();

        // Vendor confirmations go straight to the kitchen; client ones wait
        // for the vendor to confirm them.
        $startStatus = $user->isVendorUser() ? OrderStatus::Confirmed : OrderStatus::Pending;
        $label = sprintf('%s · %s · %s', $client->name, $type->label(), CarbonImmutable::parse($date)->format('d M Y'));

        if ($order === null) {
            if ($quantity === 0) {
                return null;
            }

            $order = Order::create([
                'client_id' => $client->id,
                'order_date' => $date,
                'meal_type' => $type,
                'quantity' => $quantity,
                'amount' => $amount,
                'status' => $startStatus,
                'delivery_address' => $client->deliveryAddress(),
                'created_by' => $user->id,
            ]);
            $order->update(['order_number' => Order::numberFor($order->id, $date)]);
            $this->syncItems($order);

            $this->activity->log('orders', 'created', "Order {$order->order_number} created: {$quantity} meals ({$label})", $order, [
                'quantity' => $quantity,
                'amount' => $amount,
                'status' => $startStatus->value,
            ], $user);

            $this->vendorAlerts->clientOrder($order, null, $user);

            return $order;
        }

        $before = ['quantity' => $order->quantity, 'status' => $order->status];
        $old = ['quantity' => $order->quantity, 'amount' => $order->amount, 'status' => $order->status->value];

        if ($quantity === 0 && $order->status !== OrderStatus::Delivered) {
            $order->fill([
                'status' => OrderStatus::Cancelled,
                'cancelled_at' => now(),
                'cancel_reason' => 'All meals were removed from the meal sheet.',
            ]);
        } elseif ($quantity > 0 && $order->status === OrderStatus::Cancelled) {
            $order->fill(['status' => $startStatus, 'cancelled_at' => null, 'cancel_reason' => null]);
        }

        $order->fill(['quantity' => $quantity, 'amount' => $amount])->save();

        if ($order->status->isBeforeDispatch()) {
            $this->syncItems($order);
        } else {
            // Already on its way: keep the dishes, follow the new count.
            $order->items()->update(['quantity' => $quantity]);
        }

        $this->activity->log('orders', 'updated', "Order {$order->order_number} updated from the meal sheet: {$old['quantity']} → {$quantity} meals", $order, [
            'old' => $old,
            'new' => ['quantity' => $quantity, 'amount' => $amount, 'status' => $order->status->value],
        ], $user);

        $this->vendorAlerts->clientOrder($order, $before, $user);

        return $order;
    }

    /**
     * Copy the day's menu into the order, one line per dish.
     */
    private function syncItems(Order $order): void
    {
        $menu = DailyMenu::query()
            ->with('items:id,name')
            ->where('menu_date', $order->order_date->toDateString())
            ->where('meal_type', $order->meal_type)
            ->first();

        $order->items()->delete();

        if (! $menu) {
            return;
        }

        foreach ($menu->items as $position => $item) {
            $order->items()->create([
                'menu_item_id' => $item->id,
                'name' => $item->name,
                'quantity' => $order->quantity,
                'sort_order' => $position,
            ]);
        }
    }
}
