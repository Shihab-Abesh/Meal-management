<?php

namespace Tests\Feature\Reports;

use App\Enums\MealEntryStatus;
use App\Enums\MenuCategory;
use App\Enums\OrderStatus;
use App\Enums\Permission;
use App\Models\Bill;
use App\Models\Client;
use App\Models\DailyMenu;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\MealEntry;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Rider;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Report figures checked against the records they are built from.
 */
class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Client $xyz;

    private Client $bank;

    /** @var Collection<int, Employee> */
    private Collection $xyzStaff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-10-07 10:00:00'));
        $this->admin = User::factory()->vendorAdmin()->create();
        $this->xyz = Client::factory()->withDinner()->create(['name' => 'XYZ Software Ltd.']);
        $this->bank = Client::factory()->create(['name' => 'ABC Bank Ltd.']);
        $this->xyzStaff = Employee::factory()->forClient($this->xyz)->count(4)->create(['dinner_enabled' => true]);
        $bankStaff = Employee::factory()->forClient($this->bank)->count(3)->create();

        // 06 Oct: XYZ 4 lunch × 120 + 2 dinner × 140; Bank 3 lunch × 130.
        $this->meals($this->xyzStaff, '2026-10-06', 'lunch', '120.00');
        $this->meals($this->xyzStaff->take(2), '2026-10-06', 'dinner', '140.00');
        $this->meals($bankStaff, '2026-10-06', 'lunch', '130.00');
        // A cancelled meal never counts.
        MealEntry::factory()->cancelled()->create(['employee_id' => $bankStaff[0]->id, 'meal_date' => '2026-10-06', 'meal_type' => 'dinner']);
        // 05 Oct: XYZ 3 lunch × 120.
        $this->meals($this->xyzStaff->take(3), '2026-10-05', 'lunch', '120.00');
        // September: XYZ 4 lunch × 120.
        $this->meals($this->xyzStaff, '2026-09-15', 'lunch', '120.00');
    }

    public function test_daily_report_matches_the_days_records(): void
    {
        Order::factory()->for($this->xyz)->status(OrderStatus::Delivered)->create(['order_date' => '2026-10-06']);
        Order::factory()->for($this->xyz)->status(OrderStatus::OnTheWay)->create(['order_date' => '2026-10-06', 'meal_type' => 'dinner']);
        Order::factory()->for($this->bank)->status(OrderStatus::Cancelled)->create(['order_date' => '2026-10-06']);

        $report = $this->report('type=daily&date=2026-10-06');

        $this->assertSame([
            'clients' => 2, 'employees' => 7, 'lunch' => 7, 'dinner' => 2, 'meals' => 9,
            'revenue' => '1150.00', // 480 + 280 + 390
            'pending' => 1, 'delivered' => 1,
        ], $this->figures($report));
        $this->assertSame([
            ['client' => 'ABC Bank Ltd.', 'lunch' => 3, 'dinner' => 0, 'meals' => 3, 'amount' => '390.00'],
            ['client' => 'XYZ Software Ltd.', 'lunch' => 4, 'dinner' => 2, 'meals' => 6, 'amount' => '760.00'],
        ], $report['rows']);
        $this->assertSame(['client' => 'Total', 'lunch' => 7, 'dinner' => 2, 'meals' => 9, 'amount' => '1150.00'], $report['totals']);
    }

    public function test_monthly_report_with_paid_and_due(): void
    {
        $bill = Bill::factory()->for($this->xyz)->create(['period_start' => '2026-10-01', 'period_end' => '2026-10-31', 'total_amount' => '1120.00', 'paid_amount' => '500.00', 'due_amount' => '620.00']);
        Payment::create(['bill_id' => $bill->id, 'client_id' => $this->xyz->id, 'payment_date' => '2026-10-07', 'amount' => '500.00', 'method' => 'cash']);

        $report = $this->report('type=monthly&month=2026-10');

        // XYZ: 7 lunch × 120 + 2 dinner × 140 = 1,120; Bank: 390.
        $this->assertSame(['meals' => 12, 'lunch' => 10, 'dinner' => 2, 'revenue' => '1510.00', 'paid' => '500.00', 'due' => '1010.00'], $this->figures($report));
        $this->assertSame(['client' => 'XYZ Software Ltd.', 'lunch' => 7, 'dinner' => 2, 'meals' => 9, 'amount' => '1120.00', 'paid' => '500.00', 'due' => '620.00'], $report['rows'][1]);
        $this->assertCount(31, $report['charts']['daily']);
        $this->assertSame(['date' => '2026-10-06', 'label' => '6', 'lunch' => 7, 'dinner' => 2], $report['charts']['daily'][5]);
        $this->assertCount(12, $report['charts']['months']);
        $this->assertSame(['month' => '2026-09', 'label' => 'Sep', 'meals' => 4], $report['charts']['months'][10]);
        $this->assertSame([['label' => 'XYZ Software Ltd.', 'value' => '1120.00'], ['label' => 'ABC Bank Ltd.', 'value' => '390.00']], $report['charts']['clients']);
    }

    public function test_client_report_lists_each_day(): void
    {
        Payment::create(['bill_id' => Bill::factory()->for($this->xyz)->create(['due_amount' => '700.00'])->id, 'client_id' => $this->xyz->id, 'payment_date' => '2026-10-02', 'amount' => '200.00', 'method' => 'bkash']);

        $report = $this->report("type=client&client={$this->xyz->id}&from=2026-10-01&to=2026-10-07");

        $this->assertSame(['meals' => 9, 'revenue' => '1120.00', 'paid' => '200.00', 'due' => '700.00'], $this->figures($report));
        $this->assertSame([
            ['date' => '2026-10-05', 'lunch' => 3, 'dinner' => 0, 'meals' => 3, 'amount' => '360.00'],
            ['date' => '2026-10-06', 'lunch' => 4, 'dinner' => 2, 'meals' => 6, 'amount' => '760.00'],
        ], $report['rows']);
    }

    public function test_employee_report_counts_each_employees_meals(): void
    {
        $report = $this->report("type=employees&client={$this->xyz->id}&from=2026-10-01&to=2026-10-31");

        $first = $this->xyzStaff->sortBy('name')->first();
        $row = collect($report['rows'])->firstWhere('employee_code', $first->employee_code);
        $tookDinner = $this->xyzStaff->take(2)->contains('id', $first->id);
        $tookOct5 = $this->xyzStaff->take(3)->contains('id', $first->id);

        $this->assertCount(4, $report['rows']);
        $this->assertSame(1 + (int) $tookOct5, $row['lunch']);
        $this->assertSame((int) $tookDinner, $row['dinner']);
        $this->assertSame(['employees' => 4, 'lunch' => 7, 'dinner' => 2, 'amount' => '1120.00'], $this->figures($report));
        $this->assertNotContains('client', array_column($report['columns'], 'key'));

        // Without a client every client's employees are listed, with a client column.
        $all = $this->report('type=employees&from=2026-10-01&to=2026-10-31');
        $this->assertCount(7, $all['rows']);
        $this->assertContains('client', array_column($all['columns'], 'key'));
    }

    public function test_menu_report_counts_usage_and_meals_served(): void
    {
        $rice = MenuItem::factory()->create(['name' => 'Plain Rice', 'category' => MenuCategory::MainCourse]);
        $curry = MenuItem::factory()->create(['name' => 'Chicken Curry', 'category' => MenuCategory::Protein]);
        foreach ([['2026-10-05', 'lunch', [$rice, $curry]], ['2026-10-06', 'lunch', [$rice]], ['2026-10-06', 'dinner', [$rice, $curry]]] as [$date, $type, $items]) {
            DailyMenu::factory()->create(['menu_date' => $date, 'meal_type' => $type])->items()->attach(collect($items)->pluck('id'));
        }

        $report = $this->report('type=menu&from=2026-10-01&to=2026-10-31');

        $this->assertSame(['name' => 'Plain Rice', 'category' => 'Main Course', 'lunch' => 2, 'dinner' => 1, 'usage' => 3, 'meals' => 3 + 7 + 2, 'first' => '2026-10-05', 'last' => '2026-10-06'], $report['rows'][0]);
        $this->assertSame(['name' => 'Chicken Curry', 'category' => 'Protein', 'lunch' => 1, 'dinner' => 1, 'usage' => 2, 'meals' => 3 + 2, 'first' => '2026-10-05', 'last' => '2026-10-06'], $report['rows'][1]);
        $this->assertSame(['menus' => 3, 'items' => 2, 'top' => 'Plain Rice'], $this->figures($report));
    }

    public function test_rider_report_counts_deliveries_by_outcome(): void
    {
        $rahim = Rider::factory()->create(['name' => 'Rahim']);
        Rider::factory()->create(['name' => 'Karim']);
        foreach ([[OrderStatus::Delivered, 22], [OrderStatus::Delivered, 13], [OrderStatus::OnTheWay, 5], [OrderStatus::Cancelled, 4]] as [$status, $quantity]) {
            $order = Order::factory()->status($status)->create(['order_date' => '2026-10-06', 'quantity' => $quantity]);
            Delivery::create(['order_id' => $order->id, 'rider_id' => $rahim->id, 'assigned_at' => now()]);
        }
        // Outside the range.
        Delivery::create(['order_id' => Order::factory()->status(OrderStatus::Delivered)->create(['order_date' => '2026-09-01'])->id, 'rider_id' => $rahim->id, 'assigned_at' => now()]);

        $report = $this->report('type=riders&from=2026-10-01&to=2026-10-31');

        $row = collect($report['rows'])->firstWhere('name', 'Rahim');
        $this->assertSame(['assigned' => 4, 'completed' => 2, 'cancelled' => 1, 'pending' => 1, 'meals' => 35], array_intersect_key($row, array_flip(['assigned', 'completed', 'cancelled', 'pending', 'meals'])));
        $this->assertSame(0, collect($report['rows'])->firstWhere('name', 'Karim')['assigned']);
    }

    public function test_amounts_are_removed_for_staff_without_financial_access(): void
    {
        $staff = User::factory()->vendorStaff()->create();

        $report = $this->report('type=monthly&month=2026-10', $staff);

        $this->assertFalse($report['showMoney']);
        $this->assertSame(['meals', 'lunch', 'dinner'], array_column($report['summary'], 'key'));
        $this->assertNotContains('currency', array_column($report['columns'], 'format'));
        $this->assertArrayNotHasKey('amount', $report['rows'][0]);
        $this->assertSame('number', $report['charts']['clientsFormat']);

        $csv = $this->actingAs($staff)->get('/reports/export?type=monthly&month=2026-10&format=csv')->streamedContent();
        $this->assertStringNotContainsString('1120', $csv);
    }

    public function test_client_accounts_only_see_their_own_organization(): void
    {
        $clientUser = User::factory()->clientAdmin($this->bank)->create();

        $this->actingAs($clientUser)->get('/reports')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('report.type', 'client')
                ->where('report.subtitle', 'ABC Bank Ltd. · 01 Oct 2026 – 07 Oct 2026')
                ->where('types', [['value' => 'client', 'label' => 'Meal summary'], ['value' => 'employees', 'label' => 'Employees']])
                ->where('clients', null));

        // Asking for another client still returns their own.
        $report = $this->report("type=employees&client={$this->xyz->id}&from=2026-10-01&to=2026-10-31", $clientUser);
        $this->assertCount(3, $report['rows']);
        $this->assertSame(['employees' => 3, 'lunch' => 3, 'dinner' => 0, 'amount' => '390.00'], $this->figures($report));

        foreach (['daily', 'monthly', 'menu', 'riders'] as $type) {
            $this->actingAs($clientUser)->get("/reports?type={$type}")->assertSessionHasErrors('type');
        }
    }

    public function test_csv_export_opens_in_excel(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports/export?type=daily&date=2026-10-06&format=csv');

        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $this->assertStringStartsWith("\u{FEFF}\"Daily report\"", $csv);
        $this->assertStringContainsString("Client,Lunch,Dinner,Total,Amount\n", $csv);
        $this->assertStringContainsString('"XYZ Software Ltd.",4,2,6,760.00', $csv);
        $this->assertStringContainsString('Total,7,2,9,1150.00', $csv);
        $this->assertDatabaseHas('activity_logs', ['module' => 'reports', 'action' => 'exported']);
    }

    public function test_csv_export_neutralizes_formulas(): void
    {
        $this->xyz->update(['name' => '=HYPERLINK("http://evil")']);

        $csv = $this->actingAs($this->admin)->get('/reports/export?type=daily&date=2026-10-06&format=csv')->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }

    public function test_pdf_export(): void
    {
        $this->actingAs($this->admin)
            ->get('/reports/export?type=monthly&month=2026-10&format=pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_access(): void
    {
        $this->actingAs(User::factory()->rider()->create())->get('/reports')->assertForbidden();
        $this->actingAs(User::factory()->vendorStaff()->withPermissions(Permission::ManageMeals)->create())->get('/reports')->assertForbidden();
        $this->actingAs(User::factory()->vendorStaff()->create())->get('/reports')->assertOk();
        $this->actingAs($this->admin)->get('/reports?type=client')->assertOk()->assertInertia(fn ($page) => $page->where('report', null));
        $this->actingAs($this->admin)->get('/reports?type=riders&from=2025-01-01&to=2026-10-07')->assertSessionHasErrors('to');
    }

    /**
     * @return array<string, mixed>
     */
    private function report(string $query, ?User $user = null): array
    {
        return $this->actingAs($user ?? $this->admin)->get('/reports?'.$query)->assertOk()->inertiaProps('report');
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array<string, mixed>
     */
    private function figures(array $report): array
    {
        return array_column($report['summary'], 'value', 'key');
    }

    /**
     * @param  Collection<int, Employee>  $employees
     */
    private function meals(Collection $employees, string $date, string $type, string $price): void
    {
        $employees->each(fn (Employee $employee) => MealEntry::factory()->create([
            'employee_id' => $employee->id,
            'meal_date' => $date,
            'meal_type' => $type,
            'unit_price' => $price,
            'status' => MealEntryStatus::Confirmed,
        ]));
    }
}
