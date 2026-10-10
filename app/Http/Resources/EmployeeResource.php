<?php

namespace App\Http\Resources;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Employee
 */
class EmployeeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client' => $this->whenLoaded('client', fn () => ['id' => $this->client->id, 'name' => $this->client->name]),
            'employee_code' => $this->employee_code,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'department' => $this->department,
            'designation' => $this->designation,
            'meal_preference' => $this->meal_preference->value,
            'meal_preference_label' => $this->meal_preference->label(),
            'lunch_enabled' => $this->lunch_enabled,
            'dinner_enabled' => $this->dinner_enabled,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'joining_date' => $this->joining_date?->toDateString(),
        ];
    }
}
