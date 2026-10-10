<?php

namespace Tests\Feature\Notifications;

use App\Enums\OrderStatus;
use App\Enums\Permission;
use App\Models\Bill;
use App\Models\Client;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\Order;
use App\Models\User;
use App\Notifications\Vendor\BillsOverdue;
use App\Notifications\Vendor\ClientOrderCancelled;
use App\Notifications\Vendor\ClientOrderChanged;
use App\Notifications\Vendor\ClientOrderPlaced;
use App\Notifications\Vendor\DeliveryCompleted;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * When a client orders, the vendor team is told — the way an order app
 * would: one alert per order thread, to the people who handle orders.
 */
class VendorNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private Client $client;

    private User $clientUser;

    /** @var Collection<int, Employee> */
    private Collection $employees;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-10-07 08:00:00'));
        $this->admin = User::factory()->vendorAdmin()->create();
        $this->staff = User::factory()->vendorStaff()->create();
        $this->client = Client::factory()->create(['name' => 'XYZ Software Ltd.', 'lunch_cutoff_time' => '10:00']);
        $this->clientUser = User::factory()->clientAdmin($this->client)->create(['name' => 'Tanvir Ahmed']);
        $this->employees = Employee::factory()->forClient($this->client)->count(30)->create();
    }

    public function test_a_client_order_alerts_the_vendor_team(): void
    {
        $noOrders = User::factory()->vendorStaff()->withPermissions(Permission::ManageMeals)->create();
        $otherClient = User::factory()->clientAdmin()->create();
        $rider = User::factory()->rider()->create();

        $this->order(22)->assertSessionHasNoErrors();

        $order = Order::sole();
        foreach ([$this->admin, $this->staff] as $user) {
            $alert = $user->notifications()->sole();
            $this->assertSame(ClientOrderPlaced::class, $alert->type);
            $this->assertSame('New lunch order from XYZ Software Ltd.', $alert->data['title']);
            $this->assertSame("22 meals for Wed, 07 Oct · {$order->order_number} · by Tanvir Ahmed", $alert->data['body']);
            $this->assertSame("/orders/{$order->id}", $alert->data['url']);
            $this->assertNull($alert->read_at);
        }

        foreach ([$noOrders, $otherClient, $rider, $this->clientUser] as $user) {
            $this->assertSame(0, $user->notifications()->count());
        }
    }

    public function test_vendor_confirmations_do_not_alert_anyone(): void
    {
        $this->actingAs($this->admin)->post('/meals', ['client_id' => $this->client->id, 'date' => '2026-10-07', 'meals' => ['lunch' => $this->ids(5)]]);

        $this->assertSame(0, DatabaseNotification::count());
    }

    public function test_edits_to_an_unread_order_fold_into_one_alert(): void
    {
        $this->order(22);
        $this->order(25);
        $this->order(24);

        // The vendor has not looked yet: still one "new order", now for 24.
        $alert = $this->admin->notifications()->sole();
        $this->assertSame(ClientOrderPlaced::class, $alert->type);
        $this->assertSame(24, $alert->data['quantity']);

        // After reading it, a change is news of its own…
        $alert->markAsRead();
        $this->order(30);
        $change = $this->admin->unreadNotifications()->sole();
        $this->assertSame(ClientOrderChanged::class, $change->type);
        $this->assertStringStartsWith('24 → 30 meals', $change->data['body']);

        // …further changes keep what the vendor last saw…
        $this->order(28);
        $this->assertStringStartsWith('24 → 28 meals', $this->admin->unreadNotifications()->sole()->data['body']);

        // …and changing back to it means there is nothing to report.
        $this->order(24);
        $this->assertSame(0, $this->admin->unreadNotifications()->count());
        $this->assertSame(1, $this->admin->notifications()->count());
    }

    public function test_removing_every_meal_alerts_a_cancellation(): void
    {
        $this->order(10);
        $this->admin->notifications()->sole()->markAsRead();

        $this->order(0);

        $alert = $this->admin->unreadNotifications()->sole();
        $this->assertSame(ClientOrderCancelled::class, $alert->type);
        $this->assertSame('XYZ Software Ltd. cancelled their lunch order', $alert->data['title']);
        $this->assertStringStartsWith('10 meals for Wed, 07 Oct were removed', $alert->data['body']);
    }

    public function test_users_can_switch_order_alerts_off(): void
    {
        $this->actingAs($this->staff)->put('/notifications/preferences', ['orders' => false, 'deliveries' => true])->assertSessionHasNoErrors();

        $this->order(5);

        $this->assertSame(0, $this->staff->notifications()->count());
        $this->assertSame(1, $this->admin->notifications()->count());
        $this->assertFalse($this->staff->fresh()->alertPreferences()['orders']);
    }

    public function test_feed_returns_the_unread_count_and_newest_alerts(): void
    {
        $this->order(5);
        $this->travel(1)->minute();
        $this->admin->notify(new DeliveryCompleted('Delivered: ABC lunch', '10 meals', '/orders/1'));

        $this->actingAs($this->admin)->getJson('/notifications/feed')
            ->assertOk()
            ->assertJsonPath('unread', 2)
            ->assertJsonCount(2, 'items')
            ->assertJsonPath('items.0.kind', 'delivery_completed')
            ->assertJsonPath('items.1.kind', 'order_placed')
            ->assertJsonPath('items.1.read', false);

        // The starting count is shared with every page.
        $this->actingAs($this->admin)->get('/dashboard')->assertInertia(fn ($page) => $page->where('notifications.unread', 2));
        $this->actingAs($this->clientUser)->get('/dashboard')->assertInertia(fn ($page) => $page->where('notifications', null));
    }

    public function test_opening_marks_read_and_goes_to_the_order(): void
    {
        $this->order(5);
        $alert = $this->admin->notifications()->sole();

        $this->actingAs($this->admin)->get("/notifications/{$alert->id}/open")->assertRedirect('/orders/'.Order::sole()->id);

        $this->assertNotNull($alert->fresh()->read_at);
        $this->assertNull($this->staff->notifications()->sole()->read_at);
    }

    public function test_nobody_can_touch_someone_elses_notification(): void
    {
        $this->order(5);
        $staffAlert = $this->staff->notifications()->sole();

        $this->actingAs($this->admin)->get("/notifications/{$staffAlert->id}/open")->assertNotFound();
        $this->actingAs($this->admin)->patch("/notifications/{$staffAlert->id}", ['read' => true])->assertNotFound();
        $this->assertNull($staffAlert->fresh()->read_at);
    }

    public function test_read_state_can_be_changed_and_read_ones_cleared(): void
    {
        $this->order(5);
        $this->admin->notify(new DeliveryCompleted('Delivered', '10 meals', '/orders/1'));
        $alert = $this->admin->notifications()->where('type', ClientOrderPlaced::class)->sole();

        $this->actingAs($this->admin)->patch("/notifications/{$alert->id}", ['read' => true])->assertSessionHasNoErrors();
        $this->assertNotNull($alert->fresh()->read_at);
        $this->actingAs($this->admin)->patch("/notifications/{$alert->id}", ['read' => false]);
        $this->assertNull($alert->fresh()->read_at);

        $this->actingAs($this->admin)->postJson('/notifications/read-all')->assertOk()->assertJsonPath('unread', 0);
        $this->assertSame(0, $this->admin->unreadNotifications()->count());

        $this->actingAs($this->admin)->delete('/notifications/read');
        $this->assertSame(0, $this->admin->notifications()->count());
        $this->assertSame(1, $this->staff->notifications()->count());
    }

    public function test_notifications_page_filters_and_counts(): void
    {
        $this->order(5);
        $this->admin->notifications()->sole()->markAsRead();
        $this->admin->notify(new DeliveryCompleted('Delivered', '10 meals', '/orders/1'));

        $this->actingAs($this->admin)->get('/notifications')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('notifications/index')
                ->has('notifications.data', 2)
                ->where('counts.all', 2)
                ->where('counts.unread', 1)
                ->where('preferences', ['orders' => true, 'deliveries' => true, 'billing' => true])
                ->has('categories', 3));

        $this->actingAs($this->admin)->get('/notifications?show=unread')->assertInertia(fn ($page) => $page->has('notifications.data', 1));
        $this->actingAs($this->admin)->get('/notifications?category=orders')->assertInertia(fn ($page) => $page->has('notifications.data', 1)->where('notifications.data.0.kind', 'order_placed'));
    }

    public function test_a_delivered_order_alerts_everyone_handling_deliveries_but_the_one_who_marked_it(): void
    {
        $order = Order::factory()->for($this->client)->status(OrderStatus::OnTheWay)->create(['quantity' => 22]);
        $rider = User::factory()->rider()->create(['name' => 'Rahim']);
        Delivery::create(['order_id' => $order->id, 'rider_id' => $rider->rider->id, 'assigned_at' => now()]);

        $this->actingAs($rider)->post("/rider/deliveries/{$order->delivery->id}/status", ['status' => 'delivered'])->assertSessionHasNoErrors();

        $alert = $this->admin->notifications()->sole();
        $this->assertSame(DeliveryCompleted::class, $alert->type);
        $this->assertSame('Delivered: XYZ Software Ltd. lunch', $alert->data['title']);
        $this->assertStringStartsWith('22 meals delivered by Rahim', $alert->data['body']);
        $this->assertSame(1, $this->staff->notifications()->count());

        // Marked delivered by the admin: everyone else hears about it, the admin does not.
        $second = Order::factory()->for($this->client)->status(OrderStatus::OnTheWay)->create(['order_date' => '2026-10-08']);
        Delivery::create(['order_id' => $second->id, 'rider_id' => $rider->rider->id, 'assigned_at' => now()]);
        $this->actingAs($this->admin)->patch("/orders/{$second->id}/status", ['status' => 'delivered']);
        $this->assertSame(1, $this->admin->notifications()->count());
        $this->assertSame(2, $this->staff->notifications()->count());
    }

    public function test_overdue_bills_alert_people_who_see_billing(): void
    {
        Bill::factory()->for($this->client)->create(['due_date' => '2026-10-01', 'due_amount' => '1200.00']);
        Bill::factory()->for($this->client)->create(['due_date' => '2026-10-02', 'due_amount' => '800.00', 'period_start' => '2026-08-01', 'period_end' => '2026-08-31']);

        $this->artisan('bills:mark-overdue')->assertSuccessful();

        $alert = $this->admin->notifications()->sole();
        $this->assertSame(BillsOverdue::class, $alert->type);
        $this->assertSame('2 bills are now overdue', $alert->data['title']);
        $this->assertStringStartsWith('৳2,000.00 is still owed', $alert->data['body']);
        // Staff have no billing access by default.
        $this->assertSame(0, $this->staff->notifications()->count());

        // Nothing new the next night.
        $this->artisan('bills:mark-overdue');
        $this->assertSame(1, $this->admin->notifications()->count());
    }

    public function test_only_vendor_users_have_notifications(): void
    {
        foreach ([$this->clientUser, User::factory()->rider()->create()] as $user) {
            $this->actingAs($user)->get('/notifications')->assertForbidden();
            $this->actingAs($user)->getJson('/notifications/feed')->assertForbidden();
        }
    }

    private function order(int $meals): TestResponse
    {
        return $this->actingAs($this->clientUser)->post('/meals', [
            'client_id' => $this->client->id,
            'date' => '2026-10-07',
            'meals' => ['lunch' => $this->ids($meals)],
        ]);
    }

    /**
     * @return list<int>
     */
    private function ids(int $count): array
    {
        return $this->employees->take($count)->modelKeys();
    }
}
