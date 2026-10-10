<?php

namespace App\Services;

use App\Enums\MealType;
use App\Models\DailyMenu;
use App\Models\MenuItem;
use App\Models\User;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Writes to menu items and daily menus, each recorded in the activity log.
 */
class MenuService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    /**
     * Create or update a menu item. A new image is stored before the
     * transaction and removed again if saving fails; the old image is only
     * deleted after a successful save.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function saveItem(?MenuItem $item, array $attributes, ?UploadedFile $image, bool $removeImage, User $user): MenuItem
    {
        $disk = Storage::disk(MenuItem::FILES_DISK);
        $newPath = $image?->store(config('catering.menu_image.directory'), MenuItem::FILES_DISK);
        $item ??= new MenuItem;
        $oldPath = $item->image_path;
        $creating = ! $item->exists;

        try {
            DB::transaction(function () use ($item, $attributes, $newPath, $removeImage, $user, $creating) {
                $item->fill([...$attributes, 'default_price' => Money::of($attributes['default_price'])]);

                if ($newPath) {
                    $item->image_path = $newPath;
                } elseif ($removeImage) {
                    $item->image_path = null;
                }

                if ($creating) {
                    $item->created_by = $user->id;
                }

                $item->save();

                if ($creating) {
                    $this->activity->log('menu', 'item_created', "Added menu item {$item->name}", $item);
                } elseif ($changes = $this->activity->changes($item)) {
                    $this->activity->log('menu', 'item_updated', "Updated menu item {$item->name}", $item, $changes);
                }
            });
        } catch (Throwable $e) {
            if ($newPath) {
                $disk->delete($newPath);
            }

            throw $e;
        }

        if ($oldPath && $oldPath !== $item->image_path) {
            $disk->delete($oldPath);
        }

        return $item;
    }

    /**
     * Delete an item that has never been on a menu. Used items are disabled
     * instead so menu history stays complete.
     */
    public function deleteItem(MenuItem $item): void
    {
        if ($item->dailyMenus()->exists()) {
            throw ValidationException::withMessages([
                'item' => "{$item->name} is on past menus, so it cannot be deleted. Disable it instead.",
            ]);
        }

        DB::transaction(function () use ($item) {
            $item->delete();
            $this->activity->log('menu', 'item_deleted', "Deleted menu item {$item->name}", null, ['id' => $item->id]);
        });

        if ($item->image_path) {
            Storage::disk(MenuItem::FILES_DISK)->delete($item->image_path);
        }
    }

    /**
     * Create or update the menu for a date and meal type.
     *
     * @param  list<int>  $itemIds  in display order
     */
    public function saveMenu(?DailyMenu $menu, string $date, MealType $type, array $itemIds, ?string $price, bool $setPrice, ?string $notes, User $user): DailyMenu
    {
        return DB::transaction(function () use ($menu, $date, $type, $itemIds, $price, $setPrice, $notes, $user) {
            $creating = $menu === null;
            $menu ??= new DailyMenu;

            $menu->fill(['menu_date' => $date, 'meal_type' => $type, 'notes' => $notes]);

            if ($setPrice) {
                $menu->price = $price === null || $price === '' ? null : Money::of($price);
            }

            if ($creating) {
                $menu->created_by = $user->id;
            }

            $menu->updated_by = $user->id;
            $menu->save();

            $menu->items()->sync($this->withOrder($itemIds));

            $label = sprintf('%s menu for %s', $type->label(), $menu->menu_date->format('d M Y'));
            $this->activity->log('menu', $creating ? 'menu_created' : 'menu_updated', ($creating ? 'Created ' : 'Updated ').mb_strtolower($label), $menu, [
                'items' => count($itemIds),
                'price' => $menu->price,
            ]);

            return $menu;
        });
    }

    /**
     * Copy a menu (items, price and notes) to another date.
     */
    public function duplicate(DailyMenu $source, string $date, User $user): DailyMenu
    {
        $exists = DailyMenu::query()->where('menu_date', $date)->where('meal_type', $source->meal_type)->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'date' => 'That date already has a '.mb_strtolower($source->meal_type->label()).' menu.',
            ]);
        }

        $itemIds = $source->items()->pluck('menu_items.id')->all();

        return $this->saveMenu(null, $date, $source->meal_type, $itemIds, $source->price, true, $source->notes, $user);
    }

    /**
     * Copy the previous week's menus into the empty slots of the week that
     * starts on $weekStart. Menus already planned are never overwritten.
     * Returns how many menus were created.
     */
    public function copyWeek(string $weekStart, User $user): int
    {
        $target = CarbonImmutable::parse($weekStart);
        $source = $target->subDays(7);

        return DB::transaction(function () use ($target, $source, $user) {
            $taken = DailyMenu::query()
                ->whereBetween('menu_date', [$target->toDateString(), $target->addDays(6)->toDateString()])
                ->get(['menu_date', 'meal_type'])
                ->map(fn (DailyMenu $menu) => $menu->menu_date->toDateString().'|'.$menu->meal_type->value)
                ->flip();

            $copied = 0;

            $menus = DailyMenu::query()
                ->with('items:id')
                ->whereBetween('menu_date', [$source->toDateString(), $source->addDays(6)->toDateString()])
                ->orderBy('menu_date')
                ->get();

            foreach ($menus as $menu) {
                $date = $menu->menu_date->addDays(7)->toDateString();

                if (isset($taken[$date.'|'.$menu->meal_type->value])) {
                    continue;
                }

                $copy = new DailyMenu(['menu_date' => $date, 'meal_type' => $menu->meal_type, 'price' => $menu->price, 'notes' => $menu->notes]);
                $copy->created_by = $user->id;
                $copy->updated_by = $user->id;
                $copy->save();
                $copy->items()->sync($this->withOrder($menu->items->pluck('id')->all()));
                $copied++;
            }

            if ($copied > 0) {
                $this->activity->log('menu', 'week_copied', sprintf(
                    'Copied %d menus from the week of %s to the week of %s',
                    $copied,
                    $source->format('d M'),
                    $target->format('d M Y'),
                ), null, ['from' => $source->toDateString(), 'to' => $target->toDateString(), 'copied' => $copied], $user);
            }

            return $copied;
        });
    }

    public function deleteMenu(DailyMenu $menu): void
    {
        DB::transaction(function () use ($menu) {
            $label = sprintf('%s menu for %s', $menu->meal_type->label(), $menu->menu_date->format('d M Y'));
            $menu->delete();
            $this->activity->log('menu', 'menu_deleted', 'Deleted '.mb_strtolower($label), null, ['id' => $menu->id]);
        });
    }

    /**
     * @param  list<int>  $itemIds
     * @return array<int, array{sort_order: int}>
     */
    private function withOrder(array $itemIds): array
    {
        $synced = [];

        foreach (array_values($itemIds) as $position => $id) {
            $synced[(int) $id] = ['sort_order' => $position];
        }

        return $synced;
    }
}
