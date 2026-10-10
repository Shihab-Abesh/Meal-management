<?php

namespace Tests\Feature\Meals;

use App\Enums\MealEntryStatus;
use App\Enums\MealType;
use App\Enums\Permission;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ClientMenuPrice;
use App\Models\Employee;
use App\Models\MealEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MealSheetTest extends TestCase
{
    use RefreshDatabase;

    private const DATE = '2026-10-06';

    private User $admin;

    private Client $client;

    /** @var Collection<int, Employee> */
    private Collection $employees;

    protected function setUp(): void
    {
        parent::setUp();

        // 08:00 on the meal date: lunch (cutoff 10:00) and dinner (16:00) are open.
        $this->travelTo(now()->setDateTimeFrom(self::DATE.' 08:00:00'));

        $this->admin = User::factory()->vendorAdmin()->create();
        $this->client = Client::factory()->withDinner()->create([
            'lunch_cutoff_time' => '10:00',
            'dinner_cutoff_time' => '16:00',
        ]);
        $this->employees = Employee::factory()->forClient($this->client)->count(4)->create(['dinner_enabled' => true]);
        $this->setPrice($this->client, 'lunch', '120', '2026-01-01');
        $this->setPrice($this->client, 'dinner', '140', '2026-01-01');
    }

    public function test_vendor_sees_the_day_overview_with_counts(): void
    {
        $this->save($this->admin, ['lunch' => $this->ids(0, 1, 2)]);

        $this->actingAs($this->admin)
            ->get('/meals?date='.self::DATE)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('meals/overview')
                ->where('clients.0.name', $this->client->name)
                ->where('clients.0.meals.0.meal_type', 'lunch')
                ->where('clients.0.meals.0.count', 3)
                ->where('clients.0.meals.1.count', 0));
    }

    public function test_sheet_lists_employees_prices_and_cutoffs(): void
    {
        $this->save($this->admin, ['lunch' => $this->ids(0)]);

        $this->actingAs($this->admin)
            ->get("/meals?client={$this->client->id}&date=".self::DATE)
            ->assertInertia(fn (Assert $page) => $page
                ->component('meals/sheet')
                ->has('employees', 4)
                ->where('meals.0.meal_type', 'lunch')
                ->where('meals.0.price', '120.00')
                ->where('meals.0.cutoff', '10:00')
                ->where('meals.0.closed', false)
                ->where('meals.0.selected', [(string) $this->employees[0]->id => '120.00'])
                ->where('meals.1.price', '140.00')
                ->where('showAmounts', true));
    }

    public function test_confirming_creates_entries_with_the_client_price(): void
    {
        $this->save($this->admin, ['lunch' => $this->ids(0, 1, 2), 'dinner' => $this->ids(1)])
            ->assertSessionHasNoErrors()
            ->assertRedirect("/meals?client={$this->client->id}&date=".self::DATE);

        $this->assertSame(3, $this->confirmed('lunch')->count());
        $this->assertSame(1, $this->confirmed('dinner')->count());
        $this->assertSame('360.00', number_format((float) $this->confirmed('lunch')->sum('unit_price'), 2, '.', ''));
        $this->assertSame('140.00', $this->confirmed('dinner')->first()->unit_price);
        $this->assertSame($this->admin->id, $this->confirmed('lunch')->first()->confirmed_by);

        $this->assertSame(2, ActivityLog::where('module', 'meals')->where('action', 'confirmed')->count());
    }

    public function test_totals_match_quantity_times_price(): void
    {
        $employees = Employee::factory()->forClient($this->client)->count(18)->create();
        $ids = [...$this->ids(0, 1, 2, 3), ...$employees->modelKeys()];

        $this->save($this->admin, ['lunch' => $ids])->assertSessionHasNoErrors();

        // 22 lunches × ৳120 = ৳2,640
        $this->assertSame(22, $this->confirmed('lunch')->count());
        $this->assertEquals(2640, $this->confirmed('lunch')->sum('unit_price'));

        $stats = collect($this->actingAs($this->admin)->get('/dashboard')->inertiaProps('stats'))->keyBy('key');
        $this->assertSame(22, $stats['todays_meals']['value']);
        $this->assertSame('2640.00', $stats['todays_revenue']['value']);
    }

    public function test_editing_cancels_unmarked_meals_and_restores_them_without_duplicates(): void
    {
        $this->save($this->admin, ['lunch' => $this->ids(0, 1, 2)]);
        $this->save($this->admin, ['lunch' => $this->ids(0, 3)])->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing($this->ids(0, 3), $this->confirmed('lunch')->pluck('employee_id')->all());
        $this->assertSame(2, MealEntry::where('status', MealEntryStatus::Cancelled)->count());
        $this->assertDatabaseHas('activity_logs', ['module' => 'meals', 'action' => 'edited']);

        // Marking someone again reuses their row.
        $this->save($this->admin, ['lunch' => $this->ids(0, 1, 3)]);
        $this->assertSame(3, $this->confirmed('lunch')->count());
        $this->assertSame(4, MealEntry::count());
    }

    public function test_saving_the_same_sheet_twice_creates_no_duplicates(): void
    {
        $this->save($this->admin, ['lunch' => $this->ids(0, 1)]);
        $this->save($this->admin, ['lunch' => $this->ids(0, 1)])->assertSessionHasNoErrors();

        $this->assertSame(2, MealEntry::count());
        $this->assertSame(1, ActivityLog::where('module', 'meals')->count());
    }

    public function test_the_database_refuses_a_duplicate_meal(): void
    {
        $this->expectException(UniqueConstraintViolationException::class);

        MealEntry::factory()->create(['employee_id' => $this->employees[0]->id, 'meal_date' => self::DATE]);
        MealEntry::factory()->create(['employee_id' => $this->employees[0]->id, 'meal_date' => self::DATE]);
    }

    public function test_recorded_meals_keep_their_price_when_the_price_changes(): void
    {
        $this->save($this->admin, ['lunch' => $this->ids(0)]);

        // New price from the same day; only meals confirmed afterwards use it.
        $this->setPrice($this->client, 'lunch', '150', self::DATE);
        $this->save($this->admin, ['lunch' => $this->ids(0, 1)]);

        $prices = $this->confirmed('lunch')->pluck('unit_price', 'employee_id');
        $this->assertSame('120.00', $prices[$this->employees[0]->id]);
        $this->assertSame('150.00', $prices[$this->employees[1]->id]);
    }

    public function test_client_admin_cannot_change_meals_after_the_cutoff(): void
    {
        $clientAdmin = User::factory()->clientAdmin($this->client)->create();
        $this->travelTo(now()->setDateTimeFrom(self::DATE.' 10:00:00'));

        $this->save($clientAdmin, ['lunch' => $this->ids(0)])->assertSessionHasErrors('meals.lunch');
        $this->assertSame(0, MealEntry::count());

        // Dinner is still open until 16:00.
        $this->save($clientAdmin, ['dinner' => $this->ids(0)])->assertSessionHasNoErrors();
        $this->assertSame(1, $this->confirmed('dinner')->count());
    }

    public function test_staff_cannot_override_but_vendor_admin_can_with_confirmation(): void
    {
        $this->travelTo(now()->setDateTimeFrom(self::DATE.' 11:30:00'));

        $this->save(User::factory()->vendorStaff()->create(), ['lunch' => $this->ids(0)])->assertSessionHasErrors('meals.lunch');

        // The admin must confirm the override explicitly.
        $this->save($this->admin, ['lunch' => $this->ids(0)])->assertSessionHasErrors('override');
        $this->assertSame(0, MealEntry::count());

        $this->save($this->admin, ['lunch' => $this->ids(0)], override: true)->assertSessionHasNoErrors();
        $this->assertSame(1, $this->confirmed('lunch')->count());

        $log = ActivityLog::where('module', 'meals')->firstOrFail();
        $this->assertTrue($log->properties['override']);
    }

    public function test_staff_granted_the_override_permission_can_override(): void
    {
        $this->travelTo(now()->setDateTimeFrom(self::DATE.' 11:30:00'));
        $staff = User::factory()->vendorStaff()->withPermissions(Permission::ManageMeals, Permission::OverrideMealCutoff)->create();

        $this->save($staff, ['lunch' => $this->ids(0)], override: true)->assertSessionHasNoErrors();
    }

    public function test_past_dates_are_closed_and_future_dates_open(): void
    {
        $clientAdmin = User::factory()->clientAdmin($this->client)->create();

        $this->save($clientAdmin, ['dinner' => $this->ids(0)], '2026-10-05')->assertSessionHasErrors('meals.dinner');
        $this->save($clientAdmin, ['lunch' => $this->ids(0)], '2026-10-20')->assertSessionHasNoErrors();
        $this->save($clientAdmin, ['lunch' => $this->ids(0)], '2027-03-01')->assertSessionHasErrors('date');
    }

    public function test_default_cutoff_applies_when_the_client_has_none(): void
    {
        $client = Client::factory()->create(['lunch_cutoff_time' => null]);
        $employee = Employee::factory()->forClient($client)->create();
        $this->travelTo(now()->setDateTimeFrom(self::DATE.' 09:59:00'));

        $this->actingAs(User::factory()->clientAdmin($client)->create())
            ->post('/meals', ['date' => self::DATE, 'meals' => ['lunch' => [$employee->id]]])
            ->assertSessionHasNoErrors();

        $this->travelTo(now()->setDateTimeFrom(self::DATE.' 10:00:00'));

        $this->post('/meals', ['date' => self::DATE, 'meals' => ['lunch' => []]])->assertSessionHasErrors('meals.lunch');
    }

    public function test_employees_of_another_client_are_rejected(): void
    {
        $stranger = Employee::factory()->forClient(Client::factory()->create())->create();

        $this->save($this->admin, ['lunch' => [...$this->ids(0), $stranger->id]])->assertSessionHasErrors('meals');
        $this->assertSame(0, MealEntry::count());
    }

    public function test_inactive_employees_cannot_be_added_but_can_be_removed(): void
    {
        $this->save($this->admin, ['lunch' => $this->ids(0)]);
        $this->employees[0]->update(['status' => 'inactive']);
        $this->employees[1]->update(['status' => 'inactive']);

        $this->save($this->admin, ['lunch' => $this->ids(0, 1)])->assertSessionHasErrors('meals');

        $this->save($this->admin, ['lunch' => []])->assertSessionHasNoErrors();
        $this->assertSame(0, $this->confirmed('lunch')->count());
    }

    public function test_meal_types_the_client_does_not_receive_are_rejected(): void
    {
        $lunchOnly = Client::factory()->create();
        $employee = Employee::factory()->forClient($lunchOnly)->create();

        $this->actingAs($this->admin)
            ->post('/meals', ['client_id' => $lunchOnly->id, 'date' => self::DATE, 'meals' => ['dinner' => [$employee->id]]])
            ->assertSessionHasErrors('meals');

        $this->post('/meals', ['client_id' => $lunchOnly->id, 'date' => self::DATE, 'meals' => ['breakfast' => [$employee->id]]])
            ->assertSessionHasErrors('meals');
    }

    public function test_client_admin_always_saves_for_their_own_client(): void
    {
        $other = Client::factory()->create();
        $otherEmployee = Employee::factory()->forClient($other)->create();
        $clientAdmin = User::factory()->clientAdmin($this->client)->create();

        // Pointing at another client is ignored; its employee is then rejected.
        $this->actingAs($clientAdmin)
            ->post('/meals', ['client_id' => $other->id, 'date' => self::DATE, 'meals' => ['lunch' => [$otherEmployee->id]]])
            ->assertSessionHasErrors('meals');

        $this->post('/meals', ['client_id' => $other->id, 'date' => self::DATE, 'meals' => ['lunch' => $this->ids(0)]])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/meals?date='.self::DATE);

        $this->assertSame($this->client->id, MealEntry::firstOrFail()->client_id);
        $this->assertSame(0, MealEntry::where('client_id', $other->id)->count());
    }

    public function test_client_admin_only_sees_their_own_sheet(): void
    {
        $other = Client::factory()->create(['name' => 'Other Client']);
        $clientAdmin = User::factory()->clientAdmin($this->client)->create();

        $this->actingAs($clientAdmin)
            ->get("/meals?client={$other->id}&date=".self::DATE)
            ->assertInertia(fn (Assert $page) => $page
                ->component('meals/sheet')
                ->where('client.id', $this->client->id)
                ->where('clients', null));
    }

    public function test_access_is_refused_without_the_meals_permission(): void
    {
        $staff = User::factory()->vendorStaff()->withPermissions(Permission::ManageClients)->create();

        foreach ([$staff, User::factory()->rider()->create()] as $user) {
            $this->actingAs($user);
            $this->get('/meals')->assertForbidden();
            $this->get('/meals/calendar')->assertForbidden();
            $this->get('/meals/history')->assertForbidden();
            $this->save($user, ['lunch' => $this->ids(0)])->assertForbidden();
        }

        $this->assertSame(0, MealEntry::count());
    }

    public function test_inactive_clients_cannot_receive_meals(): void
    {
        $this->client->update(['status' => 'inactive']);

        $this->save($this->admin, ['lunch' => $this->ids(0)])->assertForbidden();
    }

    public function test_history_is_filtered_and_isolated_per_client(): void
    {
        $this->save($this->admin, ['lunch' => $this->ids(0, 1), 'dinner' => $this->ids(0)]);
        $this->save($this->admin, ['lunch' => $this->ids(0)]);

        $other = Client::factory()->create();
        MealEntry::factory()->create(['employee_id' => Employee::factory()->forClient($other)->create()->id, 'meal_date' => self::DATE]);

        $this->actingAs($this->admin);
        $this->get('/meals/history?from='.self::DATE.'&to='.self::DATE)->assertInertia(fn (Assert $page) => $page
            ->has('entries.data', 4)
            ->where('summary.meals', 3)
            ->where('summary.amount', '380.00'));
        $this->get('/meals/history?from='.self::DATE.'&to='.self::DATE.'&status=cancelled')
            ->assertInertia(fn (Assert $page) => $page->has('entries.data', 1));
        $this->get('/meals/history?from='.self::DATE.'&to='.self::DATE.'&meal_type=dinner')
            ->assertInertia(fn (Assert $page) => $page->has('entries.data', 1));
        $this->get('/meals/history?from='.self::DATE.'&to='.self::DATE."&employee={$this->employees[0]->id}")
            ->assertInertia(fn (Assert $page) => $page->has('entries.data', 2));

        // A client account never sees another client's meals, whatever it asks for.
        $this->actingAs(User::factory()->clientAdmin($other)->create())
            ->get('/meals/history?from='.self::DATE.'&to='.self::DATE."&client={$this->client->id}")
            ->assertInertia(fn (Assert $page) => $page->has('entries.data', 1)->missing('entries.data.0.client'));
    }

    public function test_calendar_counts_confirmed_meals_per_day(): void
    {
        $this->save($this->admin, ['lunch' => $this->ids(0, 1, 2), 'dinner' => $this->ids(0)]);
        $this->save($this->admin, ['lunch' => $this->ids(0, 1)], '2026-10-07');

        $this->actingAs($this->admin)
            ->get('/meals/calendar?month=2026-10')
            ->assertInertia(fn (Assert $page) => $page
                ->component('meals/calendar')
                ->where('days.2026-10-06', ['lunch' => 3, 'dinner' => 1])
                ->where('days.2026-10-07', ['lunch' => 2, 'dinner' => 0])
                ->where('totals.lunch', 5)
                ->where('totals.dinner', 1));
    }

    public function test_staff_without_financial_access_do_not_see_amounts(): void
    {
        $staff = User::factory()->vendorStaff()->create();
        $this->save($staff, ['lunch' => $this->ids(0)]);

        $this->actingAs($staff)
            ->get("/meals?client={$this->client->id}&date=".self::DATE)
            ->assertInertia(fn (Assert $page) => $page
                ->where('showAmounts', false)
                ->where('meals.0.price', null)
                ->where('meals.0.selected', [(string) $this->employees[0]->id => null]));

        $this->get('/meals/history?from='.self::DATE.'&to='.self::DATE)
            ->assertInertia(fn (Assert $page) => $page->where('summary.amount', null)->where('entries.data.0.unit_price', null));
    }

    /**
     * @return list<int>
     */
    private function ids(int ...$indexes): array
    {
        return array_map(fn (int $index) => $this->employees[$index]->id, $indexes);
    }

    /**
     * @param  array<string, list<int>>  $meals
     */
    private function save(User $user, array $meals, string $date = self::DATE, bool $override = false): TestResponse
    {
        return $this->actingAs($user)->post('/meals', [
            'client_id' => $this->client->id,
            'date' => $date,
            'meals' => $meals,
            'override' => $override,
        ]);
    }

    /**
     * @return Builder<MealEntry>
     */
    private function confirmed(string $type)
    {
        return MealEntry::query()->confirmed()->where('meal_type', MealType::from($type))->where('meal_date', self::DATE);
    }

    private function setPrice(Client $client, string $type, string $price, string $from): void
    {
        $record = new ClientMenuPrice(['meal_type' => $type, 'price' => $price, 'effective_from' => $from]);
        $record->client_id = $client->id;
        $record->save();
    }
}
