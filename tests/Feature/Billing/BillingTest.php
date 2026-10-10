<?php

namespace Tests\Feature\Billing;

use App\Enums\BillStatus;
use App\Enums\MealEntryStatus;
use App\Enums\Permission;
use App\Models\Bill;
use App\Models\Client;
use App\Models\Employee;
use App\Models\MealEntry;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\BillIssued;
use App\Notifications\PaymentReceived;
use App\Services\Settings;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-10-07 10:00:00'));
        $this->admin = User::factory()->vendorAdmin()->create();
        $this->client = Client::factory()->withDinner()->create();
    }

    public function test_bill_total_is_meals_times_the_client_price(): void
    {
        // 60 employees × 20 days = 1,200 meals × ৳120 = ৳144,000
        $this->addMeals($this->client, 60, '2026-09-01', 20, '120.00');

        $this->generate()->assertSessionHasNoErrors();

        $bill = Bill::sole();
        $this->assertSame(1200, $bill->total_meals);
        $this->assertSame(1200, $bill->lunch_meals);
        $this->assertSame('144000.00', $bill->total_amount);
        $this->assertSame('0.00', $bill->paid_amount);
        $this->assertSame('144000.00', $bill->due_amount);
        $this->assertSame(BillStatus::Unpaid, $bill->status);
        $this->assertSame('2026-10-07', $bill->issue_date->toDateString());
        $this->assertSame('2026-10-22', $bill->due_date->toDateString());
        $this->assertSame(sprintf('BILL-2610-%05d', $bill->id), $bill->bill_number);
        $this->assertSame([['meal_type' => 'lunch', 'unit_price' => '120.00', 'quantity' => 1200, 'amount' => '144000.00']], $bill->lines);
        $this->assertDatabaseHas('activity_logs', ['module' => 'billing', 'action' => 'bill_generated', 'subject_id' => $bill->id]);
    }

    public function test_each_client_is_billed_at_its_own_prices(): void
    {
        $other = Client::factory()->create();
        $this->addMeals($this->client, 5, '2026-09-01', 2, '120.00');
        $this->addMeals($other, 5, '2026-09-01', 2, '150.00');

        $this->generate()->assertSessionHasNoErrors();
        $this->generate(['client_id' => $other->id])->assertSessionHasNoErrors();

        $this->assertSame('1200.00', Bill::where('client_id', $this->client->id)->value('total_amount'));
        $this->assertSame('1500.00', Bill::where('client_id', $other->id)->value('total_amount'));
    }

    public function test_a_price_change_inside_the_period_gives_two_lines(): void
    {
        $this->addMeals($this->client, 4, '2026-09-01', 10, '120.00');
        $this->addMeals($this->client, 4, '2026-09-15', 5, '130.00');
        $this->addMeals($this->client, 2, '2026-09-01', 3, '140.00', 'dinner');

        $this->generate()->assertSessionHasNoErrors();

        $bill = Bill::sole();
        // 40 × 120 + 20 × 130 + 6 × 140 = 4,800 + 2,600 + 840 = 8,240
        $this->assertSame('8240.00', $bill->total_amount);
        $this->assertSame(60, $bill->lunch_meals);
        $this->assertSame(6, $bill->dinner_meals);
        $this->assertSame(['120.00', '130.00', '140.00'], array_column($bill->lines, 'unit_price'));
    }

    public function test_only_confirmed_meals_of_the_client_inside_the_period_are_billed(): void
    {
        $this->addMeals($this->client, 3, '2026-09-30', 1, '120.00');
        $this->addMeals($this->client, 3, '2026-10-01', 1, '120.00'); // next period
        $this->addMeals($this->client, 2, '2026-09-10', 1, '120.00', 'lunch', MealEntryStatus::Cancelled);
        $this->addMeals(Client::factory()->create(), 7, '2026-09-10', 1, '120.00'); // another client

        $this->generate()->assertSessionHasNoErrors();

        $this->assertSame(3, Bill::sole()->total_meals);
        $this->assertSame('360.00', Bill::sole()->total_amount);
    }

    public function test_periods_cannot_overlap_and_must_contain_meals(): void
    {
        $this->addMeals($this->client, 2, '2026-09-01', 30, '120.00');
        $this->generate()->assertSessionHasNoErrors();

        $this->generate(['period_start' => '2026-09-20', 'period_end' => '2026-10-05'])->assertSessionHasErrors('period_start');
        $this->generate(['period_start' => '2026-08-01', 'period_end' => '2026-08-31'])->assertSessionHasErrors('period_start');
        $this->generate(['period_start' => '2026-09-10', 'period_end' => '2026-09-01'])->assertSessionHasErrors('period_end');
        $this->generate(['period_start' => '2026-01-01', 'period_end' => '2026-06-30'])->assertSessionHasErrors('period_end');

        $this->assertSame(1, Bill::count());
    }

    public function test_full_payment_settles_the_bill(): void
    {
        $bill = $this->billFor(1200);

        $this->pay($bill, '1200.00')->assertSessionHasNoErrors();

        $bill->refresh();
        $this->assertSame('1200.00', $bill->paid_amount);
        $this->assertSame('0.00', $bill->due_amount);
        $this->assertSame(BillStatus::Paid, $bill->status);
        $this->assertDatabaseHas('activity_logs', ['module' => 'billing', 'action' => 'payment_added']);
    }

    public function test_partial_and_multiple_payments_add_up(): void
    {
        $bill = $this->billFor(1200);

        $this->pay($bill, '500.00', 'bkash', 'TX123')->assertSessionHasNoErrors();
        $bill->refresh();
        $this->assertSame(BillStatus::PartiallyPaid, $bill->status);
        $this->assertSame('700.00', $bill->due_amount);

        $this->pay($bill, '200.50', 'nagad')->assertSessionHasNoErrors();
        $this->pay($bill, '499.50', 'bank')->assertSessionHasNoErrors();

        $bill->refresh();
        $this->assertSame('1200.00', $bill->paid_amount);
        $this->assertSame('0.00', $bill->due_amount);
        $this->assertSame(BillStatus::Paid, $bill->status);
        $this->assertSame(3, $bill->payments()->count());
        $this->assertSame('TX123', Payment::where('method', 'bkash')->value('reference'));
    }

    public function test_payment_validation(): void
    {
        $bill = $this->billFor(1200);

        $this->pay($bill, '1200.01')->assertSessionHasErrors('amount');
        $this->pay($bill, '0')->assertSessionHasErrors('amount');
        $this->pay($bill, '10.555')->assertSessionHasErrors('amount');
        $this->pay($bill, '100', 'cheque')->assertSessionHasErrors('method');
        $this->actingAs($this->admin)
            ->post("/bills/{$bill->id}/payments", ['payment_date' => '2026-10-08', 'amount' => '100', 'method' => 'cash'])
            ->assertSessionHasErrors('payment_date');

        $this->assertSame(0, Payment::count());
        $this->assertSame('1200.00', $bill->fresh()->due_amount);
    }

    public function test_removing_a_payment_makes_the_amount_due_again(): void
    {
        $bill = $this->billFor(1200);
        $this->pay($bill, '1200.00');
        $payment = Payment::sole();

        $this->actingAs($this->admin)->delete("/payments/{$payment->id}")->assertSessionHasNoErrors();

        $bill->refresh();
        $this->assertSame('0.00', $bill->paid_amount);
        $this->assertSame('1200.00', $bill->due_amount);
        $this->assertSame(BillStatus::Unpaid, $bill->status);
        $this->assertSoftDeleted($payment);
        $this->assertDatabaseHas('activity_logs', ['action' => 'payment_removed']);
    }

    public function test_bills_become_overdue_after_the_due_date(): void
    {
        $bill = $this->billFor(1200);
        $this->pay($bill, '200.00');

        $this->travelTo(CarbonImmutable::parse('2026-10-23 09:00:00'));

        // Shown as overdue straight away, stored by the nightly job.
        $this->assertSame(BillStatus::Overdue, $bill->fresh()->currentStatus());
        $this->actingAs($this->admin)->get('/bills?status=overdue')->assertInertia(fn ($page) => $page->has('bills.data', 1)->where('bills.data.0.status', 'overdue'));
        $this->actingAs($this->admin)->get('/bills?status=partially_paid')->assertInertia(fn ($page) => $page->has('bills.data', 0));

        $this->artisan('bills:mark-overdue')->assertSuccessful();
        $this->assertSame(BillStatus::Overdue, $bill->fresh()->status);

        // Paying the rest settles it.
        $this->pay($bill, '1000.00');
        $this->assertSame(BillStatus::Paid, $bill->fresh()->status);
    }

    public function test_recalculate_follows_meal_corrections_and_keeps_payments(): void
    {
        $this->addMeals($this->client, 10, '2026-09-01', 1, '120.00');
        $this->generate();
        $bill = Bill::sole();
        $this->pay($bill, '600.00');

        // Four meals are cancelled after the bill was made (cutoff override).
        MealEntry::query()->limit(4)->get()->each->update(['status' => MealEntryStatus::Cancelled]);

        $this->actingAs($this->admin)->get("/bills/{$bill->id}")->assertInertia(fn ($page) => $page->where('outOfDate', true));
        $this->actingAs($this->admin)->post("/bills/{$bill->id}/recalculate")->assertSessionHasNoErrors();

        $bill->refresh();
        $this->assertSame(6, $bill->total_meals);
        $this->assertSame('720.00', $bill->total_amount);
        $this->assertSame('600.00', $bill->paid_amount);
        $this->assertSame('120.00', $bill->due_amount);

        // The total can never drop below what was paid.
        MealEntry::query()->confirmed()->limit(2)->get()->each->update(['status' => MealEntryStatus::Cancelled]);
        $this->actingAs($this->admin)->post("/bills/{$bill->id}/recalculate")->assertSessionHasErrors('bill');
        $this->assertSame('720.00', $bill->fresh()->total_amount);
    }

    public function test_only_bills_without_payment_history_can_be_deleted(): void
    {
        $bill = $this->billFor(1200);
        $this->pay($bill, '100.00');

        $this->actingAs($this->admin)->delete("/bills/{$bill->id}")->assertSessionHasErrors('bill');

        // A removed payment still counts as history.
        $this->actingAs($this->admin)->delete('/payments/'.Payment::sole()->id);
        $this->actingAs($this->admin)->delete("/bills/{$bill->id}")->assertSessionHasErrors('bill');
        $this->assertModelExists($bill);

        $fresh = $this->billFor(500, Client::factory()->create());
        $this->actingAs($this->admin)->get("/bills/{$fresh->id}")->assertInertia(fn ($page) => $page->where('can.delete', true));
        $this->actingAs($this->admin)->delete("/bills/{$fresh->id}")->assertRedirect('/bills');
        $this->assertModelMissing($fresh);
        $this->assertDatabaseHas('activity_logs', ['action' => 'bill_deleted']);
    }

    public function test_create_page_previews_the_bill(): void
    {
        $this->addMeals($this->client, 3, '2026-09-01', 2, '120.00');

        $this->actingAs($this->admin)
            ->get("/bills/create?client={$this->client->id}&start=2026-09-01&end=2026-09-30")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('bills/create')
                ->where('preview.total_meals', 6)
                ->where('preview.total_amount', '720.00')
                ->where('preview.overlaps', null));
    }

    public function test_bill_list_totals_and_filters(): void
    {
        $paid = $this->billFor(1000);
        $this->pay($paid, '1000.00');
        $this->billFor(500, Client::factory()->create(['name' => 'Tech Solutions Ltd.']));

        $this->actingAs($this->admin)->get('/bills')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('bills/index')
                ->has('bills.data', 2)
                ->where('totals.billed', '1500.00')
                ->where('totals.paid', '1000.00')
                ->where('totals.due', '500.00'));

        $this->actingAs($this->admin)->get('/bills?search=tech')->assertInertia(fn ($page) => $page->has('bills.data', 1));
        $this->actingAs($this->admin)->get('/bills?status=paid')->assertInertia(fn ($page) => $page->has('bills.data', 1)->where('bills.data.0.id', $paid->id));
    }

    public function test_client_accounts_are_emailed_when_enabled(): void
    {
        Notification::fake();
        $clientUser = User::factory()->clientAdmin($this->client)->create();
        $this->addMeals($this->client, 2, '2026-09-01', 1, '120.00');

        $this->generate();
        $this->pay(Bill::sole(), '100.00');

        Notification::assertSentTo($clientUser, BillIssued::class);
        Notification::assertSentTo($clientUser, PaymentReceived::class);

        app(Settings::class)->set(['notifications.payment_received' => false], $this->admin);
        $this->pay(Bill::sole(), '50.00');
        Notification::assertSentToTimes($clientUser, PaymentReceived::class, 1);
    }

    public function test_billing_access_follows_permissions(): void
    {
        $bill = $this->billFor(1200);
        $staff = User::factory()->vendorStaff()->create();
        $viewer = User::factory()->vendorStaff()->withPermissions(Permission::ViewFinancials)->create();

        // Staff have no financial access by default.
        $this->actingAs($staff)->get('/bills')->assertForbidden();
        $this->actingAs($staff)->get("/bills/{$bill->id}")->assertForbidden();
        $this->actingAs($staff)->get('/payments')->assertForbidden();

        // Financial access can view but not change.
        $this->actingAs($viewer)->get('/bills')->assertOk();
        $this->actingAs($viewer)->get("/bills/{$bill->id}")->assertOk()->assertInertia(fn ($page) => $page->where('can.pay', false));
        $this->actingAs($viewer)->get('/bills/create')->assertForbidden();
        $this->pay($bill, '100.00', user: $viewer)->assertForbidden();
        $this->actingAs($viewer)->post("/bills/{$bill->id}/recalculate")->assertForbidden();

        foreach ([User::factory()->clientAdmin($this->client)->create(), User::factory()->rider()->create()] as $user) {
            $this->actingAs($user)->get('/bills')->assertForbidden();
            $this->actingAs($user)->get('/payments')->assertForbidden();
            $this->pay($bill, '100.00', user: $user)->assertForbidden();
        }

        $this->assertSame(0, Payment::count());
    }

    private function generate(array $overrides = []): TestResponse
    {
        return $this->actingAs($this->admin)->post('/bills', [
            'client_id' => $this->client->id,
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            ...$overrides,
        ]);
    }

    private function pay(Bill $bill, string $amount, string $method = 'cash', ?string $reference = null, ?User $user = null): TestResponse
    {
        return $this->actingAs($user ?? $this->admin)->post("/bills/{$bill->id}/payments", [
            'payment_date' => '2026-10-07',
            'amount' => $amount,
            'method' => $method,
            'reference' => $reference,
        ]);
    }

    /**
     * A September bill for the client with the given total (one meal at that price).
     */
    private function billFor(int $amount, ?Client $client = null): Bill
    {
        $client ??= $this->client;
        $this->addMeals($client, 1, '2026-09-01', 1, number_format($amount, 2, '.', ''));
        $this->actingAs($this->admin)->post('/bills', ['client_id' => $client->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30']);

        return Bill::where('client_id', $client->id)->sole();
    }

    /**
     * Confirmed (or other status) meals for $employees new employees on
     * each of $days consecutive days from $from.
     */
    private function addMeals(Client $client, int $employees, string $from, int $days, string $price, string $type = 'lunch', MealEntryStatus $status = MealEntryStatus::Confirmed): void
    {
        $ids = Employee::factory()->forClient($client)->count($employees)->create()->modelKeys();
        $rows = [];

        foreach (range(0, $days - 1) as $offset) {
            $date = CarbonImmutable::parse($from)->addDays($offset)->toDateString();

            foreach ($ids as $id) {
                $rows[] = [
                    'client_id' => $client->id,
                    'employee_id' => $id,
                    'meal_date' => $date,
                    'meal_type' => $type,
                    'status' => $status->value,
                    'unit_price' => $price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            MealEntry::insert($chunk);
        }
    }
}
