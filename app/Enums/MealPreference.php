<?php

namespace App\Enums;

enum MealPreference: string
{
    case Regular = 'regular';
    case Vegetarian = 'vegetarian';
    case NoBeef = 'no_beef';
    case NoFish = 'no_fish';
    case Diabetic = 'diabetic';

    public function label(): string
    {
        return match ($this) {
            self::Regular => 'Regular',
            self::Vegetarian => 'Vegetarian',
            self::NoBeef => 'No beef',
            self::NoFish => 'No fish',
            self::Diabetic => 'Diabetic',
        };
    }

    /**
     * Match a value or label typed in a spreadsheet: "No Beef", "no_beef"
     * and "no-beef" all resolve to NoBeef. Empty means Regular.
     */
    public static function fromInput(?string $input): ?self
    {
        $normalized = str_replace([' ', '-'], '_', mb_strtolower(trim((string) $input)));

        if ($normalized === '') {
            return self::Regular;
        }

        return self::tryFrom($normalized);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $preference) => ['value' => $preference->value, 'label' => $preference->label()],
            self::cases(),
        );
    }
}
