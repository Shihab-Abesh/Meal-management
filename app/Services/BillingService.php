<?php

namespace App\Services;

use App\Enums\BillStatus;
use App\Enums\MealType;
use App\Enums\PaymentMethod;
use App\Models\Bill;
use App\Models\Client;
use App\Models\MealEntry;
use App\Models\Payment;
use App\Models\User;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Bills and payments. Totals = confirmed meals in the period × the client
 * price each meal was confirmed at. Every write runs in a transaction and is
 * recorded in the activity log.
 */
class BillingService
{
    /**
     * Longest period a single bill may cover.
     */
    public const MAX_PERIOD_DAYS = 92;

    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly Settings $settings,
        private readonly Notifier $notifier,
        private readonly VendorNotifier $vendorAlerts,
    ) {}

    /**
     * Meal counts and amounts for a client and period, grouped by meal type
     * and unit price (a price change mid-month gives two lines).
     *
     * @return array{lunch_meals: int, dinner_meals: int, total_meals: int, total_amount: string, lines: list<array{meal_type: string, unit_price: string, quantity: int, amount: string}>}
     */
    public function calculate(int $clientId, string $start, string $end): array
    {
        $rows = MealEntry::query()
            ->confirmed()
            ->where('client_id', $clientId)
            ->whereBetween('meal_date', [$start, $end])
            ->selectRaw('meal_type, unit_price, COUNT(*) as quantity')
            ->groupBy('meal_type', 'unit_price')
            ->toBase()
            ->get()
            ->sortBy(fn ($row) => [$row->meal_type === MealType::Lunch->value ? 0 : 1, (float) $row->unit_price]);

        $lines = [];
        $counts = ['lunch' => 0, 'dinner' => 0];
        $total = '0.00';

        foreach ($rows as $row) {
            $quantity = (int) $row->quantity;
            $amount = Money::multiply(Money::of($row->unit_price), $quantity);
            $counts[$row->meal_type] = ($counts[$row->meal_type] ?? 0) + $quantity;
            $total = Money::add($total, $amount);

            $lines[] = [
                'meal_type' => $row->meal_type,
                'unit_price' => Money::of($row->unit_price),
                'quantity' => $quantity,
                'amount' => $amount,
            ];
        }

        return [
            'lunch_meals' => $counts['lunch'],
            'dinner_meals' => $counts['dinner'],
            'total_meals' => $counts['lunch'] + $counts['dinner'],
            'total_amount' => $total,
            'lines' => $lines,
        ];
    }

    /**
     * @throws ValidationException
     */
    public function generate(Client $client, string $start, string $end, User $user, ?string $notes = null): Bill
    {
        $bill = DB::transaction(function () use ($client, $start, $end, $user, $notes) {
            // One bill generation per client at a time, so overlap checks hold.
            Client::query()->whereKey($client->id)->lockForUpdate()->first();

            $this->guardPeriod($client, $start, $end);

            $totals = $this->calculate($client->id, $start, $end);

            if ($totals['total_meals'] === 0) {
                throw ValidationException::withMessages([
                    'period_start' => 'There are no confirmed meals for this client in that period.',
                ]);
            }

            $issueDate = now()->toDateString();
            $dueDate = now()->addDays($this->settings->billDueDays())->toDateString();

            $bill = Bill::create([
                ...$totals,
                'client_id' => $client->id,
                'period_start' => $start,
                'period_end' => $end,
                'paid_amount' => '0.00',
                'due_amount' => $totals['total_amount'],
                'status' => BillStatus::resolve($totals['total_amount'], '0.00', $dueDate, $issueDate),
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'notes' => $notes,
                'created_by' => $user->id,
            ]);
            $bill->update(['bill_number' => Bill::numberFor($bill->id, $issueDate)]);

            $this->activity->log('billing', 'bill_generated', sprintf(
                'Bill %s for %s: %d meals, %s (%s)',
                $bill->bill_number,
                $client->name,
                $bill->total_meals,
                $bill->total_amount,
                $this->periodLabel($start, $end),
            ), $bill, ['total_meals' => $bill->total_meals, 'total_amount' => $bill->total_amount], $user);

            return $bill;
        });

        $this->notifier->billIssued($bill);

        return $bill;
    }

    /**
     * Recount the bill from the meals, e.g. after meals were corrected with
     * a cutoff override. Payments are kept.
     *
     * @throws ValidationException
     */
    public function recalculate(Bill $bill, User $user): Bill
    {
        return DB::transaction(function () use ($bill, $user) {
            $bill = Bill::query()->lockForUpdate()->findOrFail($bill->id);
            $totals = $this->calculate($bill->client_id, $bill->period_start->toDateString(), $bill->period_end->toDateString());

            if (Money::compare($totals['total_amount'], $bill->paid_amount) < 0) {
                throw ValidationException::withMessages([
                    'bill' => "The new total ({$totals['total_amount']}) would be less than what has already been paid ({$bill->paid_amount}). Remove a payment first.",
                ]);
            }

            $old = ['total_meals' => $bill->total_meals, 'total_amount' => $bill->total_amount];

            $bill->fill([...$totals, 'due_amount' => Money::subtract($totals['total_amount'], $bill->paid_amount)]);
            $bill->status = $bill->currentStatus();
            $bill->save();

            $this->activity->log('billing', 'bill_recalculated', sprintf(
                'Bill %s recalculated: %d → %d meals, %s → %s',
                $bill->bill_number,
                $old['total_meals'],
                $bill->total_meals,
                $old['total_amount'],
                $bill->total_amount,
            ), $bill, ['old' => $old, 'new' => ['total_meals' => $bill->total_meals, 'total_amount' => $bill->total_amount]], $user);

            return $bill;
        });
    }

    /**
     * Whether the bill can be deleted: only if no payment was ever recorded
     * against it (removed payments are kept as part of the money trail).
     */
    public function canDelete(Bill $bill): bool
    {
        return ! Payment::withTrashed()->where('bill_id', $bill->id)->exists();
    }

    /**
     * Delete a bill without payment history, e.g. to bill a different period.
     *
     * @throws ValidationException
     */
    public function delete(Bill $bill, User $user): void
    {
        DB::transaction(function () use ($bill, $user) {
            $bill = Bill::query()->with('client:id,name')->lockForUpdate()->findOrFail($bill->id);

            if (! $this->canDelete($bill)) {
                throw ValidationException::withMessages(['bill' => 'Payments have been recorded against this bill, so it cannot be deleted. Recalculate it instead.']);
            }

            $bill->delete();

            $this->activity->log('billing', 'bill_deleted', "Deleted bill {$bill->bill_number} for {$bill->client->name}", $bill->client, [
                'bill_id' => $bill->id,
                'total_amount' => $bill->total_amount,
            ], $user);
        });
    }

    /**
     * Record money received against a bill. The amount may not exceed what
     * is still due.
     *
     * @param  array{payment_date: string, amount: string, method: string, reference?: string|null, note?: string|null}  $data
     *
     * @throws ValidationException
     */
    public function recordPayment(Bill $bill, array $data, User $user): Payment
    {
        $payment = DB::transaction(function () use ($bill, $data, $user) {
            $bill = Bill::query()->with('client:id,name')->lockForUpdate()->findOrFail($bill->id);
            $amount = Money::of($data['amount']);

            if (! Money::isPositive($amount)) {
                throw ValidationException::withMessages(['amount' => 'Enter an amount greater than zero.']);
            }

            if (Money::compare($amount, $bill->due_amount) > 0) {
                throw ValidationException::withMessages(['amount' => "The amount is more than the {$bill->due_amount} still due on this bill."]);
            }

            $payment = Payment::create([
                'bill_id' => $bill->id,
                'client_id' => $bill->client_id,
                'payment_date' => $data['payment_date'],
                'amount' => $amount,
                'method' => PaymentMethod::from($data['method']),
                'reference' => $data['reference'] ?? null,
                'note' => $data['note'] ?? null,
                'received_by' => $user->id,
            ]);

            $this->applyPayments($bill);

            $this->activity->log('billing', 'payment_added', sprintf(
                'Payment of %s (%s) received from %s for bill %s',
                $amount,
                $payment->method->label(),
                $bill->client->name,
                $bill->bill_number,
            ), $payment, ['bill_id' => $bill->id, 'amount' => $amount, 'method' => $payment->method->value, 'due_after' => $bill->due_amount], $user);

            return $payment->setRelation('bill', $bill);
        });

        $this->notifier->paymentReceived($payment);

        return $payment;
    }

    /**
     * Remove a payment recorded by mistake. It is soft deleted and the bill
     * owes that amount again.
     */
    public function removePayment(Payment $payment, User $user): void
    {
        DB::transaction(function () use ($payment, $user) {
            $bill = Bill::query()->with('client:id,name')->lockForUpdate()->findOrFail($payment->bill_id);

            $payment->delete();
            $this->applyPayments($bill);

            $this->activity->log('billing', 'payment_removed', sprintf(
                'Removed payment of %s from %s (bill %s)',
                $payment->amount,
                $bill->client->name,
                $bill->bill_number,
            ), $payment, ['bill_id' => $bill->id, 'amount' => $payment->amount, 'due_after' => $bill->due_amount], $user);
        });
    }

    /**
     * Store "overdue" on bills past their due date with money owed. Run
     * nightly by the scheduler; returns how many bills changed.
     */
    public function markOverdue(): int
    {
        $becoming = Bill::query()
            ->whereIn('status', [BillStatus::Unpaid, BillStatus::PartiallyPaid])
            ->where('due_amount', '>', 0)
            ->where('due_date', '<', now()->toDateString());

        $due = Money::of((clone $becoming)->sum('due_amount'));
        $count = $becoming->update(['status' => BillStatus::Overdue, 'updated_at' => now()]);

        $this->vendorAlerts->billsOverdue($count, $due);

        return $count;
    }

    /**
     * The first day not yet covered by a bill for the client, offered as the
     * start of the next billing period.
     */
    public function nextPeriodStart(Client $client): ?string
    {
        return $client->bills()->max('period_end')
            ? CarbonImmutable::parse($client->bills()->max('period_end'))->addDay()->toDateString()
            : null;
    }

    public function periodLabel(string $start, string $end): string
    {
        $from = CarbonImmutable::parse($start);
        $to = CarbonImmutable::parse($end);

        if ($from->isSameDay($from->startOfMonth()) && $to->isSameDay($from->endOfMonth())) {
            return $from->format('F Y');
        }

        return $from->format('d M Y').' – '.$to->format('d M Y');
    }

    /**
     * Re-total the bill's payments and update paid, due and status.
     */
    private function applyPayments(Bill $bill): void
    {
        $paid = Money::of(Payment::query()->where('bill_id', $bill->id)->sum('amount'));

        $bill->paid_amount = $paid;
        $bill->due_amount = Money::subtract($bill->total_amount, $paid);
        $bill->status = $bill->currentStatus();
        $bill->save();
    }

    /**
     * @throws ValidationException
     */
    private function guardPeriod(Client $client, string $start, string $end): void
    {
        $days = CarbonImmutable::parse($start)->diffInDays(CarbonImmutable::parse($end)) + 1;

        if ($days > self::MAX_PERIOD_DAYS) {
            throw ValidationException::withMessages(['period_end' => 'A bill can cover at most '.self::MAX_PERIOD_DAYS.' days.']);
        }

        $overlap = Bill::query()
            ->where('client_id', $client->id)
            ->where('period_start', '<=', $end)
            ->where('period_end', '>=', $start)
            ->orderBy('period_start')
            ->first();

        if ($overlap) {
            throw ValidationException::withMessages([
                'period_start' => sprintf(
                    'This period overlaps bill %s (%s). Meals can only be billed once.',
                    $overlap->bill_number,
                    $this->periodLabel($overlap->period_start->toDateString(), $overlap->period_end->toDateString()),
                ),
            ]);
        }
    }
}
