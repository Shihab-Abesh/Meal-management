<?php

namespace Database\Factories;

use App\Enums\MenuCategory;
use App\Enums\RecordStatus;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'category' => MenuCategory::MainCourse,
            'description' => null,
            'default_price' => '50.00',
            'status' => RecordStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => RecordStatus::Inactive]);
    }
}
