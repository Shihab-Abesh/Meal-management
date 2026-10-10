<?php

namespace Tests\Feature\Orders;

use App\Enums\MealEntryStatus;
use App\Enums\OrderStatus;
use App\Enums\Permission;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\MealEntry;
use App\Models\Order;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pending → Confirmed → Preparing → Ready → Assigned → Picked Up →
 * On The Way → Delivered, with rider assignment and cancellation.
 */
class OrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Order $order;

    private Rider $rider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->vendorAdmin()->create();
        $this->order = Order::factory()->status(OrderStatus::Pending)->create();
        $this->rider = User::factory()->rider()->create()->rider;
    }

    public function test_the_full_lifecycle_from_pending_to_delivered(): void
    {
        foreach ([OrderStatus::Confirmed, OrderStatus::Preparing, OrderStatus::Ready] as $status) {
            $this->setStatus($status)->assertSessionHasNoErrors();
            $this->assertSame($status, $this->order->fresh()->status);
        }

        $this->assign()->assertSessionHasNoErrors();
        $this->assertSame(OrderStatus::Assigned, $this->order->fresh()->status);

        foreach ([OrderStatus::PickedUp, OrderStatus::OnTheWay, OrderStatus::Delivered] as $status) {
            $this->setStatus($status)->assertSessionHasNoErrors();
            $this->assertSame($status, $this->order->fresh()->status);
        }

        $delivery = Delivery::sole();
        $this->assertSame($this->rider->id, $delivery->rider_id);
        $this->assertSame($this->admin->id, $delivery->assigned_by);
        $this->assertNotNull($delivery->picked_up_at);
        $this->assertNotNull($delivery->on_the_way_at);
        $this->assertNotNull($delivery->delivered_at);

        // Every step is in the order's history.
        $this->assertSame(7, ActivityLog::where('subject_type', $this->order->getMorphClass())->where('subject_id', $this->order->id)->count());
    }

    public function test_invalid_transitions_are_refused(): void
    {
        $this->setStatus(OrderStatus::Delivered)->assertSessionHasErrors('status');
        $this->setStatus(OrderStatus::Assigned)->assertSessionHasErrors('status');

        $this->order->update(['status' => OrderStatus::Ready]);
        // Leaving the kitchen needs a rider first.
        $this->setStatus(OrderStatus::PickedUp)->assertSessionHasErrors('status');
        $this->setStatus(OrderStatus::Preparing)->assertSessionHasErrors('status');

        $this->order->update(['status' => OrderStatus::Delivered]);
        $this->setStatus(OrderStatus::Cancelled)->assertSessionHasErrors('status');

        $this->assertSame(OrderStatus::Delivered, $this->order->fresh()->status);
    }

    public function test_rider_can_be_changed_until_pickup_but_not_after(): void
    {
        $other = Rider::factory()->create();

        $this->assign()->assertSessionHasNoErrors();
        $this->assign($other)->assertSessionHasNoErrors();
        $this->assertSame($other->id, Delivery::sole()->rider_id);
        $this->assertDatabaseHas('activity_logs', ['action' => 'rider_changed', 'subject_id' => $this->order->id]);

        $this->setStatus(OrderStatus::PickedUp)->assertSessionHasNoErrors();
        $this->assign()->assertSessionHasErrors('rider_id');
        $this->assertSame($other->id, Delivery::sole()->rider_id);
    }

    public function test_inactive_riders_cannot_be_assigned(): void
    {
        $inactive = Rider::factory()->inactive()->create();

        $this->assign($inactive)->assertSessionHasErrors('rider_id');
        $this->assertSame(0, Delivery::count());
    }

    public function test_assignment_sets_the_delivery_time_and_note(): void
    {
        $this->actingAs($this->admin)
            ->post("/orders/{$this->order->id}/assign", ['rider_id' => $this->rider->id, 'delivery_time' => '11:45', 'notes' => 'Back gate'])
            ->assertSessionHasNoErrors();

        $this->assertSame('11:45', substr($this->order->fresh()->delivery_time, 0, 5));
        $this->assertSame('Back gate', Delivery::sole()->notes);
    }

    public function test_unassigning_returns_the_order_to_ready(): void
    {
        $this->assign();

        $this->actingAs($this->admin)->delete("/orders/{$this->order->id}/assign")->assertSessionHasNoErrors();

        $this->assertSame(OrderStatus::Ready, $this->order->fresh()->status);
        $this->assertSame(0, Delivery::count());
    }

    public function test_cancelling_cancels_the_meals_and_keeps_the_reason(): void
    {
        $client = Client::factory()->create();
        $employees = Employee::factory()->forClient($client)->count(3)->create();
        $order = Order::factory()->for($client)->create(['quantity' => 3, 'amount' => '360.00']);
        $employees->each(fn (Employee $employee) => MealEntry::factory()->create([
            'employee_id' => $employee->id,
            'meal_date' => $order->order_date->toDateString(),
        ]));

        $this->actingAs($this->admin)
            ->patch("/orders/{$order->id}/status", ['status' => 'cancelled', 'reason' => 'Office closed'])
            ->assertSessionHasNoErrors();

        $order->refresh();
        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertSame('Office closed', $order->cancel_reason);
        $this->assertNotNull($order->cancelled_at);
        $this->assertSame(3, MealEntry::where('status', MealEntryStatus::Cancelled)->count());
    }

    public function test_delivery_details_can_be_edited_until_delivered(): void
    {
        $this->actingAs($this->admin)
            ->put("/orders/{$this->order->id}", ['delivery_address' => 'New address', 'delivery_time' => '12:30', 'notes' => 'Ring twice'])
            ->assertSessionHasNoErrors();

        $this->assertSame('New address', $this->order->fresh()->delivery_address);

        $this->order->update(['status' => OrderStatus::Delivered]);

        $this->actingAs($this->admin)
            ->put("/orders/{$this->order->id}", ['delivery_address' => 'Too late'])
            ->assertSessionHasErrors('delivery_address');
    }

    public function test_order_page_shows_the_order_and_its_riders(): void
    {
        $this->actingAs($this->admin)
            ->get("/orders/{$this->order->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('orders/show')
                ->where('order.id', $this->order->id)
                ->where('order.next_statuses.0.value', 'confirmed')
                ->has('riders', 1)
                ->where('can.manageOrders', true));
    }

    public function test_delivery_board_groups_orders_by_tab(): void
    {
        Order::factory()->status(OrderStatus::Delivered)->create();

        $this->actingAs($this->admin)
            ->get('/deliveries')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('deliveries/index')
                ->where('counts.to_assign', 1)
                ->where('counts.delivered', 1)
                ->where('counts.all', 2)
                ->has('orders', 1));
    }

    public function test_staff_permissions_split_kitchen_and_delivery_steps(): void
    {
        $deliveriesOnly = User::factory()->vendorStaff()->withPermissions(Permission::ManageDeliveries)->create();
        $ordersOnly = User::factory()->vendorStaff()->withPermissions(Permission::ManageOrders)->create();

        $this->actingAs($deliveriesOnly)->get('/orders')->assertForbidden();
        $this->actingAs($deliveriesOnly)->get("/orders/{$this->order->id}")->assertOk();
        $this->actingAs($deliveriesOnly)->patch("/orders/{$this->order->id}/status", ['status' => 'confirmed'])->assertForbidden();

        $this->actingAs($ordersOnly)->get('/deliveries')->assertForbidden();
        $this->actingAs($ordersOnly)->post("/orders/{$this->order->id}/assign", ['rider_id' => $this->rider->id])->assertForbidden();
        $this->actingAs($ordersOnly)->patch("/orders/{$this->order->id}/status", ['status' => 'confirmed'])->assertSessionHasNoErrors();
    }

    public function test_clients_and_riders_cannot_reach_order_management(): void
    {
        foreach ([User::factory()->clientAdmin()->create(), User::factory()->rider()->create()] as $user) {
            $this->actingAs($user)->get('/orders')->assertForbidden();
            $this->actingAs($user)->get("/orders/{$this->order->id}")->assertForbidden();
            $this->actingAs($user)->patch("/orders/{$this->order->id}/status", ['status' => 'confirmed'])->assertForbidden();
            $this->actingAs($user)->post("/orders/{$this->order->id}/assign", ['rider_id' => $this->rider->id])->assertForbidden();
            $this->actingAs($user)->get('/deliveries')->assertForbidden();
        }

        $this->assertSame(OrderStatus::Pending, $this->order->fresh()->status);
    }

    private function setStatus(OrderStatus $status)
    {
        return $this->actingAs($this->admin)->patch("/orders/{$this->order->id}/status", ['status' => $status->value]);
    }

    private function assign(?Rider $rider = null)
    {
        return $this->actingAs($this->admin)->post("/orders/{$this->order->id}/assign", ['rider_id' => ($rider ?? $this->rider)->id]);
    }
}
