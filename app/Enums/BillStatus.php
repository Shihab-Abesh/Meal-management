<?php

namespace App\Enums;

use App\Support\Money;

enum BillStatus: string
{
    case Unpaid = 'unpaid';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Overdue = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Unpaid',
            self::PartiallyPaid => 'Partially Paid',
            self::Paid => 'Paid',
            self::Overdue => 'Overdue',
        };
    }

    /**
     * The status a bill has given its amounts and due date: paid in full,
     * overdue when money is still owed after the due date, otherwise unpaid
     * or partially paid.
     */
    public static function resolve(string $total, string $paid, string $dueDate, string $today): self
    {
        return match (true) {
            Money::compare($paid, $total) >= 0 => self::Paid,
            $dueDate < $today => self::Overdue,
            Money::isPositive($paid) => self::PartiallyPaid,
            default => self::Unpaid,
        };
    }

    /**
     * Statuses that still have money owed.
     *
     * @return list<self>
     */
    public static function open(): array
    {
        return [self::Unpaid, self::PartiallyPaid, self::Overdue];
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
