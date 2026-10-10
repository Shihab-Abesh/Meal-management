<?php

namespace App\Enums;

enum VehicleType: string
{
    case Bicycle = 'bicycle';
    case Motorcycle = 'motorcycle';
    case Van = 'van';
    case Car = 'car';
    case OnFoot = 'on_foot';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Bicycle => 'Bicycle',
            self::Motorcycle => 'Motorcycle',
            self::Van => 'Van',
            self::Car => 'Car',
            self::OnFoot => 'On foot',
            self::Other => 'Other',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $type) => ['value' => $type->value, 'label' => $type->label()],
            self::cases(),
        );
    }
}
