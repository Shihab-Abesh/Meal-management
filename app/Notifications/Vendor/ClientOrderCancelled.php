<?php

namespace App\Notifications\Vendor;

/**
 * A client removed every meal from an order.
 */
class ClientOrderCancelled extends VendorAlert
{
    public static function category(): string
    {
        return 'orders';
    }

    public static function kind(): string
    {
        return 'order_cancelled';
    }
}
