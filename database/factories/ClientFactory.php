<?php

namespace Database\Factories;

use App\Enums\RecordStatus;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company().' Ltd.',
            'contact_person' => fake()->name(),
            'phone' => fake()->numerify('018########'),
            'email' => fake()->companyEmail(),
            'address' => fake()->streetAddress(),
            'city' => 'Dhaka',
            'delivery_address' => null,
            'employee_count' => fake()->numberBetween(10, 80),
            'lunch_enabled' => true,
            'dinner_enabled' => false,
            'office_start_time' => '09:00',
            'lunch_cutoff_time' => null,
            'dinner_cutoff_time' => null,
            'special_instructions' => null,
            'status' => RecordStatus::Active,
            'notes' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => RecordStatus::Inactive]);
    }

    public function withDinner(): static
    {
        return $this->state(['dinner_enabled' => true]);
    }
}
