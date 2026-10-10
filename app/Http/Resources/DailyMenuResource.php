<?php

namespace App\Http\Resources;

use App\Models\DailyMenu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DailyMenu
 */
class DailyMenuResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'menu_date' => $this->menu_date->toDateString(),
            'meal_type' => $this->meal_type->value,
            'meal_type_label' => $this->meal_type->label(),
            'price' => $this->price,
            'notes' => $this->notes,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn (MenuItem $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $item->category->value,
                'category_label' => $item->category->label(),
                'description' => $item->description,
                'image_url' => $item->image_url,
            ])->all()),
        ];
    }
}
