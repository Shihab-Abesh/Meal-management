<?php

namespace Database\Seeders;

use App\Enums\RecordStatus;
use App\Models\VendorProfile;
use Illuminate\Database\Seeder;

class VendorProfileSeeder extends Seeder
{
    /**
     * Create a starter vendor profile the admin can edit from the app.
     * An existing profile is never overwritten.
     */
    public function run(): void
    {
        if (VendorProfile::query()->exists()) {
            return;
        }

        VendorProfile::create([
            'business_name' => 'ABC Catering',
            'organization_name' => 'ABC Catering Ltd.',
            'owner_name' => 'Business Owner',
            'phone' => '01700000000',
            'email' => 'info@example.com',
            'address' => 'House 1, Road 1',
            'city' => 'Dhaka',
            'business_type' => 'Catering Service',
            'vat_tin' => null,
            'status' => RecordStatus::Active,
        ]);
    }
}
