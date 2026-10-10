<?php

namespace App\Http\Resources;

use App\Enums\MealType;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Client summary used in lists.
 *
 * @mixin Client
 */
class ClientResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'contact_person' => $this->contact_person,
            'phone' => $this->phone,
            'email' => $this->email,
            'city' => $this->city,
            'meal_types' => array_map(fn (MealType $type) => $type->value, $this->mealTypes()),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'archived' => $this->trashed(),
            'employees_count' => $this->whenCounted('employees'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
