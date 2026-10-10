<?php

namespace Tests\Feature\Riders;

use App\Enums\OrderStatus;
use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiderManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->vendorAdmin()->create();
    }

    public function test_rider_list_can_be_searched_and_filtered(): void
    {
        Rider::factory()->create(['name' => 'Rahim Uddin', 'phone' => '01510000001']);
        Rider::factory()->create(['name' => 'Karim Mia']);
        Rider::factory()->inactive()->create(['name' => 'Hasan Ali']);

        $this->actingAs($this->admin)->get('/riders?search=rahim')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('riders/index')->has('riders.data', 1)->where('riders.data.0.name', 'Rahim Uddin'));

        $this->actingAs($this->admin)->get('/riders?search=01510000001')
            ->assertInertia(fn ($page) => $page->has('riders.data', 1));

        $this->actingAs($this->admin)->get('/riders?status=inactive')
            ->assertInertia(fn ($page) => $page->has('riders.data', 1)->where('riders.data.0.name', 'Hasan Ali'));
    }

    public function test_rider_can_be_added(): void
    {
        $this->actingAs($this->admin)
            ->post('/riders', $this->payload())
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $rider = Rider::sole();
        $this->assertSame('Rahim Uddin', $rider->name);
        $this->assertSame('01712345678', $rider->phone);
        $this->assertNull($rider->user_id);
        $this->assertDatabaseHas('activity_logs', ['module' => 'riders', 'action' => 'created']);
    }

    public function test_rider_with_login_can_sign_in_with_their_phone(): void
    {
        $this->actingAs($this->admin)
            ->post('/riders', $this->payload(['create_login' => true, 'password' => 'rider1234']))
            ->assertSessionHasNoErrors();

        $rider = Rider::with('user')->sole();
        $this->assertSame(UserRole::Rider, $rider->user->role);

        auth()->logout();
        $this->post('/login', ['login' => '01712-345678', 'password' => 'rider1234'])
            ->assertRedirect(route('rider.deliveries.index', absolute: false));
        $this->assertAuthenticatedAs($rider->user);
    }

    public function test_validation(): void
    {
        Rider::factory()->create(['phone' => '01712345678']);

        $this->actingAs($this->admin)
            ->post('/riders', $this->payload(['name' => '', 'phone' => '017 1234 5678', 'vehicle_type' => 'rocket', 'nid' => 'bad id!']))
            ->assertSessionHasErrors(['name', 'phone', 'vehicle_type', 'nid']);

        $this->actingAs($this->admin)
            ->post('/riders', $this->payload(['phone' => '01900000000', 'create_login' => true, 'password' => '']))
            ->assertSessionHasErrors('password');

        $this->assertSame(1, Rider::count());
    }

    public function test_a_login_phone_must_be_free_among_all_accounts(): void
    {
        User::factory()->create(['phone' => '01712345678']);

        $this->actingAs($this->admin)
            ->post('/riders', $this->payload(['create_login' => true, 'password' => 'rider1234']))
            ->assertSessionHasErrors('phone');
    }

    public function test_rider_can_be_edited_and_the_login_follows(): void
    {
        $user = User::factory()->rider()->create();
        $rider = $user->rider;

        $this->actingAs($this->admin)
            ->put("/riders/{$rider->id}", $this->payload(['name' => 'New Name', 'phone' => '01899999999', 'password' => 'newpass123']))
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('01899999999', $user->phone);
        $this->assertTrue(password_verify('newpass123', $user->password));
    }

    public function test_deactivating_a_rider_blocks_their_login(): void
    {
        $user = User::factory()->rider()->create();

        $this->actingAs($this->admin)
            ->patch("/riders/{$user->rider->id}/status", ['status' => 'inactive'])
            ->assertSessionHasNoErrors();

        $this->assertSame(RecordStatus::Inactive, $user->fresh()->status);

        $this->actingAs($user->fresh())->get('/rider/deliveries')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_rider_page_shows_delivery_history_and_counts(): void
    {
        $rider = Rider::factory()->create();
        foreach ([OrderStatus::Delivered, OrderStatus::Delivered, OrderStatus::OnTheWay, OrderStatus::Cancelled] as $status) {
            $order = Order::factory()->status($status)->create();
            Delivery::create(['order_id' => $order->id, 'rider_id' => $rider->id, 'assigned_at' => now()]);
        }

        $this->actingAs($this->admin)
            ->get("/riders/{$rider->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('riders/show')
                ->where('stats.assigned', 4)
                ->where('stats.completed', 2)
                ->where('stats.pending', 1)
                ->where('stats.cancelled', 1)
                ->has('deliveries.data', 4));
    }

    public function test_forms_render(): void
    {
        $rider = Rider::factory()->create();

        $this->actingAs($this->admin)->get('/riders/create')->assertOk()->assertInertia(fn ($page) => $page->component('riders/form')->where('rider', null));
        $this->actingAs($this->admin)->get("/riders/{$rider->id}/edit")->assertOk()->assertInertia(fn ($page) => $page->where('rider.id', $rider->id));
    }

    public function test_access_requires_the_riders_permission(): void
    {
        $rider = Rider::factory()->create();
        $staffWithout = User::factory()->vendorStaff()->withPermissions(Permission::ManageOrders)->create();

        foreach ([$staffWithout, User::factory()->clientAdmin()->create(), User::factory()->rider()->create()] as $user) {
            $this->actingAs($user)->get('/riders')->assertForbidden();
            $this->actingAs($user)->get("/riders/{$rider->id}")->assertForbidden();
            $this->actingAs($user)->post('/riders', $this->payload(['phone' => '01611111111']))->assertForbidden();
            $this->actingAs($user)->patch("/riders/{$rider->id}/status", ['status' => 'inactive'])->assertForbidden();
        }

        // Vendor staff have the riders permission by default.
        $this->actingAs(User::factory()->vendorStaff()->create())->get('/riders')->assertOk();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'name' => 'Rahim Uddin',
            'phone' => '01712-345678',
            'nid' => '1990123456789',
            'address' => 'Mirpur 10, Dhaka',
            'emergency_contact' => 'Karim (brother) 01711000000',
            'vehicle_type' => 'motorcycle',
            'vehicle_number' => 'DHA-11-2233',
            'joining_date' => '2026-01-15',
            'status' => 'active',
            'notes' => null,
            ...$overrides,
        ];
    }
}
