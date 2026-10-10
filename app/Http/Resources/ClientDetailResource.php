<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Support\Time;
use Illuminate\Http\Request;

/**
 * Full client record for the detail and edit pages.
 *
 * @mixin Client
 */
class ClientDetailResource extends ClientResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'address' => $this->address,
            'delivery_address' => $this->delivery_address,
            'effective_delivery_address' => $this->deliveryAddress(),
            'employee_count' => $this->employee_count,
            'office_start_time' => Time::short($this->office_start_time),
            'lunch_cutoff_time' => Time::short($this->lunch_cutoff_time),
            'dinner_cutoff_time' => Time::short($this->dinner_cutoff_time),
            'special_instructions' => $this->special_instructions,
            'notes' => $this->notes,
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
