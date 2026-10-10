<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Client;
use App\Models\User;

/**
 * Client organizations are managed on the vendor side only. Client and
 * rider accounts never hold these permissions.
 */
class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ManageClients);
    }

    public function view(User $user, Client $client): bool
    {
        return $user->hasPermission(Permission::ManageClients);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ManageClients);
    }

    public function update(User $user, Client $client): bool
    {
        return $user->hasPermission(Permission::ManageClients) && ! $client->trashed();
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->hasPermission(Permission::ManageClients) && ! $client->trashed();
    }

    public function restore(User $user, Client $client): bool
    {
        return $user->hasPermission(Permission::ManageClients) && $client->trashed();
    }

    /**
     * Mark and confirm daily meals for the client: vendor users with the
     * meals permission, or the client's own accounts.
     */
    public function manageMeals(User $user, Client $client): bool
    {
        if (! $client->isActive()) {
            return false;
        }

        return $user->hasPermission(Permission::ManageMeals)
            || ($user->isClientAdmin() && $user->client_id === $client->id);
    }

    /**
     * Create and manage the client's login accounts.
     */
    public function manageUsers(User $user, Client $client): bool
    {
        return $user->hasPermission(Permission::ManageClientUsers) && ! $client->trashed();
    }
}
