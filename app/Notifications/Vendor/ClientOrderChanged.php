<?php

namespace App\Notifications\Vendor;

/**
 * A client changed the number of meals on an order.
 */
class ClientOrderChanged extends VendorAlert
{
    public static function category(): string
    {
        return 'orders';
    }

    public static function kind(): string
    {
        return 'order_changed';
    }
}
