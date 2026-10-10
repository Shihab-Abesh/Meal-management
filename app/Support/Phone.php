<?php

namespace App\Support;

class Phone
{
    /**
     * Validation pattern for phone numbers: digits with optional leading +,
     * spaces, dashes and brackets (e.g. 01712-345678, +880 1712 345678).
     */
    public const PATTERN = '/^\+?[0-9\s\-()]{7,20}$/';

    /**
     * Strip formatting so "017 1234-5678" and "01712345678" match on login.
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $normalized = preg_replace('/[^0-9+]/', '', $phone);

        return preg_match('/[0-9]/', $normalized) ? $normalized : null;
    }
}
