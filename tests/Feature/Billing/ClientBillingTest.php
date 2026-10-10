<?php

namespace Tests\Feature\Billing;

use App\Models\Bill;
use App\Models\Client;
use App\Models\Employee;
use App\Models\MealEntry;
use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * What a client account sees about its own billing, and that it never sees
 * another client's.
 */
class ClientBillingTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private Client $other;

    private User $clientUser;

    private Bill $bill;

    private Bill $otherBill;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-10-07 10:00:00'));

        $this->client = Client::factory()->create();
        $this->other = Client::factory()->create();
        $this->clientUser = User::factory()->clientAdmin($this->client)->create();

        $this->bill = Bill::factory()->for($this->client)->create([
            'total_amount' => '3000.00', 'paid_amount' => '1000.00', 'due_amount' => '2000.00', 'status' => 'partially_paid',
        ]);
        Payment::create(['bill_id' => $this->bill->id, 'client_id' => $this->client->id, 'payment_date' => '2026-10-05', 'amount' => '1000.00', 'method' => 'bkash', 'reference' => 'TX9']);

        $this->otherBill = Bill::factory()->for($this->other)->create(['total_amount' => '9999.00', 'due_amount' => '9999.00']);
        Payment::create(['bill_id' => $this->otherBill->id, 'client_id' => $this->other->id, 'payment_date' => '2026-10-05', 'amount' => '500.00', 'method' => 'cash']);

        // Meals after the billed period (September) are not billed yet.
        $employee = Employee::factory()->forClient($this->client)->create();
        foreach (['2026-10-01', '2026-10-02', '2026-10-06'] as $date) {
            MealEntry::factory()->create(['employee_id' => $employee->id, 'meal_date' => $date, 'unit_price' => '130.00']);
        }
    }

    public function test_billing_page_shows_balance_bills_payments_and_unbilled_meals(): void
    {
        $this->actingAs($this->clientUser)->get('/billing')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('billing/index')
                ->where('balance.billed', '3000.00')
                ->where('balance.paid', '1000.00')
                ->where('balance.due', '2000.00')
                ->where('balance.overdue', '0.00')
                ->where('unbilled.meals', 3)
                ->where('unbilled.amount', '390.00')
                ->where('unbilled.from', '2026-10-01')
                ->has('bills.data', 1)
                ->where('bills.data.0.id', $this->bill->id)
                ->has('payments', 1)
                ->where('payments.0.reference', 'TX9')
                ->missing('payments.0.received_by'));
    }

    public function test_client_can_open_their_own_bill_but_not_another_clients(): void
    {
        $this->actingAs($this->clientUser)->get("/bills/{$this->bill->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('can.pay', false)
                ->where('can.update', false)
                ->where('outOfDate', false)
                ->where('bill.created_by', null));

        $this->actingAs($this->clientUser)->get("/bills/{$this->otherBill->id}")->assertForbidden();
        $this->actingAs($this->clientUser)->post("/bills/{$this->bill->id}/payments", ['payment_date' => '2026-10-07', 'amount' => '10', 'method' => 'cash'])->assertForbidden();
        $this->actingAs($this->clientUser)->delete('/payments/'.Payment::first()->id)->assertForbidden();
    }

    public function test_statement_is_always_the_clients_own(): void
    {
        $response = $this->actingAs($this->clientUser)->get("/billing/statement?month=2026-10&client={$this->other->id}")->assertOk();

        $response->assertInertia(fn ($page) => $page
            ->component('billing/statement')
            ->where('client.id', $this->client->id)
            ->where('statement.totals.meals', 3)
            ->where('statement.totals.amount', '390.00')
            ->has('statement.days', 3)
            ->where('statement.days.0.date', '2026-10-01')
            ->where('clients', null)
            ->has('statement.payments', 1));

        $this->assertStringNotContainsString('9999', $response->getContent());
    }

    public function test_vendor_statement_for_any_client_needs_financial_access(): void
    {
        $admin = User::factory()->vendorAdmin()->create();

        $this->actingAs($admin)->get("/billing/statement?client={$this->other->id}&month=2026-10")
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('client.id', $this->other->id)->where('balance.due', '9999.00'));

        $this->actingAs(User::factory()->vendorStaff()->create())->get("/billing/statement?client={$this->other->id}")->assertForbidden();
        $this->actingAs(User::factory()->rider()->create())->get('/billing/statement')->assertForbidden();
    }

    public function test_dashboard_and_client_page_show_the_balance(): void
    {
        $stats = collect($this->actingAs($this->clientUser)->get('/dashboard')->inertiaProps('stats'))->keyBy('key');
        $this->assertSame('2000.00', $stats['balance_due']['value']);

        $this->actingAs(User::factory()->vendorAdmin()->create())->get("/clients/{$this->client->id}")
            ->assertInertia(fn ($page) => $page->where('overview.stats.outstanding_balance', '2000.00'));
    }

    public function test_vendor_and_rider_accounts_cannot_open_the_client_billing_page(): void
    {
        $this->actingAs(User::factory()->vendorAdmin()->create())->get('/billing')->assertForbidden();
        $this->actingAs(User::factory()->rider()->create())->get('/billing')->assertForbidden();
    }
}
