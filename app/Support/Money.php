<?php

namespace App\Support;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

/**
 * Exact decimal arithmetic for amounts, which are passed around as strings
 * with two decimals ("2640.00"). Never use floats for money.
 */
class Money
{
    public static function of(string|int|float|null $amount): string
    {
        return (string) BigDecimal::of($amount ?? 0)->toScale(2, RoundingMode::HALF_UP);
    }

    public static function multiply(string|int|null $amount, int $quantity): string
    {
        return (string) BigDecimal::of($amount ?? 0)->multipliedBy($quantity)->toScale(2, RoundingMode::HALF_UP);
    }

    public static function divide(string|int|null $amount, int $by): string
    {
        return (string) BigDecimal::of($amount ?? 0)->dividedBy($by, 2, RoundingMode::HALF_UP);
    }

    public static function add(string|int|null ...$amounts): string
    {
        $total = BigDecimal::zero();

        foreach ($amounts as $amount) {
            $total = $total->plus($amount ?? 0);
        }

        return (string) $total->toScale(2, RoundingMode::HALF_UP);
    }

    public static function subtract(string|int|null $amount, string|int|null $minus): string
    {
        return (string) BigDecimal::of($amount ?? 0)->minus($minus ?? 0)->toScale(2, RoundingMode::HALF_UP);
    }

    public static function compare(string|int|null $a, string|int|null $b): int
    {
        return BigDecimal::of($a ?? 0)->compareTo($b ?? 0);
    }

    public static function isPositive(string|int|null $amount): bool
    {
        return BigDecimal::of($amount ?? 0)->isPositive();
    }
}
