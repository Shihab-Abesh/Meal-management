<?php

namespace App\Notifications\Vendor;

/**
 * A client placed a meal order.
 */
class ClientOrderPlaced extends VendorAlert
{
    public static function category(): string
    {
        return 'orders';
    }

    public static function kind(): string
    {
        return 'order_placed';
    }
}
