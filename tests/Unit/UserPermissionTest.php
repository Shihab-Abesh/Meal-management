<?php

namespace Tests\Unit;

use App\Enums\Permission;
use App\Enums\UserRole;
use App\Models\User;
use Tests\TestCase;

class UserPermissionTest extends TestCase
{
    public function test_vendor_admin_holds_every_permission(): void
    {
        $admin = User::factory()->vendorAdmin()->make();

        foreach (Permission::cases() as $permission) {
            $this->assertTrue($admin->hasPermission($permission), $permission->value);
            $this->assertTrue($admin->can($permission->value), $permission->value);
        }
    }

    public function test_vendor_staff_get_operational_but_no_critical_permissions_by_default(): void
    {
        $staff = User::factory()->vendorStaff()->make();

        $this->assertSame(Permission::operational(), $staff->effectivePermissions());
        $this->assertTrue($staff->can(Permission::ManageClients->value));

        foreach (Permission::cases() as $permission) {
            if (! $permission->isOperational()) {
                $this->assertFalse($staff->can($permission->value), $permission->value);
            }
        }
    }

    public function test_a_stored_list_replaces_the_staff_defaults(): void
    {
        $staff = User::factory()->vendorStaff()->make([
            'permissions' => ['vendor_profile.manage', 'not.a.real.permission'],
        ]);

        $this->assertSame([Permission::ManageVendorProfile], $staff->effectivePermissions());
        $this->assertTrue($staff->can(Permission::ManageVendorProfile->value));
        $this->assertFalse($staff->can(Permission::ViewFinancials->value));
        // Operational access can be revoked as well.
        $this->assertFalse($staff->can(Permission::ManageClients->value));
    }

    public function test_grants_are_ignored_for_client_and_rider_accounts(): void
    {
        $grants = ['permissions' => array_map(fn (Permission $p) => $p->value, Permission::cases())];

        $this->assertSame([], User::factory()->state(['role' => UserRole::ClientAdmin])->make($grants)->effectivePermissions());
        $this->assertSame([], User::factory()->rider()->make($grants)->effectivePermissions());
    }
}
