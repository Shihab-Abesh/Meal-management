<?php

namespace App\Enums;

/**
 * Lifecycle of an order and its delivery:
 * Pending → Confirmed → Preparing → Ready → Assigned → Picked Up → On The Way → Delivered,
 * or Cancelled before it is delivered.
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Assigned = 'assigned';
    case PickedUp = 'picked_up';
    case OnTheWay = 'on_the_way';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Preparing => 'Preparing',
            self::Ready => 'Ready',
            self::Assigned => 'Assigned',
            self::PickedUp => 'Picked Up',
            self::OnTheWay => 'On The Way',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Statuses this one may move to with a plain status change. Assigning a
     * rider (→ Assigned) is a separate action, see canAssignRider().
     *
     * @return list<self>
     */
    public function next(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Preparing, self::Ready, self::Cancelled],
            self::Confirmed => [self::Preparing, self::Ready, self::Cancelled],
            self::Preparing => [self::Ready, self::Cancelled],
            self::Ready => [self::Cancelled],
            self::Assigned => [self::PickedUp, self::Cancelled],
            self::PickedUp => [self::OnTheWay],
            self::OnTheWay => [self::Delivered],
            self::Delivered, self::Cancelled => [],
        };
    }

    public function canMoveTo(self $status): bool
    {
        return in_array($status, $this->next(), true);
    }

    /**
     * A rider can be assigned (or changed) until the food is picked up.
     */
    public function canAssignRider(): bool
    {
        return in_array($this, [self::Pending, self::Confirmed, self::Preparing, self::Ready, self::Assigned], true);
    }

    /**
     * Still in the kitchen: quantity and items follow the meal sheet.
     */
    public function isBeforeDispatch(): bool
    {
        return in_array($this, [self::Pending, self::Confirmed, self::Preparing, self::Ready], true);
    }

    /**
     * Steps a rider performs on their own delivery.
     *
     * @return list<self>
     */
    public static function riderSteps(): array
    {
        return [self::PickedUp, self::OnTheWay, self::Delivered];
    }

    /**
     * Statuses still waiting to be delivered.
     *
     * @return list<self>
     */
    public static function open(): array
    {
        return [self::Pending, self::Confirmed, self::Preparing, self::Ready, self::Assigned, self::PickedUp, self::OnTheWay];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status) => ['value' => $status->value, 'label' => $status->label()],
            self::cases(),
        );
    }
}
