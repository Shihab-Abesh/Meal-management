<?php

namespace App\Notifications\Vendor;

/**
 * A rider delivered an order.
 */
class DeliveryCompleted extends VendorAlert
{
    public static function category(): string
    {
        return 'deliveries';
    }

    public static function kind(): string
    {
        return 'delivery_completed';
    }
}
