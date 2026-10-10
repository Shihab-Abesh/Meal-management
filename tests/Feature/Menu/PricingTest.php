<?php

namespace Tests\Feature\Menu;

use App\Enums\MealType;
use App\Enums\Permission;
use App\Models\Client;
use App\Models\ClientMenuPrice;
use App\Models\DailyMenu;
use App\Models\User;
use App\Services\PricingService;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PricingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->vendorAdmin()->create();
    }

    public function test_default_price_applies_without_client_or_menu_price(): void
    {
        $client = Client::factory()->create();

        $this->assertSame(
            ['price' => '120.00', 'source' => 'default'],
            app(PricingService::class)->priceFor($client, MealType::Lunch, '2026-10-06'),
        );
    }

    public function test_menu_price_overrides_the_default(): void
    {
        $client = Client::factory()->create();
        DailyMenu::factory()->create(['menu_date' => '2026-10-06', 'meal_type' => MealType::Lunch, 'price' => '150']);

        $pricing = app(PricingService::class);

        $this->assertSame(['price' => '150.00', 'source' => 'menu'], $pricing->priceFor($client, MealType::Lunch, '2026-10-06'));
        // Only that date and meal.
        $this->assertSame('default', $pricing->priceFor($client, MealType::Lunch, '2026-10-07')['source']);
        $this->assertSame('default', $pricing->priceFor($client, MealType::Dinner, '2026-10-06')['source']);
    }

    public function test_each_client_gets_its_own_price(): void
    {
        [$a, $b, $c] = Client::factory()->count(3)->create();
        $this->setPrice($a, 'lunch', '120', '2026-01-01');
        $this->setPrice($b, 'lunch', '130', '2026-01-01');
        $this->setPrice($c, 'lunch', '150', '2026-01-01');
        // A menu price never overrides a client's own price.
        DailyMenu::factory()->create(['menu_date' => '2026-10-06', 'meal_type' => MealType::Lunch, 'price' => '999']);

        $pricing = app(PricingService::class);

        $this->assertSame('120.00', $pricing->priceFor($a, MealType::Lunch, '2026-10-06')['price']);
        $this->assertSame('130.00', $pricing->priceFor($b, MealType::Lunch, '2026-10-06')['price']);
        $this->assertSame('150.00', $pricing->priceFor($c, MealType::Lunch, '2026-10-06')['price']);
    }

    public function test_price_changes_apply_from_their_effective_date_only(): void
    {
        $client = Client::factory()->create();
        $this->setPrice($client, 'lunch', '120', '2026-09-01');
        $this->setPrice($client, 'lunch', '135', '2026-10-15');

        $pricing = app(PricingService::class);

        $this->assertSame('default', $pricing->priceFor($client, MealType::Lunch, '2026-08-31')['source']);
        $this->assertSame('120.00', $pricing->priceFor($client, MealType::Lunch, '2026-09-01')['price']);
        $this->assertSame('120.00', $pricing->priceFor($client, MealType::Lunch, '2026-10-14')['price']);
        $this->assertSame('135.00', $pricing->priceFor($client, MealType::Lunch, '2026-10-15')['price']);
        $this->assertSame('135.00', $pricing->priceFor($client, MealType::Lunch, '2027-01-01')['price']);
    }

    public function test_money_arithmetic_is_exact(): void
    {
        $this->assertSame('2640.00', Money::multiply('120.00', 22));
        $this->assertSame('144000.00', Money::multiply('120', 1200));
        $this->assertSame('0.30', Money::add('0.10', '0.20'));
        $this->assertSame('10.01', Money::subtract('100.11', '90.10'));
    }

    public function test_vendor_admin_sets_a_client_price_and_it_is_logged(): void
    {
        $client = Client::factory()->create();

        $this->actingAs($this->admin)
            ->post("/pricing/clients/{$client->id}", ['meal_type' => 'lunch', 'price' => '125.50', 'effective_from' => '2026-10-01'])
            ->assertSessionHasNoErrors();

        $price = ClientMenuPrice::firstOrFail();
        $this->assertSame('125.50', $price->price);
        $this->assertSame('2026-10-01', $price->effective_from->toDateString());
        $this->assertDatabaseHas('activity_logs', ['module' => 'pricing', 'action' => 'client_price_set']);
    }

    public function test_setting_a_price_for_the_same_day_replaces_it(): void
    {
        $client = Client::factory()->create();
        $this->actingAs($this->admin);

        $this->post("/pricing/clients/{$client->id}", ['meal_type' => 'lunch', 'price' => '125', 'effective_from' => '2026-10-01']);
        $this->post("/pricing/clients/{$client->id}", ['meal_type' => 'lunch', 'price' => '128', 'effective_from' => '2026-10-01']);

        $this->assertSame(1, ClientMenuPrice::count());
        $this->assertSame('128.00', ClientMenuPrice::firstOrFail()->price);
    }

    public function test_price_validation(): void
    {
        $client = Client::factory()->create();

        $this->actingAs($this->admin)
            ->post("/pricing/clients/{$client->id}", ['meal_type' => 'breakfast', 'price' => '-5', 'effective_from' => '01/10/2026'])
            ->assertSessionHasErrors(['meal_type', 'price', 'effective_from']);

        $this->post("/pricing/clients/{$client->id}", ['meal_type' => 'lunch', 'price' => '12.345', 'effective_from' => '2026-10-01'])
            ->assertSessionHasErrors('price');
    }

    public function test_only_scheduled_prices_can_be_removed(): void
    {
        $client = Client::factory()->create();
        $past = $this->setPrice($client, 'lunch', '120', now()->subDay()->toDateString());
        $future = $this->setPrice($client, 'lunch', '140', now()->addWeek()->toDateString());

        $this->actingAs($this->admin);

        $this->delete("/pricing/clients/{$client->id}/prices/{$past->id}")->assertSessionHasErrors('price');
        $this->assertModelExists($past);

        $this->delete("/pricing/clients/{$client->id}/prices/{$future->id}")->assertSessionHasNoErrors();
        $this->assertModelMissing($future);
    }

    public function test_a_price_of_another_client_cannot_be_removed_through_this_client(): void
    {
        $client = Client::factory()->create();
        $other = Client::factory()->create();
        $price = $this->setPrice($other, 'lunch', '140', now()->addWeek()->toDateString());

        $this->actingAs($this->admin)->delete("/pricing/clients/{$client->id}/prices/{$price->id}")->assertNotFound();
        $this->assertModelExists($price);
    }

    public function test_default_prices_can_be_changed(): void
    {
        $this->actingAs($this->admin)
            ->put('/pricing/defaults', ['lunch_price' => '110', 'dinner_price' => '125.5'])
            ->assertSessionHasNoErrors();

        $client = Client::factory()->create();
        $pricing = app(PricingService::class);

        $this->assertSame('110.00', $pricing->priceFor($client, MealType::Lunch, '2026-10-06')['price']);
        $this->assertSame('125.50', $pricing->priceFor($client, MealType::Dinner, '2026-10-06')['price']);
        $this->assertDatabaseHas('activity_logs', ['module' => 'settings', 'action' => 'updated']);
    }

    public function test_pricing_overview_shows_current_prices(): void
    {
        $client = Client::factory()->withDinner()->create(['name' => 'XYZ']);
        $this->setPrice($client, 'lunch', '120', now()->subMonth()->toDateString());

        $this->actingAs($this->admin)
            ->get('/pricing')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('pricing/index')
                ->where('defaults.lunch', '120.00')
                ->where('clients.0.prices.lunch', ['price' => '120.00', 'custom' => true])
                ->where('clients.0.prices.dinner', ['price' => '120.00', 'custom' => false]));
    }

    public function test_pricing_requires_the_pricing_permission(): void
    {
        $client = Client::factory()->create();

        foreach ([User::factory()->vendorStaff()->create(), User::factory()->clientAdmin($client)->create(), User::factory()->rider()->create()] as $user) {
            $this->actingAs($user);

            $this->get('/pricing')->assertForbidden();
            $this->get("/pricing/clients/{$client->id}")->assertForbidden();
            $this->post("/pricing/clients/{$client->id}", ['meal_type' => 'lunch', 'price' => '1', 'effective_from' => '2026-10-01'])->assertForbidden();
            $this->put('/pricing/defaults', ['lunch_price' => '1', 'dinner_price' => '1'])->assertForbidden();
        }

        $this->assertSame(0, ClientMenuPrice::count());

        $granted = User::factory()->vendorStaff()->withPermissions(Permission::ManagePricing)->create();
        $this->actingAs($granted)->get('/pricing')->assertOk();
    }

    private function setPrice(Client $client, string $type, string $price, string $from): ClientMenuPrice
    {
        $record = new ClientMenuPrice(['meal_type' => $type, 'price' => $price, 'effective_from' => $from]);
        $record->client_id = $client->id;
        $record->save();

        return $record;
    }
}
