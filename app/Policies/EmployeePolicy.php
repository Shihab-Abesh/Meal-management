<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Client;
use App\Models\Employee;
use App\Models\User;

/**
 * Vendor users with the employee permission manage every client's
 * employees; a client account manages only its own organization's.
 */
class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ManageEmployees) || $user->isClientAdmin();
    }

    public function view(User $user, Employee $employee): bool
    {
        return $this->ownsOrManages($user, $employee->client_id);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Employee $employee): bool
    {
        return $this->ownsOrManages($user, $employee->client_id);
    }

    /**
     * Add employees to a specific client (single or by import).
     */
    public function createFor(User $user, Client $client): bool
    {
        return $client->isActive() && $this->ownsOrManages($user, $client->id);
    }

    private function ownsOrManages(User $user, ?int $clientId): bool
    {
        if ($user->hasPermission(Permission::ManageEmployees)) {
            return true;
        }

        return $user->isClientAdmin() && $user->client_id === $clientId;
    }
}
