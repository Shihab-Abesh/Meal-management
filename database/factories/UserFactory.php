<?php

namespace Database\Factories;

use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->numerify('017########'),
            'role' => UserRole::VendorStaff,
            'status' => RecordStatus::Active,
            'permissions' => null,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'last_login_at' => null,
            'last_login_ip' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function vendorAdmin(): static
    {
        return $this->state(['role' => UserRole::VendorAdmin]);
    }

    public function vendorStaff(): static
    {
        return $this->state(['role' => UserRole::VendorStaff]);
    }

    /**
     * A client account. Pass a client to attach it to; otherwise a new
     * active client is created.
     */
    public function clientAdmin(?Client $client = null): static
    {
        return $this->state([
            'role' => UserRole::ClientAdmin,
            'client_id' => $client?->id ?? Client::factory(),
        ]);
    }

    /**
     * A rider account with its rider profile.
     */
    public function rider(): static
    {
        return $this->state(['role' => UserRole::Rider])->afterCreating(function (User $user) {
            Rider::factory()->create([
                'user_id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone ?? fake()->unique()->numerify('016########'),
            ]);
        });
    }

    public function inactive(): static
    {
        return $this->state(['status' => RecordStatus::Inactive]);
    }

    public function withPermissions(Permission ...$permissions): static
    {
        return $this->state([
            'permissions' => array_map(fn (Permission $permission) => $permission->value, $permissions),
        ]);
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
