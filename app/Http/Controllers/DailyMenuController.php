<?php

namespace App\Http\Controllers;

use App\Enums\MealType;
use App\Enums\Permission;
use App\Http\Requests\DailyMenuRequest;
use App\Models\DailyMenu;
use App\Models\MenuItem;
use App\Services\MenuService;
use App\Services\Settings;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The menu chart: what is served each day, a week or a month at a time,
 * planned and edited in place. Routes require the menu permission.
 */
class DailyMenuController extends Controller
{
    /**
     * Weeks run Saturday to Friday, with the weekly holiday last.
     */
    public const WEEK_STARTS = CarbonInterface::SATURDAY;

    public function __construct(private readonly MenuService $menus) {}

    public function index(Request $request, Settings $settings): Response
    {
        $validated = $request->validate([
            'view' => ['nullable', Rule::in(['week', 'month'])],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $view = $validated['view'] ?? 'week';
        $anchor = CarbonImmutable::parse($validated['date'] ?? now()->toDateString());

        // A month is shown as whole weeks, so the grid has no gaps.
        [$start, $end] = $view === 'week'
            ? [$anchor->startOfWeek(self::WEEK_STARTS), $anchor->startOfWeek(self::WEEK_STARTS)->addDays(6)]
            : [$anchor->startOfMonth()->startOfWeek(self::WEEK_STARTS), $anchor->endOfMonth()->endOfWeek($this->weekEnds())];

        $menus = DailyMenu::query()
            ->with('items:id,name,category')
            ->whereBetween('menu_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        return Inertia::render('daily-menus/index', [
            'view' => $view,
            'date' => $anchor->toDateString(),
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'today' => now()->toDateString(),
            'menus' => $menus->map(fn (DailyMenu $menu) => $this->present($menu))->values(),
            'previous' => $this->latestBefore($start->toDateString()),
            'items' => MenuItem::query()->orderBy('name')->get(['id', 'name', 'category', 'status'])
                ->map(fn (MenuItem $item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category->value,
                    'category_label' => $item->category->label(),
                    'active' => $item->status->value === 'active',
                ]),
            'mealTypes' => MealType::options(),
            'canPrice' => $request->user()->can(Permission::ManagePricing->value),
            'defaultPrices' => [
                'lunch' => $settings->defaultMealPrice(MealType::Lunch),
                'dinner' => $settings->defaultMealPrice(MealType::Dinner),
            ],
        ]);
    }

    public function store(DailyMenuRequest $request): RedirectResponse
    {
        $menu = $this->menus->saveMenu(
            null,
            $request->validated('menu_date'),
            MealType::from($request->validated('meal_type')),
            $request->validated('items'),
            $request->validated('price'),
            $request->canSetPrice(),
            $request->validated('notes'),
            $request->user(),
        );

        $this->toast($menu->meal_type->label().' menu saved for '.$menu->menu_date->format('D, d M').'.');

        return back();
    }

    public function update(DailyMenuRequest $request, DailyMenu $dailyMenu): RedirectResponse
    {
        $this->menus->saveMenu(
            $dailyMenu,
            $request->validated('menu_date'),
            MealType::from($request->validated('meal_type')),
            $request->validated('items'),
            $request->validated('price'),
            $request->canSetPrice(),
            $request->validated('notes'),
            $request->user(),
        );

        $this->toast('Menu saved.');

        return back();
    }

    public function duplicate(Request $request, DailyMenu $dailyMenu): RedirectResponse
    {
        $validated = $request->validate(['date' => ['required', 'date_format:Y-m-d']]);

        $copy = $this->menus->duplicate($dailyMenu, $validated['date'], $request->user());

        $this->toast('Menu copied to '.$copy->menu_date->format('D, d M').'.');

        return back();
    }

    /**
     * Fill the empty slots of a week with the previous week's menus.
     */
    public function copyWeek(Request $request): RedirectResponse
    {
        $validated = $request->validate(['week' => ['required', 'date_format:Y-m-d']]);
        $week = CarbonImmutable::parse($validated['week'])->startOfWeek(self::WEEK_STARTS);

        $copied = $this->menus->copyWeek($week->toDateString(), $request->user());

        $this->toast(
            $copied > 0 ? "{$copied} menus copied from last week." : 'Nothing to copy: last week has no menus for the empty days.',
            $copied > 0 ? 'success' : 'info',
        );

        return back();
    }

    public function destroy(DailyMenu $dailyMenu): RedirectResponse
    {
        $this->menus->deleteMenu($dailyMenu);

        $this->toast('Menu deleted.');

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function present(DailyMenu $menu): array
    {
        return [
            'id' => $menu->id,
            'date' => $menu->menu_date->toDateString(),
            'meal_type' => $menu->meal_type->value,
            'price' => $menu->price,
            'notes' => $menu->notes,
            'items' => $menu->items->map(fn (MenuItem $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $item->category->value,
            ])->all(),
        ];
    }

    /**
     * The latest menu of each meal before the shown range, offered in the
     * editor as "copy previous" when nothing earlier is on screen.
     *
     * @return array<string, array<string, mixed>|null>
     */
    private function latestBefore(string $date): array
    {
        $latest = [];

        foreach (MealType::cases() as $type) {
            $menu = DailyMenu::query()
                ->with('items:id,name,category')
                ->where('meal_type', $type)
                ->where('menu_date', '<', $date)
                ->orderByDesc('menu_date')
                ->first();

            $latest[$type->value] = $menu ? $this->present($menu) : null;
        }

        return $latest;
    }

    private function weekEnds(): int
    {
        return (self::WEEK_STARTS + 6) % 7;
    }
}
