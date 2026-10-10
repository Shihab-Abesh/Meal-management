<?php

namespace Tests\Feature\Orders;

use App\Enums\MealType;
use App\Enums\MenuCategory;
use App\Enums\OrderStatus;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ClientMenuPrice;
use App\Models\DailyMenu;
use App\Models\Employee;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Confirmed meals become orders automatically, and the order always equals
 * the confirmed meals (business rule 9).
 */
class OrderGenerationTest extends TestCase
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

        $this->travelTo(now()->setDateTimeFrom(self::DATE.' 08:00:00'));

        $this->admin = User::factory()->vendorAdmin()->create();
        $this->client = Client::factory()->withDinner()->create([
            'lunch_cutoff_time' => '10:00',
            'dinner_cutoff_time' => '16:00',
            'delivery_address' => '5th floor, House 10, Road 5',
        ]);
        $this->employees = Employee::factory()->forClient($this->client)->count(22)->create(['dinner_enabled' => true]);
        $this->setPrice('lunch', '120');
        $this->setPrice('dinner', '140');
    }

    public function test_vendor_confirmation_creates_a_confirmed_order_with_totals(): void
    {
        $this->save($this->admin, ['lunch' => $this->employees->modelKeys()])->assertSessionHasNoErrors();

        $order = Order::sole();

        // 22 lunches × ৳120 = ৳2,640
        $this->assertSame(22, $order->quantity);
        $this->assertSame('2640.00', $order->amount);
        $this->assertSame('120.00', $order->unitPrice());
        $this->assertSame(OrderStatus::Confirmed, $order->status);
        $this->assertSame(MealType::Lunch, $order->meal_type);
        $this->assertSame(self::DATE, $order->order_date->toDateString());
        $this->assertSame('5th floor, House 10, Road 5', $order->delivery_address);
        $this->assertSame(sprintf('ORD-261006-%05d', $order->id), $order->order_number);
        $this->assertDatabaseHas('activity_logs', ['module' => 'orders', 'action' => 'created', 'subject_id' => $order->id]);
    }

    public function test_each_meal_type_gets_its_own_order(): void
    {
        $this->save($this->admin, [
            'lunch' => $this->employees->take(10)->modelKeys(),
            'dinner' => $this->employees->take(4)->modelKeys(),
        ]);

        $this->assertSame(2, Order::count());
        $this->assertSame('1200.00', Order::where('meal_type', 'lunch')->value('amount'));
        $this->assertSame('560.00', Order::where('meal_type', 'dinner')->value('amount'));
    }

    public function test_client_confirmation_creates_a_pending_order(): void
    {
        $clientUser = User::factory()->clientAdmin($this->client)->create();

        $this->save($clientUser, ['lunch' => $this->employees->take(3)->modelKeys()])->assertSessionHasNoErrors();

        $this->assertSame(OrderStatus::Pending, Order::sole()->status);
    }

    public function test_editing_meals_updates_the_order_without_duplicating_it(): void
    {
        $this->save($this->admin, ['lunch' => $this->employees->take(5)->modelKeys()]);
        $this->save($this->admin, ['lunch' => $this->employees->take(8)->modelKeys()]);

        $order = Order::sole();
        $this->assertSame(8, $order->quantity);
        $this->assertSame('960.00', $order->amount);
        $this->assertSame(1, ActivityLog::where('module', 'orders')->where('action', 'updated')->count());
    }

    public function test_removing_every_meal_cancels_the_order_and_adding_them_back_restores_it(): void
    {
        $this->save($this->admin, ['lunch' => $this->employees->take(3)->modelKeys()]);
        $this->save($this->admin, ['lunch' => []]);

        $order = Order::sole();
        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertSame(0, $order->quantity);
        $this->assertSame('0.00', $order->amount);

        $this->save($this->admin, ['lunch' => $this->employees->take(2)->modelKeys()]);

        $order->refresh();
        $this->assertSame(OrderStatus::Confirmed, $order->status);
        $this->assertSame(2, $order->quantity);
        $this->assertNull($order->cancelled_at);
        $this->assertSame(1, Order::count());
    }

    public function test_no_order_is_created_when_nothing_is_selected(): void
    {
        $this->save($this->admin, ['lunch' => []])->assertSessionHasNoErrors();

        $this->assertSame(0, Order::count());
    }

    public function test_order_lists_the_days_menu(): void
    {
        $menu = DailyMenu::factory()->create(['menu_date' => self::DATE, 'meal_type' => MealType::Lunch]);
        $rice = MenuItem::factory()->create(['name' => 'Plain Rice', 'category' => MenuCategory::MainCourse]);
        $curry = MenuItem::factory()->create(['name' => 'Chicken Curry', 'category' => MenuCategory::Protein]);
        $menu->items()->attach([$rice->id => ['sort_order' => 0], $curry->id => ['sort_order' => 1]]);

        $this->save($this->admin, ['lunch' => $this->employees->take(4)->modelKeys()]);

        $items = Order::sole()->items;
        $this->assertSame(['Plain Rice', 'Chicken Curry'], $items->pluck('name')->all());
        $this->assertSame([4, 4], $items->pluck('quantity')->all());
    }

    public function test_order_amount_keeps_the_price_each_meal_was_confirmed_at(): void
    {
        $this->save($this->admin, ['lunch' => $this->employees->take(2)->modelKeys()]);

        // The price changes for today; the two existing meals keep ৳120.
        $this->setPrice('lunch', '150', self::DATE);
        $this->save($this->admin, ['lunch' => $this->employees->take(3)->modelKeys()]);

        $this->assertSame('390.00', Order::sole()->amount);
    }

    public function test_orders_page_lists_the_days_orders(): void
    {
        $this->save($this->admin, ['lunch' => $this->employees->take(3)->modelKeys()]);

        $this->actingAs($this->admin)
            ->get('/orders?date='.self::DATE)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('orders/index')
                ->has('orders.data', 1)
                ->where('orders.data.0.quantity', 3)
                ->where('orders.data.0.amount', '360.00')
                ->where('statusCounts.confirmed', 1));
    }

    public function test_amounts_are_hidden_from_staff_without_financial_access(): void
    {
        $this->save($this->admin, ['lunch' => $this->employees->take(3)->modelKeys()]);
        $staff = User::factory()->vendorStaff()->create();

        $this->actingAs($staff)
            ->get('/orders?date='.self::DATE)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('orders.data.0.quantity', 3)
                ->where('orders.data.0.amount', null)
                ->where('orders.data.0.unit_price', null));
    }

    /**
     * @param  array<string, list<int>>  $meals
     */
    private function save(User $user, array $meals): TestResponse
    {
        return $this->actingAs($user)->post('/meals', [
            'client_id' => $this->client->id,
            'date' => self::DATE,
            'meals' => $meals,
        ]);
    }

    private function setPrice(string $type, string $price, string $from = '2026-01-01'): void
    {
        $record = new ClientMenuPrice(['meal_type' => $type, 'price' => $price, 'effective_from' => $from]);
        $record->client_id = $this->client->id;
        $record->save();
    }
}
