<?php

namespace App\Http\Resources;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payment
 */
class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_date' => $this->payment_date->toDateString(),
            'amount' => $this->amount,
            'method' => $this->method->value,
            'method_label' => $this->method->label(),
            'reference' => $this->reference,
            'note' => $this->note,
            'bill' => $this->whenLoaded('bill', fn () => ['id' => $this->bill->id, 'bill_number' => $this->bill->bill_number]),
            'client' => $this->whenLoaded('client', fn () => ['id' => $this->client->id, 'name' => $this->client->name]),
            // Who took the money is internal to the vendor.
            'received_by' => $this->when(
                (bool) $request->user()?->isVendorUser() && $this->relationLoaded('receiver'),
                fn () => $this->receiver?->name,
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
