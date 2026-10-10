<?php

namespace Tests\Feature\Employees;

use App\Enums\MealPreference;
use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Models\Client;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Client $clientA;

    private Client $clientB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->vendorAdmin()->create();
        $this->clientA = Client::factory()->create(['name' => 'Client A']);
        $this->clientB = Client::factory()->create(['name' => 'Client B']);
    }

    public function test_vendor_sees_employees_of_all_clients_with_their_client(): void
    {
        Employee::factory()->forClient($this->clientA)->count(2)->create();
        Employee::factory()->forClient($this->clientB)->create();

        $this->actingAs($this->admin)
            ->get('/employees')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('employees/index')
                ->has('employees.data', 3)
                ->has('employees.data.0.client.name')
                ->has('clients', 2));
    }

    public function test_vendor_can_filter_by_client_search_and_attributes(): void
    {
        Employee::factory()->forClient($this->clientA)->create(['name' => 'Abdullah', 'department' => 'Finance']);
        Employee::factory()->forClient($this->clientA)->create(['name' => 'Rahim', 'department' => 'Sales', 'dinner_enabled' => true]);
        Employee::factory()->forClient($this->clientA)->inactive()->create(['name' => 'Karim', 'department' => 'HR']);
        Employee::factory()->forClient($this->clientB)->create(['name' => 'Hasan', 'department' => 'HR', 'meal_preference' => MealPreference::Vegetarian]);

        $this->actingAs($this->admin);

        $this->assertSame(['Abdullah', 'Karim', 'Rahim'], $this->names("/employees?client={$this->clientA->id}"));
        $this->assertSame(['Rahim'], $this->names('/employees?search=rah'));
        $this->assertSame(['Abdullah'], $this->names('/employees?department=Finance'));
        $this->assertSame(['Karim'], $this->names('/employees?status=inactive'));
        $this->assertSame(['Rahim'], $this->names('/employees?meal=dinner'));
        $this->assertSame(['Hasan'], $this->names('/employees?meal_preference=vegetarian'));
        $this->assertSame('Rahim', $this->names('/employees?sort=name&direction=desc')[0]);
    }

    public function test_list_is_paginated(): void
    {
        Employee::factory()->forClient($this->clientA)->count(25)->create();

        $this->actingAs($this->admin)
            ->get('/employees?page=2')
            ->assertInertia(fn (Assert $page) => $page->has('employees.data', 5)->where('employees.meta.total', 25));
    }

    public function test_vendor_can_add_an_employee_to_a_client(): void
    {
        $this->actingAs($this->admin)
            ->post('/employees', $this->validPayload(['client_id' => $this->clientA->id]))
            ->assertSessionHasNoErrors();

        $employee = Employee::firstOrFail();
        $this->assertSame($this->clientA->id, $employee->client_id);
        $this->assertSame('EMP-001', $employee->employee_code);
        $this->assertTrue($employee->lunch_enabled);
        $this->assertFalse($employee->dinner_enabled);
        $this->assertSame(MealPreference::NoBeef, $employee->meal_preference);
        $this->assertSame('2025-01-15', $employee->joining_date->toDateString());

        $this->assertDatabaseHas('activity_logs', [
            'module' => 'employees', 'action' => 'created', 'subject_id' => $employee->id, 'client_id' => $this->clientA->id,
        ]);
    }

    public function test_save_and_add_another_returns_to_the_form(): void
    {
        $this->actingAs($this->admin)
            ->post('/employees', $this->validPayload(['client_id' => $this->clientA->id, 'add_another' => true]))
            ->assertRedirect("/employees/create?client={$this->clientA->id}");
    }

    public function test_vendor_must_choose_an_active_client(): void
    {
        $inactive = Client::factory()->inactive()->create();
        $archived = Client::factory()->create();
        $archived->delete();

        $this->actingAs($this->admin);

        $this->post('/employees', $this->validPayload())->assertSessionHasErrors('client_id');
        $this->post('/employees', $this->validPayload(['client_id' => $inactive->id]))->assertSessionHasErrors('client_id');
        $this->post('/employees', $this->validPayload(['client_id' => $archived->id]))->assertSessionHasErrors('client_id');
        $this->post('/employees', $this->validPayload(['client_id' => 99999]))->assertSessionHasErrors('client_id');

        $this->assertSame(0, Employee::count());
    }

    public function test_employee_id_is_unique_per_client_only(): void
    {
        Employee::factory()->forClient($this->clientA)->create(['employee_code' => 'EMP-001']);

        $this->actingAs($this->admin);

        $this->post('/employees', $this->validPayload(['client_id' => $this->clientA->id]))
            ->assertSessionHasErrors(['employee_code' => 'This employee ID is already used at this client.']);

        // The same ID at another client is fine.
        $this->post('/employees', $this->validPayload(['client_id' => $this->clientB->id]))->assertSessionHasNoErrors();
    }

    public function test_employee_validation(): void
    {
        $this->actingAs($this->admin)
            ->post('/employees', [
                'client_id' => $this->clientA->id,
                'employee_code' => 'bad id!',
                'name' => '',
                'phone' => 'phone',
                'email' => 'nope',
                'meal_preference' => 'carnivore',
                'status' => 'retired',
                'joining_date' => '15/01/2025',
                'lunch_enabled' => 'maybe',
            ])
            ->assertSessionHasErrors(['employee_code', 'name', 'phone', 'email', 'meal_preference', 'status', 'joining_date', 'lunch_enabled']);
    }

    public function test_employee_details_page(): void
    {
        $employee = Employee::factory()->forClient($this->clientA)->create(['name' => 'Abdullah']);

        $this->actingAs($this->admin)
            ->get("/employees/{$employee->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('employees/show')
                ->where('employee.name', 'Abdullah')
                ->where('employee.client.name', 'Client A')
                ->has('history')
                ->where('can.update', true));
    }

    public function test_employee_can_be_updated_but_not_moved_to_another_client(): void
    {
        $employee = Employee::factory()->forClient($this->clientA)->create(['employee_code' => 'EMP-009']);

        $this->actingAs($this->admin)
            ->put("/employees/{$employee->id}", $this->validPayload([
                'client_id' => $this->clientB->id,
                'employee_code' => 'EMP-009',
                'name' => 'Renamed',
                'dinner_enabled' => true,
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect("/employees/{$employee->id}");

        $employee->refresh();
        $this->assertSame('Renamed', $employee->name);
        $this->assertTrue($employee->dinner_enabled);
        $this->assertSame($this->clientA->id, $employee->client_id);

        $this->assertDatabaseHas('activity_logs', ['module' => 'employees', 'action' => 'updated', 'subject_id' => $employee->id]);
    }

    public function test_employee_can_be_deactivated_and_activated(): void
    {
        $employee = Employee::factory()->forClient($this->clientA)->create();

        $this->actingAs($this->admin)->patch("/employees/{$employee->id}/status", ['status' => 'inactive']);
        $this->assertSame(RecordStatus::Inactive, $employee->fresh()->status);

        $this->patch("/employees/{$employee->id}/status", ['status' => 'active']);
        $this->assertSame(RecordStatus::Active, $employee->fresh()->status);
    }

    public function test_client_admin_only_sees_and_manages_their_own_employees(): void
    {
        $own = Employee::factory()->forClient($this->clientA)->create(['name' => 'Own Employee']);
        $other = Employee::factory()->forClient($this->clientB)->create(['name' => 'Other Employee']);
        $clientAdmin = User::factory()->clientAdmin($this->clientA)->create();

        $this->actingAs($clientAdmin);

        // The client filter cannot widen the scope.
        $this->assertSame(['Own Employee'], $this->names('/employees'));
        $this->assertSame(['Own Employee'], $this->names("/employees?client={$this->clientB->id}"));
        $this->get('/employees')->assertInertia(fn (Assert $page) => $page->where('clients', null));

        $this->get("/employees/{$own->id}")->assertOk();
        $this->get("/employees/{$other->id}")->assertForbidden();
        $this->get("/employees/{$other->id}/edit")->assertForbidden();
        $this->put("/employees/{$other->id}", $this->validPayload(['name' => 'Hacked']))->assertForbidden();
        $this->patch("/employees/{$other->id}/status", ['status' => 'inactive'])->assertForbidden();

        $this->assertSame('Other Employee', $other->fresh()->name);
        $this->assertTrue($other->fresh()->isActive());
    }

    public function test_client_admin_always_creates_employees_in_their_own_client(): void
    {
        $clientAdmin = User::factory()->clientAdmin($this->clientA)->create();

        $this->actingAs($clientAdmin)
            ->post('/employees', $this->validPayload(['client_id' => $this->clientB->id]))
            ->assertSessionHasNoErrors();

        $this->assertSame($this->clientA->id, Employee::firstOrFail()->client_id);
    }

    public function test_staff_without_employee_permission_and_riders_are_refused(): void
    {
        $employee = Employee::factory()->forClient($this->clientA)->create();
        $staff = User::factory()->vendorStaff()->withPermissions(Permission::ManageClients)->create();

        foreach ([$staff, User::factory()->rider()->create()] as $user) {
            $this->actingAs($user);

            $this->get('/employees')->assertForbidden();
            $this->get("/employees/{$employee->id}")->assertForbidden();
            $this->post('/employees', $this->validPayload(['client_id' => $this->clientA->id]))->assertForbidden();
        }
    }

    public function test_employee_counts_reach_the_client_list_and_dashboards(): void
    {
        Employee::factory()->forClient($this->clientA)->count(3)->create();
        Employee::factory()->forClient($this->clientA)->inactive()->create();
        Employee::factory()->forClient($this->clientB)->count(2)->create();

        $this->actingAs($this->admin);

        $counts = collect($this->get('/clients')->inertiaProps('clients.data'))->pluck('employees_count', 'name');
        $this->assertSame(3, $counts['Client A']);
        $this->assertSame(2, $counts['Client B']);

        $stats = collect($this->get('/dashboard')->inertiaProps('stats'))->keyBy('key');
        $this->assertSame(5, $stats['total_employees']['value']);

        $clientStats = collect($this->actingAs(User::factory()->clientAdmin($this->clientA)->create())
            ->get('/dashboard')->inertiaProps('stats'))->keyBy('key');
        $this->assertSame(3, $clientStats['total_employees']['value']);
    }

    /**
     * @return list<string>
     */
    private function names(string $url): array
    {
        return array_column($this->get($url)->inertiaProps('employees.data'), 'name');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'employee_code' => 'EMP-001',
            'name' => 'Abdullah Al Mamun',
            'phone' => '01711-000000',
            'email' => 'abdullah@example.com',
            'department' => 'Engineering',
            'designation' => 'Engineer',
            'meal_preference' => 'no_beef',
            'lunch_enabled' => true,
            'dinner_enabled' => false,
            'status' => 'active',
            'joining_date' => '2025-01-15',
            'notes' => '',
            ...$overrides,
        ];
    }
}
