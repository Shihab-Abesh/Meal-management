<?php

namespace App\Support;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Builds the sidebar and mobile navigation for the signed-in user.
 *
 * Menu entries live here rather than in React so visibility follows the same
 * role and permission rules the backend enforces. New modules register their
 * entry in items().
 */
class Navigation
{
    /**
     * @return list<array{label: string|null, items: list<array{label: string, href: string, icon: string, active: bool, mobile: bool}>}>
     */
    public function for(Request $request): array
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return [];
        }

        $groups = [];

        foreach ($this->items($user) as $item) {
            if (! $item['visible']) {
                continue;
            }

            $groups[$item['group']][] = [
                'label' => $item['label'],
                'href' => route($item['route']),
                'icon' => $item['icon'],
                'active' => $request->routeIs($item['match'] ?? $item['route']),
                'mobile' => $item['mobile'] ?? false,
            ];
        }

        $result = [];

        foreach ($groups as $label => $items) {
            $result[] = ['label' => $label === '' ? null : $label, 'items' => $items];
        }

        return $result;
    }

    /**
     * @return list<array{label: string, route: string, match?: string, icon: string, group: string, mobile?: bool, visible: bool}>
     */
    private function items(User $user): array
    {
        $mealsAccess = $user->hasPermission(Permission::ManageMeals) || $user->isClientAdmin();
        $seesBilling = $user->isVendorUser()
            && ($user->hasPermission(Permission::ViewFinancials) || $user->hasPermission(Permission::ManageBilling));

        return [
            [
                'label' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => 'layout-dashboard',
                'group' => '',
                'mobile' => true,
                'visible' => true,
            ],
            [
                'label' => 'Daily Meals',
                'route' => 'meals.index',
                'icon' => 'clipboard-check',
                'group' => 'Meals',
                'mobile' => true,
                'visible' => $mealsAccess,
            ],
            [
                'label' => 'Meal Calendar',
                'route' => 'meals.calendar',
                'icon' => 'calendar',
                'group' => 'Meals',
                'visible' => $mealsAccess,
            ],
            [
                'label' => 'Meal History',
                'route' => 'meals.history',
                'icon' => 'history',
                'group' => 'Meals',
                'visible' => $mealsAccess,
            ],
            [
                'label' => 'Orders',
                'route' => 'orders.index',
                'match' => 'orders.*',
                'icon' => 'receipt',
                'group' => 'Operations',
                'mobile' => true,
                'visible' => $user->hasPermission(Permission::ManageOrders),
            ],
            [
                'label' => 'Deliveries',
                'route' => 'deliveries.index',
                'icon' => 'truck',
                'group' => 'Operations',
                'mobile' => true,
                'visible' => $user->hasPermission(Permission::ManageDeliveries),
            ],
            [
                'label' => 'Riders',
                'route' => 'riders.index',
                'match' => 'riders.*',
                'icon' => 'bike',
                'group' => 'Operations',
                'visible' => $user->hasPermission(Permission::ManageRiders),
            ],
            [
                'label' => "Today's Deliveries",
                'route' => 'rider.deliveries.index',
                'match' => 'rider.deliveries.index',
                'icon' => 'truck',
                'group' => '',
                'mobile' => true,
                'visible' => $user->isRider(),
            ],
            [
                'label' => 'Delivery History',
                'route' => 'rider.deliveries.history',
                'icon' => 'history',
                'group' => '',
                'mobile' => true,
                'visible' => $user->isRider(),
            ],
            [
                'label' => 'Clients',
                'route' => 'clients.index',
                'match' => 'clients.*',
                'icon' => 'building',
                'group' => 'Management',
                'mobile' => true,
                'visible' => $user->hasPermission(Permission::ManageClients),
            ],
            [
                'label' => 'Employees',
                'route' => 'employees.index',
                'match' => 'employees.*',
                'icon' => 'users',
                'group' => 'Management',
                'mobile' => true,
                'visible' => $user->hasPermission(Permission::ManageEmployees) || $user->isClientAdmin(),
            ],
            [
                'label' => "Today's Menu",
                'route' => 'menu.today',
                'icon' => 'book-open',
                'group' => 'Menu',
                'mobile' => $user->isClientAdmin(),
                'visible' => $user->isVendorUser() || $user->isClientAdmin(),
            ],
            [
                'label' => 'Menu Chart',
                'route' => 'daily-menus.index',
                'match' => 'daily-menus.*',
                'icon' => 'calendar',
                'group' => 'Menu',
                'visible' => $user->hasPermission(Permission::ManageMenu),
            ],
            [
                'label' => 'Menu Items',
                'route' => 'menu-items.index',
                'match' => 'menu-items.*',
                'icon' => 'chef-hat',
                'group' => 'Menu',
                'visible' => $user->hasPermission(Permission::ManageMenu),
            ],
            [
                'label' => 'Pricing',
                'route' => 'pricing.index',
                'match' => 'pricing.*',
                'icon' => 'tag',
                'group' => 'Menu',
                'visible' => $user->hasPermission(Permission::ManagePricing),
            ],
            [
                'label' => 'Bills',
                'route' => 'bills.index',
                'match' => 'bills.*',
                'icon' => 'file-text',
                'group' => 'Billing',
                'visible' => $seesBilling,
            ],
            [
                'label' => 'Payments',
                'route' => 'payments.index',
                'icon' => 'wallet',
                'group' => 'Billing',
                'visible' => $seesBilling,
            ],
            [
                'label' => 'Statements',
                'route' => 'billing.statement',
                'icon' => 'receipt',
                'group' => 'Billing',
                'visible' => $seesBilling,
            ],
            [
                'label' => 'Billing',
                'route' => 'billing.index',
                'match' => 'billing.*',
                'icon' => 'wallet',
                'group' => 'Billing',
                'visible' => $user->isClientAdmin(),
            ],
            [
                'label' => 'Reports',
                'route' => 'reports.index',
                'icon' => 'chart',
                'group' => 'Reports',
                'visible' => $user->hasPermission(Permission::ViewReports) || $user->isClientAdmin(),
            ],
            [
                'label' => 'Settings',
                'route' => 'settings.edit',
                'icon' => 'settings',
                'group' => 'Settings',
                'visible' => $user->hasPermission(Permission::ManageSettings),
            ],
            [
                'label' => 'Vendor Profile',
                'route' => 'vendor-profile.show',
                'match' => 'vendor-profile.*',
                'icon' => 'store',
                'group' => 'Settings',
                'visible' => $user->isVendorUser(),
            ],
            [
                'label' => 'Users',
                'route' => 'users.index',
                'match' => 'users.*',
                'icon' => 'user-cog',
                'group' => 'Settings',
                'visible' => $user->hasPermission(Permission::ManageUsers),
            ],
            [
                'label' => 'Activity Log',
                'route' => 'activity-logs.index',
                'icon' => 'activity',
                'group' => 'Settings',
                'visible' => $user->hasPermission(Permission::ViewActivityLogs),
            ],
        ];
    }
}
