<?php

namespace Tests\Feature\Settings;

use App\Enums\MealType;
use App\Enums\Permission;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Setting;
use App\Models\User;
use App\Services\MealCutoff;
use App\Services\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->vendorAdmin()->create();
    }

    public function test_settings_page_shows_current_values(): void
    {
        $this->actingAs($this->admin)->get('/settings')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('settings/edit')
                ->where('values.lunch_cutoff', '10:00')
                ->where('values.dinner_cutoff', '16:00')
                ->where('values.lunch_price', '120.00')
                ->where('values.due_days', 15)
                ->where('values.bill_issued', true)
                ->where('values.order_delivered', false)
                ->where('can.pricing', true)
                ->where('mailConfigured', false));
    }

    public function test_each_section_saves_its_own_values_and_is_logged(): void
    {
        $this->save(['section' => 'meals', 'lunch_cutoff' => '09:30', 'dinner_cutoff' => '15:45'])->assertSessionHasNoErrors();
        $this->save(['section' => 'pricing', 'lunch_price' => '125.5', 'dinner_price' => '140'])->assertSessionHasNoErrors();
        $this->save(['section' => 'billing', 'due_days' => 7])->assertSessionHasNoErrors();
        $this->save(['section' => 'notifications', 'bill_issued' => false, 'payment_received' => true, 'order_delivered' => true])->assertSessionHasNoErrors();

        $stored = Setting::query()->pluck('value', 'key');
        $this->assertSame('09:30', $stored['meals.lunch_cutoff']);
        $this->assertSame('125.50', $stored['meals.lunch_price']);
        $this->assertSame(7, $stored['billing.due_days']);
        $this->assertFalse($stored['notifications.bill_issued']);
        $this->assertTrue($stored['notifications.order_delivered']);
        $this->assertSame(4, ActivityLog::where('module', 'settings')->count());

        // The defaults are what the rest of the app now uses.
        $settings = app(Settings::class);
        $client = Client::factory()->create(['lunch_cutoff_time' => null]);
        $this->assertSame('09:30', (new MealCutoff($settings))->time($client, MealType::Lunch));
        $this->assertSame(7, $settings->billDueDays());
    }

    public function test_validation(): void
    {
        $this->save(['section' => 'meals', 'lunch_cutoff' => '25:00', 'dinner_cutoff' => ''])->assertSessionHasErrors(['lunch_cutoff', 'dinner_cutoff']);
        $this->save(['section' => 'pricing', 'lunch_price' => '-1', 'dinner_price' => '1.234'])->assertSessionHasErrors(['lunch_price', 'dinner_price']);
        $this->save(['section' => 'billing', 'due_days' => 500])->assertSessionHasErrors('due_days');
        $this->save(['section' => 'secrets'])->assertSessionHasErrors('section');

        $this->assertSame(0, Setting::count());
    }

    public function test_settings_need_the_settings_permission(): void
    {
        $staff = User::factory()->vendorStaff()->create();
        $this->actingAs($staff)->get('/settings')->assertForbidden();
        $this->actingAs($staff)->put('/settings', ['section' => 'billing', 'due_days' => 1])->assertForbidden();

        foreach ([User::factory()->clientAdmin()->create(), User::factory()->rider()->create()] as $user) {
            $this->actingAs($user)->get('/settings')->assertForbidden();
        }

        // Settings access alone does not include changing prices.
        $settingsOnly = User::factory()->vendorStaff()->withPermissions(Permission::ManageSettings)->create();
        $this->actingAs($settingsOnly)->get('/settings')->assertOk()->assertInertia(fn ($page) => $page->where('can.pricing', false));
        $this->actingAs($settingsOnly)->put('/settings', ['section' => 'pricing', 'lunch_price' => '1', 'dinner_price' => '1'])->assertForbidden();
        $this->actingAs($settingsOnly)->put('/settings', ['section' => 'billing', 'due_days' => 10])->assertSessionHasNoErrors();
    }

    private function save(array $data)
    {
        return $this->actingAs($this->admin)->put('/settings', $data);
    }
}
