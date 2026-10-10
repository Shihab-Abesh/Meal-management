<?php

namespace Database\Factories;

use App\Enums\BillStatus;
use App\Models\Bill;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A bill with fixed totals. Tests that check calculations generate bills
 * through BillingService instead.
 *
 * @extends Factory<Bill>
 */
class BillFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'period_start' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
            'period_end' => now()->subMonthNoOverflow()->endOfMonth()->toDateString(),
            'lunch_meals' => 10,
            'dinner_meals' => 0,
            'total_meals' => 10,
            'total_amount' => '1200.00',
            'paid_amount' => '0.00',
            'due_amount' => '1200.00',
            'status' => BillStatus::Unpaid,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'lines' => [['meal_type' => 'lunch', 'unit_price' => '120.00', 'quantity' => 10, 'amount' => '1200.00']],
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Bill $bill) {
            $bill->update(['bill_number' => Bill::numberFor($bill->id, $bill->issue_date->toDateString())]);
        });
    }
}
