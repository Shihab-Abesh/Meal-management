<?php

namespace Tests\Feature\Menu;

use App\Enums\MealType;
use App\Enums\MenuCategory;
use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Models\Client;
use App\Models\ClientMenuPrice;
use App\Models\DailyMenu;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MenuManagementTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->vendorAdmin()->create();
    }

    public function test_menu_items_can_be_created_with_an_image_and_listed(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)
            ->post('/menu-items', [
                'name' => 'Chicken Curry',
                'category' => 'protein',
                'description' => 'Home style',
                'default_price' => '70.5',
                'status' => 'active',
                'image' => UploadedFile::fake()->createWithContent('curry.png', base64_decode(self::PNG)),
            ])
            ->assertSessionHasNoErrors();

        $item = MenuItem::firstOrFail();
        $this->assertSame(MenuCategory::Protein, $item->category);
        $this->assertSame('70.50', $item->default_price);
        Storage::disk('public')->assertExists($item->image_path);

        $this->get('/menu-items?category=protein')->assertInertia(fn (Assert $page) => $page
            ->component('menu-items/index')
            ->has('items.data', 1)
            ->where('items.data.0.menus_count', 0));
    }

    public function test_menu_item_validation(): void
    {
        MenuItem::factory()->create(['name' => 'Dal']);

        $this->actingAs($this->admin)
            ->post('/menu-items', ['name' => 'Dal', 'category' => 'snack', 'default_price' => '-1', 'status' => 'gone'])
            ->assertSessionHasErrors(['name', 'category', 'default_price', 'status']);

        $this->post('/menu-items', [
            'name' => 'Pic', 'category' => 'other', 'default_price' => '1', 'status' => 'active',
            'image' => UploadedFile::fake()->create('menu.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('image');
    }

    public function test_menu_item_can_be_updated_and_disabled(): void
    {
        $item = MenuItem::factory()->create(['name' => 'Dal']);

        $this->actingAs($this->admin)
            ->put("/menu-items/{$item->id}", ['name' => 'Yellow Dal', 'category' => 'side_dish', 'default_price' => '15', 'status' => 'active'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Yellow Dal', $item->fresh()->name);

        $this->patch("/menu-items/{$item->id}/status", ['status' => 'inactive']);
        $this->assertSame(RecordStatus::Inactive, $item->fresh()->status);
    }

    public function test_items_used_on_a_menu_cannot_be_deleted(): void
    {
        $used = MenuItem::factory()->create();
        $unused = MenuItem::factory()->create();
        DailyMenu::factory()->create()->items()->attach($used->id);

        $this->actingAs($this->admin);

        $this->delete("/menu-items/{$used->id}")->assertSessionHasErrors('item');
        $this->assertModelExists($used);

        $this->delete("/menu-items/{$unused->id}")->assertSessionHasNoErrors();
        $this->assertModelMissing($unused);
    }

    public function test_daily_menu_is_created_with_ordered_items(): void
    {
        [$rice, $curry, $dal] = MenuItem::factory()->count(3)->create();

        // Saved from the chart, the chart comes back.
        $this->actingAs($this->admin)
            ->from('/daily-menus?date=2026-10-06')
            ->post('/daily-menus', [
                'menu_date' => '2026-10-06',
                'meal_type' => 'lunch',
                'items' => [$curry->id, $rice->id, $dal->id],
                'price' => '120',
                'notes' => 'Regular lunch',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/daily-menus?date=2026-10-06');

        $menu = DailyMenu::firstOrFail();
        $this->assertSame('2026-10-06', $menu->menu_date->toDateString());
        $this->assertSame('120.00', $menu->price);
        $this->assertSame([$curry->id, $rice->id, $dal->id], $menu->items->pluck('id')->all());
        $this->assertDatabaseHas('activity_logs', ['module' => 'menu', 'action' => 'menu_created']);
    }

    public function test_only_one_menu_per_date_and_meal(): void
    {
        $item = MenuItem::factory()->create();
        DailyMenu::factory()->create(['menu_date' => '2026-10-06', 'meal_type' => MealType::Lunch]);

        $this->actingAs($this->admin);

        $this->post('/daily-menus', ['menu_date' => '2026-10-06', 'meal_type' => 'lunch', 'items' => [$item->id]])
            ->assertSessionHasErrors('meal_type');

        $this->post('/daily-menus', ['menu_date' => '2026-10-06', 'meal_type' => 'dinner', 'items' => [$item->id]])
            ->assertSessionHasNoErrors();
    }

    public function test_daily_menu_validation(): void
    {
        $this->actingAs($this->admin)
            ->post('/daily-menus', ['menu_date' => 'tomorrow', 'meal_type' => 'brunch', 'items' => [999999], 'price' => 'free'])
            ->assertSessionHasErrors(['menu_date', 'meal_type', 'items.0', 'price']);

        $this->post('/daily-menus', ['menu_date' => '2026-10-06', 'meal_type' => 'lunch', 'items' => []])
            ->assertSessionHasErrors('items');
    }

    public function test_menu_can_be_updated(): void
    {
        [$a, $b] = MenuItem::factory()->count(2)->create();
        $menu = DailyMenu::factory()->create(['menu_date' => '2026-10-06']);
        $menu->items()->attach($a->id);

        $this->actingAs($this->admin)
            ->put("/daily-menus/{$menu->id}", ['menu_date' => '2026-10-06', 'meal_type' => 'lunch', 'items' => [$b->id], 'price' => ''])
            ->assertSessionHasNoErrors();

        $this->assertSame([$b->id], $menu->fresh()->items->pluck('id')->all());
        $this->assertNull($menu->fresh()->price);
    }

    public function test_staff_without_pricing_permission_cannot_change_the_menu_price(): void
    {
        $item = MenuItem::factory()->create();
        $menu = DailyMenu::factory()->create(['menu_date' => '2026-10-06', 'price' => '150']);
        $menu->items()->attach($item->id);

        $this->actingAs(User::factory()->vendorStaff()->create())
            ->put("/daily-menus/{$menu->id}", ['menu_date' => '2026-10-06', 'meal_type' => 'lunch', 'items' => [$item->id], 'price' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertSame('150.00', $menu->fresh()->price);
    }

    public function test_menu_can_be_duplicated_to_another_date(): void
    {
        [$a, $b] = MenuItem::factory()->count(2)->create();
        $menu = DailyMenu::factory()->create(['menu_date' => '2026-10-05', 'price' => '125', 'notes' => 'Same again']);
        $menu->items()->attach([$a->id => ['sort_order' => 1], $b->id => ['sort_order' => 0]]);

        $this->actingAs($this->admin)
            ->post("/daily-menus/{$menu->id}/duplicate", ['date' => '2026-10-06'])
            ->assertSessionHasNoErrors();

        $copy = DailyMenu::where('menu_date', '2026-10-06')->firstOrFail();
        $this->assertSame([$b->id, $a->id], $copy->items->pluck('id')->all());
        $this->assertSame('125.00', $copy->price);
        $this->assertSame('Same again', $copy->notes);

        // A second copy to the same date is refused.
        $this->post("/daily-menus/{$menu->id}/duplicate", ['date' => '2026-10-06'])->assertSessionHasErrors('date');
        $this->assertSame(2, DailyMenu::count());
    }

    public function test_week_chart_runs_saturday_to_friday_and_offers_the_previous_menu(): void
    {
        $rice = MenuItem::factory()->create(['name' => 'Plain Rice']);
        $older = DailyMenu::factory()->create(['menu_date' => '2026-09-28']);
        $older->items()->attach($rice->id);
        $latest = DailyMenu::factory()->create(['menu_date' => '2026-10-01', 'price' => '130']);
        $latest->items()->attach($rice->id);
        // Inside the week of Tuesday 06 Oct: Saturday 03 to Friday 09.
        DailyMenu::factory()->create(['menu_date' => '2026-10-03'])->items()->attach($rice->id);
        DailyMenu::factory()->create(['menu_date' => '2026-10-09', 'meal_type' => MealType::Dinner]);
        DailyMenu::factory()->create(['menu_date' => '2026-10-10']);

        $this->actingAs($this->admin)
            ->get('/daily-menus?date=2026-10-06')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('daily-menus/index')
                ->where('view', 'week')
                ->where('start', '2026-10-03')
                ->where('end', '2026-10-09')
                ->has('menus', 2)
                ->where('menus.0.items.0.name', 'Plain Rice')
                ->where('previous.lunch.id', $latest->id)
                ->where('previous.lunch.price', '130.00')
                ->where('previous.dinner', null)
                ->has('items', 1));
    }

    public function test_month_chart_covers_whole_weeks(): void
    {
        DailyMenu::factory()->create(['menu_date' => '2026-09-25']);
        DailyMenu::factory()->create(['menu_date' => '2026-09-26']);
        DailyMenu::factory()->create(['menu_date' => '2026-10-31', 'meal_type' => MealType::Dinner]);
        DailyMenu::factory()->create(['menu_date' => '2026-11-06']);
        DailyMenu::factory()->create(['menu_date' => '2026-11-07']);

        $this->actingAs($this->admin)
            ->get('/daily-menus?view=month&date=2026-10-15')
            ->assertInertia(fn (Assert $page) => $page
                ->where('view', 'month')
                ->where('start', '2026-09-26')
                ->where('end', '2026-11-06')
                ->has('menus', 3));
    }

    public function test_copy_last_week_fills_only_the_empty_slots(): void
    {
        [$rice, $curry, $fish] = MenuItem::factory()->count(3)->create();
        $monday = DailyMenu::factory()->create(['menu_date' => '2026-09-28', 'price' => '125', 'notes' => 'Curry day']);
        $monday->items()->attach([$curry->id => ['sort_order' => 1], $rice->id => ['sort_order' => 0]]);
        DailyMenu::factory()->create(['menu_date' => '2026-09-29'])->items()->attach($rice->id);
        // Already planned this week: kept as it is.
        DailyMenu::factory()->create(['menu_date' => '2026-10-06'])->items()->attach($fish->id);

        $this->actingAs($this->admin)
            ->post('/daily-menus/copy-week', ['week' => '2026-10-03'])
            ->assertSessionHasNoErrors();

        $copy = DailyMenu::where('menu_date', '2026-10-05')->sole();
        $this->assertSame([$rice->id, $curry->id], $copy->items->pluck('id')->all());
        $this->assertSame('125.00', $copy->price);
        $this->assertSame('Curry day', $copy->notes);
        $this->assertSame([$fish->id], DailyMenu::where('menu_date', '2026-10-06')->sole()->items->pluck('id')->all());
        $this->assertSame(4, DailyMenu::count());
        $this->assertDatabaseHas('activity_logs', ['module' => 'menu', 'action' => 'week_copied']);

        // Nothing left to copy the second time.
        $this->post('/daily-menus/copy-week', ['week' => '2026-10-03']);
        $this->assertSame(4, DailyMenu::count());
    }

    public function test_menu_pages_require_the_menu_permission(): void
    {
        $item = MenuItem::factory()->create();
        $staff = User::factory()->vendorStaff()->withPermissions(Permission::ManageClients)->create();

        foreach ([$staff, User::factory()->clientAdmin()->create(), User::factory()->rider()->create()] as $user) {
            $this->actingAs($user);

            $this->get('/menu-items')->assertForbidden();
            $this->post('/menu-items', ['name' => 'X', 'category' => 'other', 'default_price' => 1, 'status' => 'active'])->assertForbidden();
            $this->delete("/menu-items/{$item->id}")->assertForbidden();
            $this->get('/daily-menus')->assertForbidden();
            $this->post('/daily-menus', ['menu_date' => '2026-10-06', 'meal_type' => 'lunch', 'items' => [$item->id]])->assertForbidden();
        }
    }

    public function test_client_sees_todays_menu_with_their_own_price(): void
    {
        $item = MenuItem::factory()->create(['name' => 'Chicken Curry']);
        $menu = DailyMenu::factory()->create(['menu_date' => now()->toDateString(), 'price' => '150']);
        $menu->items()->attach($item->id);

        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        $price = new ClientMenuPrice(['meal_type' => 'lunch', 'price' => '120', 'effective_from' => now()->subMonth()->toDateString()]);
        $price->client_id = $clientA->id;
        $price->save();

        $this->actingAs(User::factory()->clientAdmin($clientA)->create())
            ->get('/menu/today')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('menu/today')
                ->has('meals', 1)
                ->where('meals.0.menu.items.0.name', 'Chicken Curry')
                ->where('meals.0.price', '120.00'));

        // Client B has no own price, so the menu price applies.
        $this->actingAs(User::factory()->clientAdmin($clientB)->create())
            ->get('/menu/today')
            ->assertInertia(fn (Assert $page) => $page->where('meals.0.price', '150.00'));
    }

    public function test_staff_without_financial_access_see_the_menu_without_prices(): void
    {
        DailyMenu::factory()->create(['menu_date' => now()->toDateString(), 'price' => '150']);

        $this->actingAs(User::factory()->vendorStaff()->create())
            ->get('/menu/today')
            ->assertInertia(fn (Assert $page) => $page->where('meals.0.price', null));

        $this->actingAs($this->admin)
            ->get('/menu/today')
            ->assertInertia(fn (Assert $page) => $page->where('meals.0.price', '150.00'));
    }

    public function test_riders_cannot_see_the_menu_page(): void
    {
        $this->actingAs(User::factory()->rider()->create())->get('/menu/today')->assertForbidden();
    }
}
