<?php

namespace App\Http\Controllers;

use App\Enums\MealType;
use App\Enums\Permission;
use App\Http\Resources\DailyMenuResource;
use App\Models\DailyMenu;
use App\Services\PricingService;
use App\Services\Settings;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The menu for a day, for vendor users and client accounts. Client
 * accounts see the price they pay; vendor users see the standard price.
 */
class TodayMenuController extends Controller
{
    public function __invoke(Request $request, PricingService $pricing, Settings $settings): Response
    {
        $validated = $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);
        $date = CarbonImmutable::parse($validated['date'] ?? now()->toDateString());
        $user = $request->user();

        $menus = DailyMenu::query()
            ->with('items:id,name,category,description,image_path')
            ->where('menu_date', $date->toDateString())
            ->get()
            ->keyBy(fn (DailyMenu $menu) => $menu->meal_type->value);

        $client = $user->isClientAdmin() ? $user->loadMissing('client')->client : null;
        $mealTypes = $client ? $client->mealTypes() : MealType::cases();
        // Vendor staff only see prices with financial access.
        $showVendorPrice = $user->hasPermission(Permission::ViewFinancials) || $user->hasPermission(Permission::ManagePricing);

        $meals = array_map(function (MealType $type) use ($menus, $client, $pricing, $settings, $date, $request, $showVendorPrice) {
            $menu = $menus->get($type->value);

            $price = match (true) {
                $client !== null => $pricing->priceFor($client, $type, $date)['price'],
                $showVendorPrice => $menu?->price ?? $settings->defaultMealPrice($type),
                default => null,
            };

            return [
                'meal_type' => $type->value,
                'meal_type_label' => $type->label(),
                'menu' => $menu ? DailyMenuResource::make($menu)->resolve($request) : null,
                'price' => $price,
            ];
        }, $mealTypes);

        return Inertia::render('menu/today', [
            'date' => $date->toDateString(),
            'today' => now()->toDateString(),
            'meals' => $meals,
            'priceLabel' => $client ? 'Your price' : 'Standard price',
        ]);
    }
}
