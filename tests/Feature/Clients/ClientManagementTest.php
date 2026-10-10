<?php

namespace Tests\Feature\Clients;

use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->vendorAdmin()->create();
    }

    public function test_vendor_admin_sees_the_client_list(): void
    {
        Client::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get('/clients')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('clients/index')
                ->has('clients.data', 3)
                ->has('clients.meta')
                ->where('can.create', true));
    }

    public function test_list_can_be_searched(): void
    {
        Client::factory()->create(['name' => 'XYZ Software Ltd.']);
        Client::factory()->create(['name' => 'ABC Bank Ltd.', 'contact_person' => 'Nusrat Jahan']);

        $this->actingAs($this->admin);

        $this->assertSame(['XYZ Software Ltd.'], $this->names('/clients?search=software'));
        $this->assertSame(['ABC Bank Ltd.'], $this->names('/clients?search=nusrat'));
        // Wildcards are treated as text, not as "match anything".
        $this->assertSame([], $this->names('/clients?search=%25'));
    }

    public function test_list_can_be_filtered_by_status_meal_type_and_city(): void
    {
        Client::factory()->create(['name' => 'Active Lunch', 'city' => 'Dhaka']);
        Client::factory()->inactive()->create(['name' => 'Inactive Client', 'city' => 'Dhaka']);
        Client::factory()->withDinner()->create(['name' => 'Dinner Client', 'city' => 'Chattogram']);
        Client::factory()->create(['name' => 'Archived Client'])->delete();

        $this->actingAs($this->admin);

        $this->assertSame(['Active Lunch', 'Dinner Client', 'Inactive Client'], $this->names('/clients'));
        $this->assertSame(['Inactive Client'], $this->names('/clients?status=inactive'));
        $this->assertSame(['Archived Client'], $this->names('/clients?status=archived'));
        $this->assertSame(['Dinner Client'], $this->names('/clients?meal_type=dinner'));
        $this->assertSame(['Dinner Client'], $this->names('/clients?city=Chattogram'));
    }

    public function test_list_can_be_sorted_and_paginated(): void
    {
        foreach (range(1, 20) as $number) {
            Client::factory()->create(['name' => sprintf('Client %02d', $number)]);
        }

        $this->actingAs($this->admin);

        $this->assertSame('Client 20', $this->names('/clients?sort=name&direction=desc')[0]);

        $this->get('/clients?page=2')->assertInertia(fn (Assert $page) => $page
            ->has('clients.data', 5)
            ->where('clients.meta.current_page', 2)
            ->where('clients.meta.total', 20));
    }

    public function test_invalid_list_parameters_are_rejected(): void
    {
        $this->actingAs($this->admin)
            ->get('/clients?sort=password&direction=sideways')
            ->assertSessionHasErrors(['sort', 'direction']);
    }

    public function test_vendor_admin_can_create_a_client(): void
    {
        $response = $this->actingAs($this->admin)->post('/clients', $this->validPayload());

        $client = Client::firstOrFail();
        $response->assertSessionHasNoErrors()->assertRedirect("/clients/{$client->id}");

        $this->assertSame('XYZ Software Ltd.', $client->name);
        $this->assertTrue($client->lunch_enabled);
        $this->assertTrue($client->dinner_enabled);
        $this->assertSame('10:30', substr((string) $client->lunch_cutoff_time, 0, 5));
        $this->assertSame($this->admin->id, $client->created_by);
        $this->assertSame('info@xyz.test', $client->email);

        $this->assertDatabaseHas('activity_logs', [
            'module' => 'clients',
            'action' => 'created',
            'subject_id' => $client->id,
            'client_id' => $client->id,
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->actingAs($this->admin)
            ->post('/clients', [])
            ->assertSessionHasErrors(['name', 'contact_person', 'phone', 'address', 'city', 'meal_types', 'status']);

        $this->assertSame(0, Client::count());
    }

    public function test_invalid_values_are_rejected(): void
    {
        Client::factory()->create(['name' => 'Taken Ltd.']);

        $this->actingAs($this->admin)
            ->post('/clients', $this->validPayload([
                'name' => 'Taken Ltd.',
                'phone' => 'call reception',
                'email' => 'not-an-email',
                'employee_count' => -5,
                'meal_types' => ['breakfast'],
                'lunch_cutoff_time' => '25:99',
                'status' => 'deleted',
            ]))
            ->assertSessionHasErrors(['name', 'phone', 'email', 'employee_count', 'meal_types.0', 'lunch_cutoff_time', 'status']);
    }

    public function test_at_least_one_meal_type_is_required(): void
    {
        $this->actingAs($this->admin)
            ->post('/clients', $this->validPayload(['meal_types' => []]))
            ->assertSessionHasErrors(['meal_types' => 'Choose at least one meal type.']);
    }

    public function test_client_details_page_shows_the_client(): void
    {
        $client = Client::factory()->create(['name' => 'ABC Bank Ltd.', 'lunch_cutoff_time' => '10:00:00']);

        $this->actingAs($this->admin)
            ->get("/clients/{$client->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('clients/show')
                ->where('client.name', 'ABC Bank Ltd.')
                ->where('client.lunch_cutoff_time', '10:00')
                ->where('client.meal_types', ['lunch'])
                ->has('overview.stats')
                ->has('users', 0)
                ->where('can.manageUsers', true));
    }

    public function test_vendor_admin_can_update_a_client_and_the_change_is_logged(): void
    {
        $client = Client::factory()->create(['name' => 'Old Name Ltd.']);

        $this->actingAs($this->admin)
            ->put("/clients/{$client->id}", $this->validPayload(['name' => 'New Name Ltd.', 'meal_types' => ['dinner']]))
            ->assertSessionHasNoErrors()
            ->assertRedirect("/clients/{$client->id}");

        $client->refresh();
        $this->assertSame('New Name Ltd.', $client->name);
        $this->assertFalse($client->lunch_enabled);
        $this->assertTrue($client->dinner_enabled);

        $log = ActivityLog::where('action', 'updated')->firstOrFail();
        $this->assertSame('Old Name Ltd.', $log->properties['old']['name']);
        $this->assertSame('New Name Ltd.', $log->properties['new']['name']);
    }

    public function test_a_client_keeps_its_own_name_on_update(): void
    {
        $client = Client::factory()->create(['name' => 'Same Ltd.']);

        $this->actingAs($this->admin)
            ->put("/clients/{$client->id}", $this->validPayload(['name' => 'Same Ltd.']))
            ->assertSessionHasNoErrors();
    }

    public function test_client_can_be_deactivated_and_activated(): void
    {
        $client = Client::factory()->create();

        $this->actingAs($this->admin)->patch("/clients/{$client->id}/status", ['status' => 'inactive'])->assertRedirect();
        $this->assertSame(RecordStatus::Inactive, $client->fresh()->status);

        $this->patch("/clients/{$client->id}/status", ['status' => 'active']);
        $this->assertSame(RecordStatus::Active, $client->fresh()->status);

        $this->patch("/clients/{$client->id}/status", ['status' => 'gone'])->assertSessionHasErrors('status');
    }

    public function test_client_can_be_archived_and_restored(): void
    {
        $client = Client::factory()->create();

        $this->actingAs($this->admin)->delete("/clients/{$client->id}")->assertRedirect('/clients');
        $this->assertSoftDeleted($client);

        // Archived clients can still be viewed, but not edited.
        $this->get("/clients/{$client->id}")->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('client.archived', true)
            ->where('can.restore', true)
            ->where('can.update', false));
        $this->get("/clients/{$client->id}/edit")->assertNotFound();

        $this->patch("/clients/{$client->id}/restore")->assertRedirect("/clients/{$client->id}");
        $this->assertNotSoftDeleted($client);
    }

    public function test_vendor_staff_can_manage_clients_by_default(): void
    {
        $this->actingAs(User::factory()->vendorStaff()->create())
            ->post('/clients', $this->validPayload())
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Client::count());
    }

    public function test_staff_without_the_client_permission_is_refused(): void
    {
        $client = Client::factory()->create();
        $this->actingAs(User::factory()->vendorStaff()->withPermissions(Permission::ViewReports)->create());

        $this->get('/clients')->assertForbidden();
        $this->get("/clients/{$client->id}")->assertForbidden();
        $this->post('/clients', $this->validPayload())->assertForbidden();
        $this->put("/clients/{$client->id}", $this->validPayload())->assertForbidden();
        $this->delete("/clients/{$client->id}")->assertForbidden();
    }

    #[DataProvider('nonVendorRoles')]
    public function test_client_and_rider_accounts_cannot_reach_client_management(string $state): void
    {
        $client = Client::factory()->create();
        $this->actingAs(User::factory()->{$state}()->create());

        $this->get('/clients')->assertForbidden();
        $this->get('/clients/create')->assertForbidden();
        $this->get("/clients/{$client->id}")->assertForbidden();
        $this->post('/clients', $this->validPayload())->assertForbidden();
        $this->patch("/clients/{$client->id}/status", ['status' => 'inactive'])->assertForbidden();
        $this->delete("/clients/{$client->id}")->assertForbidden();

        $this->assertNotSoftDeleted($client);
    }

    public static function nonVendorRoles(): array
    {
        return ['client admin' => ['clientAdmin'], 'rider' => ['rider']];
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/clients')->assertRedirect('/login');
        $this->post('/clients', $this->validPayload())->assertRedirect('/login');
    }

    public function test_dashboard_counts_active_clients(): void
    {
        Client::factory()->count(2)->create();
        Client::factory()->inactive()->create();

        $stats = collect($this->actingAs($this->admin)->get('/dashboard')->inertiaProps('stats'))->keyBy('key');

        $this->assertSame(2, $stats['total_clients']['value']);
    }

    /**
     * @return list<string>
     */
    private function names(string $url): array
    {
        return array_column($this->get($url)->inertiaProps('clients.data'), 'name');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'name' => 'XYZ Software Ltd.',
            'contact_person' => 'Tanvir Ahmed',
            'phone' => '01711-000000',
            'email' => 'Info@XYZ.test',
            'address' => 'House 10, Road 5, Gulshan',
            'city' => 'Dhaka',
            'delivery_address' => '',
            'employee_count' => 35,
            'meal_types' => ['lunch', 'dinner'],
            'office_start_time' => '09:00',
            'lunch_cutoff_time' => '10:30',
            'dinner_cutoff_time' => '',
            'special_instructions' => 'Call reception on arrival.',
            'status' => 'active',
            'notes' => '',
            ...$overrides,
        ];
    }
}
