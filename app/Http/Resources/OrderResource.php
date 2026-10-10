<?php

namespace App\Http\Resources;

use App\Enums\Permission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\Time;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An order for vendor screens. Amounts are only included for users with
 * financial access.
 *
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $showAmounts = (bool) $request->user()?->hasPermission(Permission::ViewFinancials);

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'order_date' => $this->order_date->toDateString(),
            'meal_type' => $this->meal_type->value,
            'meal_type_label' => $this->meal_type->label(),
            'quantity' => $this->quantity,
            'amount' => $showAmounts ? $this->amount : null,
            'unit_price' => $showAmounts ? $this->unitPrice() : null,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'delivery_address' => $this->delivery_address,
            'delivery_time' => Time::short($this->delivery_time),
            'notes' => $this->notes,
            'cancel_reason' => $this->cancel_reason,
            'client' => $this->whenLoaded('client', fn () => [
                'id' => $this->client->id,
                'name' => $this->client->name,
            ]),
            'rider' => $this->whenLoaded('delivery', fn () => $this->delivery?->rider ? [
                'id' => $this->delivery->rider->id,
                'name' => $this->delivery->rider->name,
                'phone' => $this->delivery->rider->phone,
            ] : null),
            'delivery' => $this->whenLoaded('delivery', fn () => $this->delivery ? [
                'assigned_at' => $this->delivery->assigned_at?->toIso8601String(),
                'picked_up_at' => $this->delivery->picked_up_at?->toIso8601String(),
                'on_the_way_at' => $this->delivery->on_the_way_at?->toIso8601String(),
                'delivered_at' => $this->delivery->delivered_at?->toIso8601String(),
                'notes' => $this->delivery->notes,
            ] : null),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn (OrderItem $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'quantity' => $item->quantity,
            ])->all()),
            'next_statuses' => array_map(fn ($status) => ['value' => $status->value, 'label' => $status->label()], $this->status->next()),
            'can_assign' => $this->status->canAssignRider(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
