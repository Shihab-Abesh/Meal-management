<?php

namespace App\Enums;

enum UserRole: string
{
    case VendorAdmin = 'vendor_admin';
    case VendorStaff = 'vendor_staff';
    case ClientAdmin = 'client_admin';
    case Rider = 'rider';

    public function label(): string
    {
        return match ($this) {
            self::VendorAdmin => 'Vendor Admin',
            self::VendorStaff => 'Vendor Staff',
            self::ClientAdmin => 'Client Admin',
            self::Rider => 'Rider',
        };
    }

    /**
     * Vendor-side roles work for the catering business itself.
     */
    public function isVendor(): bool
    {
        return in_array($this, [self::VendorAdmin, self::VendorStaff], true);
    }

    /**
     * Permissions a role receives when nothing is stored for the user.
     *
     * Vendor admins hold every permission and staff the operational ones.
     * Client and rider access is scoped by role, not by permissions.
     *
     * @return list<Permission>
     */
    public function defaultPermissions(): array
    {
        return match ($this) {
            self::VendorAdmin => Permission::cases(),
            self::VendorStaff => Permission::operational(),
            self::ClientAdmin, self::Rider => [],
        };
    }

    /**
     * Where the role lands after signing in.
     */
    public function homeRoute(): string
    {
        return $this === self::Rider ? 'rider.deliveries.index' : 'dashboard';
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $role) => ['value' => $role->value, 'label' => $role->label()],
            self::cases(),
        );
    }
}
