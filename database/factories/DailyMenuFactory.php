<?php

namespace Database\Factories;

use App\Enums\MealType;
use App\Models\DailyMenu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyMenu>
 */
class DailyMenuFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'menu_date' => now()->toDateString(),
            'meal_type' => MealType::Lunch,
            'price' => null,
            'notes' => null,
        ];
    }
}
