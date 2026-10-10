<?php

namespace App\Http\Resources;

use App\Models\Rider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Rider
 */
class RiderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'nid' => $this->nid,
            'address' => $this->address,
            'emergency_contact' => $this->emergency_contact,
            'vehicle_type' => $this->vehicle_type->value,
            'vehicle_type_label' => $this->vehicle_type->label(),
            'vehicle_number' => $this->vehicle_number,
            'joining_date' => $this->joining_date?->toDateString(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'notes' => $this->notes,
            'has_login' => $this->user_id !== null,
            'open_deliveries' => $this->whenCounted('openDeliveries'),
        ];
    }
}
