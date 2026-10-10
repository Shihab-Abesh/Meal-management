<?php

namespace App\Http\Controllers;

use App\Enums\MenuCategory;
use App\Enums\RecordStatus;
use App\Http\Requests\MenuItemRequest;
use App\Http\Resources\MenuItemResource;
use App\Models\MenuItem;
use App\Services\ActivityLogger;
use App\Services\MenuService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Menu items (dishes and drinks). Routes require the menu permission.
 */
class MenuItemController extends Controller
{
    public function __construct(private readonly MenuService $menus) {}

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::enum(MenuCategory::class)],
            'status' => ['nullable', Rule::enum(RecordStatus::class)],
        ]);

        $items = MenuItem::query()
            ->withCount('dailyMenus')
            ->search($filters['search'] ?? null)
            ->when($filters['category'] ?? null, fn (Builder $query, string $category) => $query->where('category', $category))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('menu-items/index', [
            'items' => MenuItemResource::collection($items),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'category' => $filters['category'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
            'categories' => MenuCategory::options(),
            'statuses' => RecordStatus::options(),
            'imageRules' => [
                'max_kb' => config('catering.menu_image.max_kb'),
                'mimes' => config('catering.menu_image.mimes'),
            ],
        ]);
    }

    public function store(MenuItemRequest $request): RedirectResponse
    {
        $item = $this->menus->saveItem(null, $request->itemAttributes(), $request->file('image'), false, $request->user());

        $this->toast("{$item->name} has been added.");

        return back();
    }

    public function update(MenuItemRequest $request, MenuItem $menuItem): RedirectResponse
    {
        $this->menus->saveItem(
            $menuItem,
            $request->itemAttributes(),
            $request->file('image'),
            $request->boolean('remove_image'),
            $request->user(),
        );

        $this->toast("{$menuItem->name} has been saved.");

        return back();
    }

    public function updateStatus(Request $request, MenuItem $menuItem, ActivityLogger $activity): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', Rule::enum(RecordStatus::class)]]);
        $status = RecordStatus::from($validated['status']);

        if ($menuItem->status !== $status) {
            $menuItem->update(['status' => $status]);
            $verb = $status === RecordStatus::Active ? 'Enabled' : 'Disabled';
            $activity->log('menu', 'item_status_changed', "{$verb} menu item {$menuItem->name}", $menuItem, ['status' => $status->value]);
        }

        $this->toast($status === RecordStatus::Active ? "{$menuItem->name} is enabled." : "{$menuItem->name} is disabled.");

        return back();
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $this->menus->deleteItem($menuItem);

        $this->toast("{$menuItem->name} has been deleted.");

        return back();
    }
}
