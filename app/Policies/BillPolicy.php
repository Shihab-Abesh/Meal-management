<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Bill;
use App\Models\Client;
use App\Models\User;

/**
 * Vendor users see bills with financial or billing access and change them
 * with billing access; a client account only sees its own bills.
 */
class BillPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->vendorCanView($user) || $user->isClientAdmin();
    }

    public function view(User $user, Bill $bill): bool
    {
        return $this->vendorCanView($user)
            || ($user->isClientAdmin() && $user->client_id === $bill->client_id);
    }

    /**
     * See billing figures for a client (statement, balance).
     */
    public function viewFor(User $user, Client $client): bool
    {
        return $this->vendorCanView($user)
            || ($user->isClientAdmin() && $user->client_id === $client->id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ManageBilling);
    }

    public function update(User $user, Bill $bill): bool
    {
        return $user->hasPermission(Permission::ManageBilling);
    }

    public function delete(User $user, Bill $bill): bool
    {
        return $user->hasPermission(Permission::ManageBilling);
    }

    /**
     * Record or remove payments on the bill.
     */
    public function pay(User $user, Bill $bill): bool
    {
        return $user->hasPermission(Permission::ManageBilling);
    }

    private function vendorCanView(User $user): bool
    {
        return $user->isVendorUser()
            && ($user->hasPermission(Permission::ViewFinancials) || $user->hasPermission(Permission::ManageBilling));
    }
}
