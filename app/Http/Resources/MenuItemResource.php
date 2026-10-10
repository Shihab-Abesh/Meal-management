<?php

namespace App\Http\Resources;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MenuItem
 */
class MenuItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category->value,
            'category_label' => $this->category->label(),
            'description' => $this->description,
            'default_price' => $this->default_price,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'image_url' => $this->image_url,
            'menus_count' => $this->whenCounted('dailyMenus'),
        ];
    }
}
