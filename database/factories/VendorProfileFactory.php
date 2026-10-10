<?php

namespace Database\Factories;

use App\Enums\RecordStatus;
use App\Models\VendorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VendorProfile>
 */
class VendorProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $business = fake()->company();

        return [
            'business_name' => $business.' Catering',
            'organization_name' => $business.' Ltd.',
            'owner_name' => fake()->name(),
            'phone' => fake()->numerify('017########'),
            'email' => fake()->companyEmail(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'business_type' => 'Catering Service',
            'vat_tin' => fake()->numerify('#########'),
            'status' => RecordStatus::Active,
        ];
    }
}
