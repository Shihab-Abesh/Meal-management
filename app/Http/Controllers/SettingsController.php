<?php

namespace App\Http\Controllers;

use App\Enums\MealType;
use App\Enums\Permission;
use App\Services\Settings;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Vendor-wide settings, one section at a time. Routes require the settings
 * permission; default prices also need the pricing permission.
 */
class SettingsController extends Controller
{
    private const SECTIONS = ['meals', 'pricing', 'billing', 'notifications'];

    public function __construct(private readonly Settings $settings) {}

    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('settings/edit', [
            'values' => [
                'lunch_cutoff' => $this->settings->defaultCutoff(MealType::Lunch),
                'dinner_cutoff' => $this->settings->defaultCutoff(MealType::Dinner),
                'lunch_price' => $this->settings->defaultMealPrice(MealType::Lunch),
                'dinner_price' => $this->settings->defaultMealPrice(MealType::Dinner),
                'due_days' => $this->settings->billDueDays(),
                'bill_issued' => $this->settings->notifies('bill_issued'),
                'payment_received' => $this->settings->notifies('payment_received'),
                'order_delivered' => $this->settings->notifies('order_delivered'),
            ],
            'can' => [
                'pricing' => $user->hasPermission(Permission::ManagePricing),
                'users' => $user->hasPermission(Permission::ManageUsers),
                'vendorProfile' => $user->hasPermission(Permission::ManageVendorProfile),
            ],
            // Emails only leave the server with a real mailer configured in .env.
            'mailConfigured' => ! in_array(config('mail.default'), ['log', 'array'], true),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $section = $request->validate(['section' => ['required', Rule::in(self::SECTIONS)]])['section'];
        $user = $request->user();

        if ($section === 'pricing') {
            abort_unless($user->hasPermission(Permission::ManagePricing), 403);
        }

        [$values, $description] = match ($section) {
            'meals' => $this->mealValues($request),
            'pricing' => $this->pricingValues($request),
            'billing' => $this->billingValues($request),
            'notifications' => $this->notificationValues($request),
        };

        $this->settings->set($values, $user, $description);

        $this->toast('Settings saved.');

        return back();
    }

    /**
     * @return array{0: array<string, mixed>, 1: string}
     */
    private function mealValues(Request $request): array
    {
        $validated = $request->validate([
            'lunch_cutoff' => ['required', 'date_format:H:i'],
            'dinner_cutoff' => ['required', 'date_format:H:i'],
        ]);

        return [[
            'meals.lunch_cutoff' => $validated['lunch_cutoff'],
            'meals.dinner_cutoff' => $validated['dinner_cutoff'],
        ], 'Updated default meal cutoff times'];
    }

    /**
     * @return array{0: array<string, mixed>, 1: string}
     */
    private function pricingValues(Request $request): array
    {
        $validated = $request->validate([
            'lunch_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999.99'],
            'dinner_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999.99'],
        ]);

        return [[
            'meals.lunch_price' => Money::of($validated['lunch_price']),
            'meals.dinner_price' => Money::of($validated['dinner_price']),
        ], 'Updated default meal prices'];
    }

    /**
     * @return array{0: array<string, mixed>, 1: string}
     */
    private function billingValues(Request $request): array
    {
        $validated = $request->validate(['due_days' => ['required', 'integer', 'min:0', 'max:120']]);

        return [['billing.due_days' => (int) $validated['due_days']], 'Updated billing settings'];
    }

    /**
     * @return array{0: array<string, mixed>, 1: string}
     */
    private function notificationValues(Request $request): array
    {
        $validated = $request->validate([
            'bill_issued' => ['required', 'boolean'],
            'payment_received' => ['required', 'boolean'],
            'order_delivered' => ['required', 'boolean'],
        ]);

        return [[
            'notifications.bill_issued' => (bool) $validated['bill_issued'],
            'notifications.payment_received' => (bool) $validated['payment_received'],
            'notifications.order_delivered' => (bool) $validated['order_delivered'],
        ], 'Updated notification settings'];
    }
}
