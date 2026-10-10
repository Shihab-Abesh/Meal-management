<?php

namespace App\Services;

use App\Enums\MealEntryStatus;
use App\Enums\OrderStatus;
use App\Models\Delivery;
use App\Models\MealEntry;
use App\Models\Order;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Moves orders through their lifecycle: kitchen steps, rider assignment,
 * delivery steps and cancellation. Every change runs in a transaction on a
 * locked order row and is recorded in the activity log.
 */
class OrderService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly Notifier $notifier,
        private readonly VendorNotifier $vendorAlerts,
    ) {}

    /**
     * Change the status along an allowed transition. Delivery timestamps are
     * stamped on the delivery row; cancelling also cancels the meals.
     *
     * @throws ValidationException
     */
    public function changeStatus(Order $order, OrderStatus $status, User $user, ?string $reason = null): Order
    {
        $order = DB::transaction(function () use ($order, $status, $user, $reason) {
            $order = Order::query()->with('delivery')->lockForUpdate()->findOrFail($order->id);
            $from = $order->status;

            if (! $from->canMoveTo($status)) {
                throw ValidationException::withMessages([
                    'status' => "An order that is {$from->label()} cannot be marked {$status->label()}.",
                ]);
            }

            if (in_array($status, OrderStatus::riderSteps(), true) && ! $order->delivery) {
                throw ValidationException::withMessages(['status' => 'Assign a rider before the order leaves the kitchen.']);
            }

            if ($status === OrderStatus::Cancelled) {
                $this->cancelMeals($order, $user);
                $order->fill(['cancelled_at' => now(), 'cancel_reason' => $reason]);
            }

            $order->status = $status;
            $order->save();

            $this->stampDelivery($order, $status);

            $this->activity->log(
                $order->delivery ? 'deliveries' : 'orders',
                'status_changed',
                "Order {$order->order_number}: {$from->label()} → {$status->label()}",
                $order,
                array_filter(['from' => $from->value, 'to' => $status->value, 'reason' => $reason]),
                $user,
            );

            return $order;
        });

        if ($status === OrderStatus::Delivered) {
            $this->notifier->orderDelivered($order);
            $this->vendorAlerts->deliveryCompleted($order, $user);
        }

        return $order;
    }

    /**
     * Assign (or change) the rider. Allowed until the food is picked up.
     *
     * @throws ValidationException
     */
    public function assignRider(Order $order, Rider $rider, User $user, ?string $deliveryTime = null, ?string $notes = null): Order
    {
        return DB::transaction(function () use ($order, $rider, $user, $deliveryTime, $notes) {
            $order = Order::query()->with('delivery.rider')->lockForUpdate()->findOrFail($order->id);

            if (! $order->status->canAssignRider()) {
                throw ValidationException::withMessages([
                    'rider_id' => "A rider cannot be assigned to an order that is {$order->status->label()}.",
                ]);
            }

            if (! $rider->isActive()) {
                throw ValidationException::withMessages(['rider_id' => 'Choose an active rider.']);
            }

            $previous = $order->delivery?->rider;

            $delivery = $order->delivery ?? new Delivery(['order_id' => $order->id]);
            $delivery->fill([
                'rider_id' => $rider->id,
                'assigned_by' => $user->id,
                'assigned_at' => now(),
                'notes' => $notes,
            ])->save();

            $from = $order->status;
            $order->fill(['status' => OrderStatus::Assigned, 'delivery_time' => $deliveryTime ?: $order->delivery_time])->save();

            $this->activity->log(
                'deliveries',
                $previous ? 'rider_changed' : 'rider_assigned',
                $previous && $previous->id !== $rider->id
                    ? "Order {$order->order_number}: rider changed from {$previous->name} to {$rider->name}"
                    : "Order {$order->order_number}: assigned to {$rider->name}",
                $order,
                ['rider_id' => $rider->id, 'previous_rider_id' => $previous?->id, 'from' => $from->value],
                $user,
            );

            return $order->setRelation('delivery', $delivery);
        });
    }

    /**
     * Take the rider off an order that has not been picked up yet.
     */
    public function unassignRider(Order $order, User $user): Order
    {
        return DB::transaction(function () use ($order, $user) {
            $order = Order::query()->with('delivery.rider')->lockForUpdate()->findOrFail($order->id);

            if ($order->status !== OrderStatus::Assigned || ! $order->delivery) {
                throw ValidationException::withMessages(['rider_id' => 'Only an assigned order that has not been picked up can be unassigned.']);
            }

            $rider = $order->delivery->rider;
            $order->delivery->delete();
            $order->update(['status' => OrderStatus::Ready]);

            $this->activity->log('deliveries', 'rider_unassigned', "Order {$order->order_number}: {$rider?->name} unassigned", $order, [
                'rider_id' => $rider?->id,
            ], $user);

            return $order->setRelation('delivery', null);
        });
    }

    /**
     * A rider moving their own delivery forward (Picked Up → On The Way →
     * Delivered). Riders cannot touch anyone else's delivery.
     *
     * @throws ValidationException
     */
    public function riderStep(Delivery $delivery, OrderStatus $status, User $rider): Order
    {
        if (! in_array($status, OrderStatus::riderSteps(), true)) {
            throw ValidationException::withMessages(['status' => 'Riders can only mark Picked Up, On The Way or Delivered.']);
        }

        return $this->changeStatus($delivery->order, $status, $rider);
    }

    private function cancelMeals(Order $order, User $user): void
    {
        MealEntry::query()
            ->confirmed()
            ->where('client_id', $order->client_id)
            ->where('meal_date', $order->order_date->toDateString())
            ->where('meal_type', $order->meal_type)
            ->update(['status' => MealEntryStatus::Cancelled->value, 'updated_by' => $user->id, 'updated_at' => now()]);
    }

    private function stampDelivery(Order $order, OrderStatus $status): void
    {
        $column = match ($status) {
            OrderStatus::PickedUp => 'picked_up_at',
            OrderStatus::OnTheWay => 'on_the_way_at',
            OrderStatus::Delivered => 'delivered_at',
            default => null,
        };

        if ($column && $order->delivery) {
            $order->delivery->update([$column => now()]);
        }
    }
}
