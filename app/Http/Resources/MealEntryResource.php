<?php

namespace App\Http\Resources;

use App\Enums\Permission;
use App\Models\MealEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A meal entry in history lists. Prices are only included for client
 * accounts (their own spend) and vendor users with financial access.
 *
 * @mixin MealEntry
 */
class MealEntryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $showAmounts = $user?->isClientAdmin() || $user?->hasPermission(Permission::ViewFinancials);

        return [
            'id' => $this->id,
            'meal_date' => $this->meal_date->toDateString(),
            'meal_type' => $this->meal_type->value,
            'meal_type_label' => $this->meal_type->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'unit_price' => $showAmounts ? $this->unit_price : null,
            'employee' => $this->whenLoaded('employee', fn () => [
                'id' => $this->employee->id,
                'name' => $this->employee->name,
                'code' => $this->employee->employee_code,
            ]),
            'client' => $this->when(
                (bool) $user?->isVendorUser() && $this->relationLoaded('client'),
                fn () => ['id' => $this->client->id, 'name' => $this->client->name],
            ),
            'confirmed_by' => $this->whenLoaded('confirmer', fn () => $this->confirmer?->name),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
