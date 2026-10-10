<?php

namespace Tests\Feature\Settings;

use App\Enums\OrderStatus;
use App\Enums\Permission;
use App\Models\ActivityLog;
use App\Models\Bill;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Rider;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-10-07 08:00:00'));
        $this->admin = User::factory()->vendorAdmin()->create(['email' => 'owner@example.com']);
    }

    /**
     * Every event the spec lists ends up in the log with who did it.
     */
    public function test_important_actions_are_logged(): void
    {
        $this->post('/login', ['login' => 'owner@example.com', 'password' => 'password']);

        $this->post('/clients', [
            'name' => 'XYZ Software Ltd.', 'contact_person' => 'Tanvir', 'phone' => '01711000000', 'address' => 'Gulshan', 'city' => 'Dhaka',
            'meal_types' => ['lunch'], 'status' => 'active',
        ]);
        $client = Client::sole();
        $this->put("/clients/{$client->id}", [...$client->only(['name', 'contact_person', 'phone', 'address', 'city']), 'meal_types' => ['lunch'], 'status' => 'active', 'notes' => 'VIP']);
        $this->post('/employees', ['client_id' => $client->id, 'employee_code' => 'EMP-1', 'name' => 'Rahim', 'meal_preference' => 'regular', 'lunch_enabled' => true, 'dinner_enabled' => false, 'status' => 'active']);
        $employee = Employee::sole();
        $this->post('/meals', ['client_id' => $client->id, 'date' => '2026-10-07', 'meals' => ['lunch' => [$employee->id]]]);
        $this->post('/meals', ['client_id' => $client->id, 'date' => '2026-10-07', 'meals' => ['lunch' => []]]);
        $this->post('/meals', ['client_id' => $client->id, 'date' => '2026-10-07', 'meals' => ['lunch' => [$employee->id]]]);
        $order = Order::sole();
        $rider = Rider::factory()->create();
        $this->post("/orders/{$order->id}/assign", ['rider_id' => $rider->id]);
        $this->patch("/orders/{$order->id}/status", ['status' => OrderStatus::PickedUp->value]);
        $bill = Bill::factory()->for($client)->create();
        $this->post("/bills/{$bill->id}/payments", ['payment_date' => '2026-10-07', 'amount' => '100', 'method' => 'cash']);
        $this->put('/settings', ['section' => 'billing', 'due_days' => 10]);
        $this->post('/logout');

        $logged = ActivityLog::query()->get()->map(fn (ActivityLog $log) => "{$log->module}.{$log->action}")->all();

        foreach ([
            'auth.login', 'clients.created', 'clients.updated', 'employees.created', 'meals.confirmed', 'meals.edited',
            'orders.created', 'deliveries.rider_assigned', 'deliveries.status_changed', 'billing.payment_added',
            'settings.updated', 'auth.logout',
        ] as $event) {
            $this->assertContains($event, $logged, "{$event} was not logged");
        }

        $this->assertSame(0, ActivityLog::whereNull('user_id')->count());
        $this->assertSame(0, ActivityLog::whereNull('ip_address')->count());
    }

    public function test_viewer_lists_and_filters_entries(): void
    {
        $client = Client::factory()->create();
        ActivityLog::create(['user_id' => $this->admin->id, 'module' => 'clients', 'action' => 'created', 'description' => 'Created client Alpha', 'client_id' => $client->id, 'properties' => ['old' => null], 'ip_address' => '10.0.0.1']);
        ActivityLog::create(['user_id' => $this->admin->id, 'module' => 'billing', 'action' => 'payment_added', 'description' => 'Payment of 500.00 received']);
        ActivityLog::create(['user_id' => null, 'module' => 'orders', 'action' => 'created', 'description' => 'Old entry', 'created_at' => now()->subDays(60)]);

        $this->actingAs($this->admin)->get('/activity-logs')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('activity-logs/index')
                ->has('logs.data', 2)
                ->where('logs.data.0.description', 'Payment of 500.00 received')
                ->where('logs.data.1.ip_address', '10.0.0.1')
                ->where('logs.data.1.client', $client->name)
                ->where('logs.data.1.user.name', $this->admin->name));

        $this->actingAs($this->admin)->get('/activity-logs?module=billing')->assertInertia(fn ($page) => $page->has('logs.data', 1));
        $this->actingAs($this->admin)->get('/activity-logs?search=alpha')->assertInertia(fn ($page) => $page->has('logs.data', 1));
        $this->actingAs($this->admin)->get("/activity-logs?client={$client->id}")->assertInertia(fn ($page) => $page->has('logs.data', 1));
        $this->actingAs($this->admin)->get('/activity-logs?from=2026-08-01&to=2026-08-31')->assertInertia(fn ($page) => $page->has('logs.data', 1)->where('logs.data.0.user', null));
    }

    public function test_viewer_needs_the_activity_log_permission(): void
    {
        $this->actingAs(User::factory()->vendorStaff()->create())->get('/activity-logs')->assertForbidden();
        $this->actingAs(User::factory()->clientAdmin()->create())->get('/activity-logs')->assertForbidden();
        $this->actingAs(User::factory()->rider()->create())->get('/activity-logs')->assertForbidden();
        $this->actingAs(User::factory()->vendorStaff()->withPermissions(Permission::ViewActivityLogs)->create())->get('/activity-logs')->assertOk();
    }

    public function test_responses_carry_security_headers(): void
    {
        $this->get('/login')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
