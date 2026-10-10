<?php

namespace App\Casts;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * A calendar date stored as "YYYY-MM-DD" and read as CarbonImmutable.
 *
 * Laravel's built-in `date` cast writes "YYYY-MM-DD 00:00:00", which SQLite
 * keeps as text, so `where('meal_date', '<=', '2026-10-06')` would compare
 * differently there than on MySQL. Storing the bare date keeps plain,
 * index-friendly comparisons correct on every database.
 *
 * @implements CastsAttributes<CarbonImmutable|null, mixed>
 */
class DateOnly implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?CarbonImmutable
    {
        return $value === null ? null : CarbonImmutable::parse($value)->startOfDay();
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return ($value instanceof \DateTimeInterface ? CarbonImmutable::instance($value) : CarbonImmutable::parse($value))
            ->toDateString();
    }
}
