<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Props shared with every page: branding, the signed-in user and navigation.
 */
class SharedDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_in_user_payload_contains_no_sensitive_fields(): void
    {
        $user = User::factory()->vendorStaff()->withPermissions(Permission::ViewFinancials)->create();

        $shared = $this->actingAs($user)->get('/dashboard')->inertiaProps('auth.user');

        $this->assertSame(
            ['id', 'name', 'email', 'phone', 'role', 'role_label', 'is_vendor', 'permissions'],
            array_keys($shared),
        );
        $this->assertSame(['financials.view'], $shared['permissions']);
    }

    public function test_branding_comes_from_the_vendor_profile(): void
    {
        VendorProfile::factory()->create(['business_name' => 'Spice Route Catering']);

        $this->get('/login')->assertInertia(fn (Assert $page) => $page
            ->where('app.name', 'Spice Route Catering')
            ->where('app.logo_url', null)
            ->where('app.currency', config('catering.currency')));
    }

    public function test_branding_falls_back_to_the_app_name(): void
    {
        $this->get('/login')->assertInertia(fn (Assert $page) => $page->where('app.name', config('app.name')));
    }

    public function test_vendor_users_get_vendor_menu_items(): void
    {
        $labels = $this->navigationLabels($this->actingAs(User::factory()->vendorStaff()->create())->get('/dashboard'));

        $this->assertContains('Clients', $labels);
        $this->assertContains('Vendor Profile', $labels);
    }

    public function test_menu_items_follow_staff_permissions(): void
    {
        $staff = User::factory()->vendorStaff()->withPermissions(Permission::ViewReports)->create();

        $labels = $this->navigationLabels($this->actingAs($staff)->get('/dashboard'));

        $this->assertNotContains('Clients', $labels);
    }

    public function test_client_and_rider_menus_hide_vendor_items(): void
    {
        foreach ([User::factory()->clientAdmin()->create(), User::factory()->rider()->create()] as $user) {
            $labels = $this->navigationLabels($this->actingAs($user)->get('/dashboard'));

            $this->assertNotContains('Clients', $labels);
            $this->assertNotContains('Vendor Profile', $labels);
        }

        $clientLabels = $this->navigationLabels($this->actingAs(User::factory()->clientAdmin()->create())->get('/dashboard'));
        $this->assertContains('Employees', $clientLabels);

        $riderLabels = $this->navigationLabels($this->actingAs(User::factory()->rider()->create())->get('/dashboard'));
        $this->assertNotContains('Employees', $riderLabels);
    }

    public function test_current_page_is_marked_active(): void
    {
        $navigation = $this->actingAs(User::factory()->vendorAdmin()->create())
            ->get('/vendor-profile')
            ->inertiaProps('navigation');

        $items = array_merge(...array_column($navigation, 'items'));
        $active = array_column(array_filter($items, fn (array $item) => $item['active']), 'label');

        $this->assertSame(['Vendor Profile'], array_values($active));
    }

    /**
     * @return list<string>
     */
    private function navigationLabels(TestResponse $response): array
    {
        $items = array_merge(...array_column($response->inertiaProps('navigation'), 'items'));

        return array_column($items, 'label');
    }
}
