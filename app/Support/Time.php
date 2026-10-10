<?php

namespace App\Support;

class Time
{
    /**
     * "10:00:00" -> "10:00", the format time inputs use.
     */
    public static function short(?string $time): ?string
    {
        return $time === null || $time === '' ? null : substr($time, 0, 5);
    }
}
