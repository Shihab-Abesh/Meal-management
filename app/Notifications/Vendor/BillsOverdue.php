<?php

namespace App\Notifications\Vendor;

/**
 * Bills passed their due date with money still owed.
 */
class BillsOverdue extends VendorAlert
{
    public static function category(): string
    {
        return 'billing';
    }

    public static function kind(): string
    {
        return 'bills_overdue';
    }
}
