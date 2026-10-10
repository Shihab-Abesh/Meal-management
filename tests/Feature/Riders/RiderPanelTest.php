<?php

namespace Tests\Feature\Riders;

use App\Enums\OrderStatus;
use App\Models\Client;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Business rule 10: riders only access their assigned deliveries, and never
 * see amounts, other clients or employee data.
 */
class RiderPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $rider;

    private Delivery $delivery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rider = User::factory()->rider()->create();
        $this->delivery = $this->deliveryFor($this->rider, OrderStatus::Assigned, ['amount' => '4200.00', 'quantity' => 35]);
    }

    public function test_rider_lands_on_their_deliveries_after_sign_in(): void
    {
        $this->post('/login', ['login' => $this->rider->phone, 'password' => 'password'])
            ->assertRedirect(route('rider.deliveries.index', absolute: false));
    }

    public function test_rider_sees_only_their_own_deliveries_without_amounts(): void
    {
        $this->deliveryFor(User::factory()->rider()->create(), OrderStatus::Assigned);

        $response = $this->actingAs($this->rider)->get('/rider/deliveries')->assertOk();

        $response->assertInertia(fn ($page) => $page
            ->component('rider/deliveries')
            ->has('deliveries', 1)
            ->where('deliveries.0.id', $this->delivery->id)
            ->where('deliveries.0.quantity', 35)
            ->where('deliveries.0.next_step.value', 'picked_up')
            ->missing('deliveries.0.amount')
            ->missing('deliveries.0.unit_price'));

        $this->assertStringNotContainsString('4200', $response->getContent());
    }

    public function test_rider_cannot_open_or_update_someone_elses_delivery(): void
    {
        $other = $this->deliveryFor(User::factory()->rider()->create(), OrderStatus::Assigned);

        $this->actingAs($this->rider)->get("/rider/deliveries/{$other->id}")->assertForbidden();
        $this->actingAs($this->rider)->post("/rider/deliveries/{$other->id}/status", ['status' => 'picked_up'])->assertForbidden();

        $this->assertSame(OrderStatus::Assigned, $other->order->fresh()->status);
    }

    public function test_rider_moves_their_delivery_through_each_step(): void
    {
        $this->actingAs($this->rider)->get("/rider/deliveries/{$this->delivery->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('rider/delivery')
                ->has('delivery.contact_phone')
                ->missing('delivery.amount'));

        foreach (['picked_up', 'on_the_way', 'delivered'] as $status) {
            $this->actingAs($this->rider)
                ->post("/rider/deliveries/{$this->delivery->id}/status", ['status' => $status])
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(OrderStatus::Delivered, $this->delivery->order->fresh()->status);
        $this->assertNotNull($this->delivery->fresh()->delivered_at);
        $this->assertDatabaseHas('activity_logs', ['module' => 'deliveries', 'user_id' => $this->rider->id]);
    }

    public function test_rider_cannot_skip_steps_or_use_kitchen_statuses(): void
    {
        $this->actingAs($this->rider)
            ->post("/rider/deliveries/{$this->delivery->id}/status", ['status' => 'delivered'])
            ->assertSessionHasErrors('status');

        foreach (['cancelled', 'ready', 'confirmed'] as $status) {
            $this->actingAs($this->rider)
                ->post("/rider/deliveries/{$this->delivery->id}/status", ['status' => $status])
                ->assertSessionHasErrors('status');
        }

        $this->assertSame(OrderStatus::Assigned, $this->delivery->order->fresh()->status);
    }

    public function test_history_lists_finished_deliveries(): void
    {
        $this->deliveryFor($this->rider, OrderStatus::Delivered);
        $this->deliveryFor($this->rider, OrderStatus::Cancelled);

        $this->actingAs($this->rider)->get('/rider/deliveries/history')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('rider/history')->has('deliveries.data', 2));

        $this->actingAs($this->rider)->get('/rider/deliveries/history?status=delivered')
            ->assertInertia(fn ($page) => $page->has('deliveries.data', 1));
    }

    public function test_rider_cannot_reach_vendor_or_client_areas(): void
    {
        foreach (['/clients', '/employees', '/meals', '/orders', '/deliveries', '/riders', '/pricing', '/menu-items', '/vendor-profile', '/menu/today'] as $path) {
            $this->actingAs($this->rider)->get($path)->assertForbidden();
        }
    }

    public function test_dashboard_shows_the_riders_own_counts_only(): void
    {
        $this->deliveryFor(User::factory()->rider()->create(), OrderStatus::Assigned);

        $stats = collect($this->actingAs($this->rider)->get('/dashboard')->inertiaProps('stats'))->keyBy('key');

        $this->assertSame(1, $stats['todays_deliveries']['value']);
        $this->assertSame(1, $stats['pending_deliveries']['value']);
        $this->assertArrayNotHasKey('todays_revenue', $stats->all());
    }

    public function test_vendor_users_cannot_use_the_rider_panel(): void
    {
        $this->actingAs(User::factory()->vendorAdmin()->create())->get('/rider/deliveries')->assertForbidden();
        $this->actingAs(User::factory()->clientAdmin()->create())->get('/rider/deliveries')->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function deliveryFor(User $rider, OrderStatus $status, array $attributes = []): Delivery
    {
        $order = Order::factory()->for(Client::factory())->status($status)->create($attributes);

        return Delivery::create(['order_id' => $order->id, 'rider_id' => $rider->rider->id, 'assigned_at' => now()]);
    }
}
