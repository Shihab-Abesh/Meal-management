<?php

namespace Database\Factories;

use App\Enums\RecordStatus;
use App\Enums\VehicleType;
use App\Models\Rider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rider>
 */
class RiderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->unique()->numerify('015########'),
            'nid' => fake()->numerify('##########'),
            'address' => fake()->streetAddress(),
            'emergency_contact' => fake()->name().' - '.fake()->numerify('017########'),
            'vehicle_type' => VehicleType::Motorcycle,
            'vehicle_number' => fake()->bothify('DHA-##-####'),
            'joining_date' => now()->subYear()->toDateString(),
            'status' => RecordStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => RecordStatus::Inactive]);
    }
}
