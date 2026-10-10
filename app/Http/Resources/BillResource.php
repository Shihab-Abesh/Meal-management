<?php

namespace App\Http\Resources;

use App\Enums\MealType;
use App\Models\Bill;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Bill
 */
class BillResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->currentStatus();
        $start = $this->period_start->toDateString();
        $end = $this->period_end->toDateString();

        return [
            'id' => $this->id,
            'bill_number' => $this->bill_number,
            'client' => $this->whenLoaded('client', fn () => ['id' => $this->client->id, 'name' => $this->client->name]),
            'period_start' => $start,
            'period_end' => $end,
            'period_label' => app(BillingService::class)->periodLabel($start, $end),
            'lunch_meals' => $this->lunch_meals,
            'dinner_meals' => $this->dinner_meals,
            'total_meals' => $this->total_meals,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'due_amount' => $this->due_amount,
            'status' => $status->value,
            'status_label' => $status->label(),
            'issue_date' => $this->issue_date->toDateString(),
            'due_date' => $this->due_date->toDateString(),
            'lines' => array_map(fn (array $line) => [
                ...$line,
                'meal_type_label' => MealType::from($line['meal_type'])->label(),
            ], $this->lines ?? []),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
