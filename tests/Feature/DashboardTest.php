<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_admin_sees_all_vendor_cards_including_revenue(): void
    {
        $response = $this->actingAs(User::factory()->vendorAdmin()->create())->get('/dashboard');

        $response->assertOk()->assertInertia(fn (Assert $page) => $page->component('dashboard')->has('today'));

        $this->assertSame(
            ['total_clients', 'total_employees', 'todays_meals', 'todays_revenue', 'pending_deliveries'],
            $this->statKeys($response),
        );
    }

    public function test_vendor_staff_does_not_receive_revenue_by_default(): void
    {
        $response = $this->actingAs(User::factory()->vendorStaff()->create())->get('/dashboard');

        $this->assertNotContains('todays_revenue', $this->statKeys($response));
        $this->assertContains('total_clients', $this->statKeys($response));
    }

    public function test_vendor_staff_with_financial_permission_receives_revenue(): void
    {
        $staff = User::factory()->vendorStaff()->withPermissions(Permission::ViewFinancials)->create();

        $this->assertContains('todays_revenue', $this->statKeys($this->actingAs($staff)->get('/dashboard')));
    }

    public function test_client_admin_only_receives_client_cards(): void
    {
        $response = $this->actingAs(User::factory()->clientAdmin()->create())->get('/dashboard');

        // Their own balance, never the vendor's revenue.
        $this->assertSame(['total_employees', 'todays_meals', 'monthly_meals', 'balance_due'], $this->statKeys($response));
    }

    public function test_rider_never_receives_financial_or_client_cards(): void
    {
        // Even a stored grant must not leak revenue to a rider.
        $rider = User::factory()->rider()->withPermissions(Permission::ViewFinancials)->create();

        $response = $this->actingAs($rider)->get('/dashboard');

        $this->assertSame(['todays_deliveries', 'pending_deliveries', 'completed_deliveries'], $this->statKeys($response));
    }

    /**
     * @return list<string>
     */
    private function statKeys(TestResponse $response): array
    {
        return array_column($response->inertiaProps('stats'), 'key');
    }
}
