<?php

namespace Database\Seeders;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Create the first vendor admin from the SEED_ADMIN_* values in .env.
     * In local development a demo staff account is added as well.
     * Existing accounts keep their current password.
     */
    public function run(): void
    {
        $admin = config('catering.seed.admin');

        $this->ensureUser(
            email: $admin['email'],
            name: $admin['name'],
            phone: $admin['phone'],
            password: $admin['password'],
            role: UserRole::VendorAdmin,
        );

        if (app()->environment('local')) {
            $this->ensureUser(
                email: 'staff@example.com',
                name: 'Vendor Staff',
                phone: '01700000001',
                password: 'password',
                role: UserRole::VendorStaff,
            );
        }
    }

    private function ensureUser(string $email, string $name, ?string $phone, string $password, UserRole $role): void
    {
        $user = User::withTrashed()->firstOrNew(['email' => Str::lower($email)]);

        if ($user->exists) {
            return;
        }

        $user->fill(['name' => $name, 'phone' => $phone, 'password' => $password]);
        $user->forceFill([
            'role' => $role,
            'status' => RecordStatus::Active,
            'email_verified_at' => now(),
        ])->save();
    }
}
