<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Client;
use App\Models\MealEntry;
use App\Models\Payment;
use App\Support\Money;
use Carbon\CarbonImmutable;

/**
 * A client's account: balance across all bills, meals not billed yet, and
 * the monthly statement (meals and amount per day).
 */
class ClientStatement
{
    /**
     * @return array{billed: string, paid: string, due: string, overdue: string}
     */
    public function balance(Client $client): array
    {
        $today = now()->toDateString();
        $row = Bill::query()
            ->where('client_id', $client->id)
            ->selectRaw('COALESCE(SUM(total_amount), 0) as billed, COALESCE(SUM(paid_amount), 0) as paid, COALESCE(SUM(due_amount), 0) as due')
            ->selectRaw('COALESCE(SUM(CASE WHEN due_date < ? THEN due_amount ELSE 0 END), 0) as overdue', [$today])
            ->toBase()
            ->first();

        return [
            'billed' => Money::of($row->billed),
            'paid' => Money::of($row->paid),
            'due' => Money::of($row->due),
            'overdue' => Money::of($row->overdue),
        ];
    }

    /**
     * Confirmed meals up to today that no bill covers yet.
     *
     * @return array{from: string|null, to: string, meals: int, amount: string}
     */
    public function unbilled(Client $client): array
    {
        $today = now()->toDateString();
        $lastEnd = Bill::query()->where('client_id', $client->id)->max('period_end');
        $from = $lastEnd ? CarbonImmutable::parse($lastEnd)->addDay()->toDateString() : null;

        $row = MealEntry::query()
            ->confirmed()
            ->where('client_id', $client->id)
            ->where('meal_date', '<=', $today)
            ->when($from, fn ($query) => $query->where('meal_date', '>=', $from))
            ->selectRaw('COUNT(*) as meals, COALESCE(SUM(unit_price), 0) as amount, MIN(meal_date) as first_date')
            ->toBase()
            ->first();

        return [
            'from' => $from ?? ($row->first_date ? substr((string) $row->first_date, 0, 10) : null),
            'to' => $today,
            'meals' => (int) $row->meals,
            'amount' => Money::of($row->amount),
        ];
    }

    /**
     * Meals and amount for each day of the month, with the bills covering
     * the month and the payments made in it.
     *
     * @return array<string, mixed>
     */
    public function month(Client $client, CarbonImmutable $month): array
    {
        $start = $month->startOfMonth()->toDateString();
        $end = $month->endOfMonth()->toDateString();

        $rows = MealEntry::query()
            ->confirmed()
            ->where('client_id', $client->id)
            ->whereBetween('meal_date', [$start, $end])
            ->selectRaw('meal_date, meal_type, COUNT(*) as meals, COALESCE(SUM(unit_price), 0) as amount')
            ->groupBy('meal_date', 'meal_type')
            ->orderBy('meal_date')
            ->toBase()
            ->get();

        $days = [];

        foreach ($rows as $row) {
            $date = substr((string) $row->meal_date, 0, 10);
            $days[$date] ??= ['date' => $date, 'lunch' => 0, 'dinner' => 0, 'meals' => 0, 'amount' => '0.00'];
            $days[$date][$row->meal_type] = (int) $row->meals;
            $days[$date]['meals'] += (int) $row->meals;
            $days[$date]['amount'] = Money::add($days[$date]['amount'], Money::of($row->amount));
        }

        $days = array_values($days);

        return [
            'days' => $days,
            'totals' => [
                'lunch' => array_sum(array_column($days, 'lunch')),
                'dinner' => array_sum(array_column($days, 'dinner')),
                'meals' => array_sum(array_column($days, 'meals')),
                'amount' => Money::add(...array_column($days, 'amount')),
            ],
            'bills' => Bill::query()
                ->where('client_id', $client->id)
                ->where('period_start', '<=', $end)
                ->where('period_end', '>=', $start)
                ->orderBy('period_start')
                ->get(),
            'payments' => Payment::query()
                ->with('bill:id,bill_number')
                ->where('client_id', $client->id)
                ->whereBetween('payment_date', [$start, $end])
                ->orderBy('payment_date')
                ->get(),
        ];
    }
}
