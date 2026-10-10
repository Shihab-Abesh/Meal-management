<?php

use App\Enums\Permission;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\ClientBillingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientUserController;
use App\Http\Controllers\DailyMenuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeImportController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RiderController;
use App\Http\Controllers\RiderPanelController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StaffUserController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\TodayMenuController;
use App\Http\Controllers\VendorProfileController;
use Illuminate\Support\Facades\Route;

// A named-route redirect keeps the base path, so the app also works from a
// subfolder such as http://localhost/app/public (Route::redirect does not).
Route::get('/', fn () => to_route('dashboard'))->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // The signed-in user's own account.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Shared by vendor users and client accounts. Policies and query scopes
    // keep client accounts inside their own organization.
    Route::middleware('role:vendor_admin,vendor_staff,client_admin')->group(function () {
        Route::get('/employees/import', [EmployeeImportController::class, 'create'])->name('employees.import');
        Route::post('/employees/import', [EmployeeImportController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('employees.import.store');
        Route::get('/employees/import/template', [EmployeeImportController::class, 'template'])->name('employees.import.template');
        Route::patch('/employees/{employee}/status', [EmployeeController::class, 'updateStatus'])->name('employees.status');
        Route::resource('employees', EmployeeController::class)->except('destroy');

        Route::get('/menu/today', TodayMenuController::class)->name('menu.today');

        Route::get('/meals', [MealController::class, 'index'])->name('meals.index');
        Route::post('/meals', [MealController::class, 'store'])->middleware('throttle:60,1')->name('meals.store');
        Route::get('/meals/calendar', [MealController::class, 'calendar'])->name('meals.calendar');
        Route::get('/meals/history', [MealController::class, 'history'])->name('meals.history');

        // Billing: BillPolicy lets vendor users with financial access see every
        // client and client accounts only their own bills.
        Route::get('/bills/{bill}', [BillController::class, 'show'])->whereNumber('bill')->name('bills.show');
        Route::get('/billing/statement', StatementController::class)->name('billing.statement');

        // Reports: ReportRequest checks the reports permission and keeps client
        // accounts on their own organization.
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->middleware('throttle:20,1')->name('reports.export');
    });

    Route::get('/billing', ClientBillingController::class)->middleware('role:client_admin')->name('billing.index');

    // Vendor-side area: vendor admins and vendor staff only. Individual
    // actions are further checked by policies and permission gates.
    Route::middleware('role:vendor_admin,vendor_staff')->group(function () {
        // The resource routes come first so /clients/create is not read as a client id.
        Route::resource('clients', ClientController::class)->except('show');
        Route::get('/clients/{client}', [ClientController::class, 'show'])->withTrashed()->name('clients.show');
        Route::patch('/clients/{client}/restore', [ClientController::class, 'restore'])->withTrashed()->name('clients.restore');
        Route::patch('/clients/{client}/status', [ClientController::class, 'updateStatus'])->name('clients.status');

        Route::scopeBindings()->group(function () {
            Route::post('/clients/{client}/users', [ClientUserController::class, 'store'])->name('clients.users.store');
            Route::put('/clients/{client}/users/{user}', [ClientUserController::class, 'update'])->name('clients.users.update');
            Route::delete('/clients/{client}/users/{user}', [ClientUserController::class, 'destroy'])->name('clients.users.destroy');
        });

        Route::middleware('can:'.Permission::ManageMenu->value)->group(function () {
            Route::get('/menu-items', [MenuItemController::class, 'index'])->name('menu-items.index');
            Route::post('/menu-items', [MenuItemController::class, 'store'])->name('menu-items.store');
            Route::put('/menu-items/{menuItem}', [MenuItemController::class, 'update'])->name('menu-items.update');
            Route::patch('/menu-items/{menuItem}/status', [MenuItemController::class, 'updateStatus'])->name('menu-items.status');
            Route::delete('/menu-items/{menuItem}', [MenuItemController::class, 'destroy'])->name('menu-items.destroy');

            // The menu chart: planned and edited in place, a week or month at a time.
            Route::post('/daily-menus/copy-week', [DailyMenuController::class, 'copyWeek'])->name('daily-menus.copy-week');
            Route::post('/daily-menus/{dailyMenu}/duplicate', [DailyMenuController::class, 'duplicate'])->name('daily-menus.duplicate');
            Route::resource('daily-menus', DailyMenuController::class)->only(['index', 'store', 'update', 'destroy']);
        });

        Route::middleware('can:'.Permission::ManagePricing->value)->group(function () {
            Route::get('/pricing', [PricingController::class, 'index'])->name('pricing.index');
            Route::put('/pricing/defaults', [PricingController::class, 'updateDefaults'])->name('pricing.defaults');
            Route::get('/pricing/clients/{client}', [PricingController::class, 'show'])->name('pricing.show');
            Route::post('/pricing/clients/{client}', [PricingController::class, 'store'])->name('pricing.store');
            Route::delete('/pricing/clients/{client}/prices/{price}', [PricingController::class, 'destroy'])
                ->scopeBindings()
                ->name('pricing.destroy');
        });

        Route::middleware('can:'.Permission::ManageRiders->value)->group(function () {
            Route::patch('/riders/{rider}/status', [RiderController::class, 'updateStatus'])->name('riders.status');
            Route::resource('riders', RiderController::class)->except('destroy');
        });

        // Orders: viewing needs the orders or deliveries permission; each
        // status change is checked against the permission it belongs to.
        Route::get('/orders', [OrderController::class, 'index'])->middleware('can:'.Permission::ManageOrders->value)->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

        Route::middleware('can:'.Permission::ManageDeliveries->value)->group(function () {
            Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
            Route::post('/orders/{order}/assign', [DeliveryController::class, 'assign'])->name('orders.assign');
            Route::delete('/orders/{order}/assign', [DeliveryController::class, 'unassign'])->name('orders.unassign');
        });

        Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
        Route::get('/bills/create', [BillController::class, 'create'])->name('bills.create');
        Route::post('/bills', [BillController::class, 'store'])->middleware('throttle:30,1')->name('bills.store');
        Route::post('/bills/{bill}/recalculate', [BillController::class, 'recalculate'])->name('bills.recalculate');
        Route::delete('/bills/{bill}', [BillController::class, 'destroy'])->name('bills.destroy');
        Route::post('/bills/{bill}/payments', [PaymentController::class, 'store'])->middleware('throttle:30,1')->name('bills.payments.store');
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

        Route::middleware('can:'.Permission::ManageSettings->value)->group(function () {
            Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
            Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        });

        Route::middleware('can:'.Permission::ManageUsers->value)->group(function () {
            Route::patch('/users/{user}/status', [StaffUserController::class, 'updateStatus'])->name('users.status');
            Route::resource('users', StaffUserController::class)->except(['show', 'destroy']);
        });

        // The user's own notifications; the controller never looks outside them.
        Route::prefix('notifications')->name('notifications.')->controller(NotificationController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/feed', 'feed')->middleware('throttle:120,1')->name('feed');
            Route::post('/read-all', 'readAll')->name('read-all');
            Route::delete('/read', 'clearRead')->name('clear-read');
            Route::put('/preferences', 'preferences')->name('preferences');
            Route::get('/{notification}/open', 'open')->whereUuid('notification')->name('open');
            Route::patch('/{notification}', 'update')->whereUuid('notification')->name('update');
        });

        Route::get('/activity-logs', [ActivityLogController::class, 'index'])
            ->middleware('can:'.Permission::ViewActivityLogs->value)
            ->name('activity-logs.index');

        Route::get('/vendor-profile', [VendorProfileController::class, 'show'])->name('vendor-profile.show');

        Route::middleware('can:'.Permission::ManageVendorProfile->value)->group(function () {
            Route::get('/vendor-profile/edit', [VendorProfileController::class, 'edit'])->name('vendor-profile.edit');
            Route::put('/vendor-profile', [VendorProfileController::class, 'update'])->name('vendor-profile.update');
        });
    });

    // Rider panel: a rider's own deliveries only.
    Route::middleware('role:rider')->prefix('rider')->name('rider.')->group(function () {
        Route::get('/deliveries', [RiderPanelController::class, 'index'])->name('deliveries.index');
        Route::get('/deliveries/history', [RiderPanelController::class, 'history'])->name('deliveries.history');
        Route::get('/deliveries/{delivery}', [RiderPanelController::class, 'show'])->name('deliveries.show');
        Route::post('/deliveries/{delivery}/status', [RiderPanelController::class, 'updateStatus'])
            ->middleware('throttle:30,1')
            ->name('deliveries.status');
    });
});

require __DIR__.'/auth.php';
