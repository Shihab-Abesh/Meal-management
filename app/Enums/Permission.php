<?php

namespace App\Enums;

/**
 * Fine-grained abilities for vendor-side accounts.
 *
 * Every case is registered as a Gate ability, so it can be used with
 * `$user->can(...)`, the `can:` route middleware and policies.
 *
 * Operational permissions are what vendor staff get by default; critical
 * ones belong to vendor admins unless explicitly granted to a staff member.
 */
enum Permission: string
{
    // Operational modules.
    case ManageClients = 'clients.manage';
    case ManageEmployees = 'employees.manage';
    case ManageMeals = 'meals.manage';
    case ManageMenu = 'menu.manage';
    case ManageOrders = 'orders.manage';
    case ManageRiders = 'riders.manage';
    case ManageDeliveries = 'deliveries.manage';
    case ViewReports = 'reports.view';

    // Critical settings and financial data.
    case ManageClientUsers = 'client_users.manage';
    case ManagePricing = 'pricing.manage';
    case OverrideMealCutoff = 'meals.override_cutoff';
    case ViewFinancials = 'financials.view';
    case ManageBilling = 'billing.manage';
    case ManageVendorProfile = 'vendor_profile.manage';
    case ManageSettings = 'settings.manage';
    case ManageUsers = 'users.manage';
    case ViewActivityLogs = 'activity_logs.view';

    public function label(): string
    {
        return match ($this) {
            self::ManageClients => 'Manage clients',
            self::ManageEmployees => 'Manage employees',
            self::ManageMeals => 'Manage daily meals',
            self::ManageMenu => 'Manage menu items and daily menus',
            self::ManageOrders => 'Manage orders',
            self::ManageRiders => 'Manage riders',
            self::ManageDeliveries => 'Assign riders and update deliveries',
            self::ViewReports => 'View reports',
            self::ManageClientUsers => 'Manage client login accounts',
            self::ManagePricing => 'Manage client prices',
            self::OverrideMealCutoff => 'Change meals after the cutoff time',
            self::ViewFinancials => 'View revenue and financial data',
            self::ManageBilling => 'Manage bills and payments',
            self::ManageVendorProfile => 'Manage vendor profile',
            self::ManageSettings => 'Manage system settings',
            self::ManageUsers => 'Manage vendor staff accounts',
            self::ViewActivityLogs => 'View activity logs',
        };
    }

    public function isOperational(): bool
    {
        return in_array($this, self::operational(), true);
    }

    /**
     * @return list<self>
     */
    public static function operational(): array
    {
        return [
            self::ManageClients,
            self::ManageEmployees,
            self::ManageMeals,
            self::ManageMenu,
            self::ManageOrders,
            self::ManageRiders,
            self::ManageDeliveries,
            self::ViewReports,
        ];
    }

    /**
     * @return list<array{value: string, label: string, operational: bool}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $permission) => [
                'value' => $permission->value,
                'label' => $permission->label(),
                'operational' => $permission->isOperational(),
            ],
            self::cases(),
        );
    }
}
