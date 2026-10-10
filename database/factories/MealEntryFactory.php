<?php

namespace Database\Factories;

use App\Enums\MealEntryStatus;
use App\Enums\MealType;
use App\Models\Employee;
use App\Models\MealEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MealEntry>
 */
class MealEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            // Always the employee's own client.
            'client_id' => fn (array $attributes) => Employee::query()->whereKey($attributes['employee_id'])->value('client_id'),
            'meal_date' => now()->toDateString(),
            'meal_type' => MealType::Lunch,
            'status' => MealEntryStatus::Confirmed,
            'unit_price' => '120.00',
            'confirmed_at' => now(),
        ];
    }

    public function cancelled(): static
    {
        return $this->state(['status' => MealEntryStatus::Cancelled]);
    }
}
