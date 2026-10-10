<?php

namespace Database\Factories;

use App\Enums\MealPreference;
use App\Enums\RecordStatus;
use App\Models\Client;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'employee_code' => fake()->unique()->bothify('EMP-####'),
            'name' => fake()->name(),
            'phone' => fake()->numerify('019########'),
            'email' => fake()->unique()->safeEmail(),
            'department' => fake()->randomElement(['Engineering', 'Finance', 'Sales', 'HR', 'Operations']),
            'designation' => fake()->jobTitle(),
            'meal_preference' => MealPreference::Regular,
            'lunch_enabled' => true,
            'dinner_enabled' => false,
            'status' => RecordStatus::Active,
            'joining_date' => fake()->dateTimeBetween('-3 years', '-1 week')->format('Y-m-d'),
            'notes' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => RecordStatus::Inactive]);
    }

    public function forClient(Client $client): static
    {
        return $this->state(['client_id' => $client->id]);
    }
}
