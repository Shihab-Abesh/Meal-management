<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VendorProfileTest extends TestCase
{
    use RefreshDatabase;

    /** A real 1x1 PNG, so tests do not need the GD extension. */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    public function test_vendor_admin_can_view_the_profile(): void
    {
        $profile = VendorProfile::factory()->create();

        $this->actingAs(User::factory()->vendorAdmin()->create())
            ->get('/vendor-profile')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('vendor-profile/show')
                ->where('profile.business_name', $profile->business_name)
                ->where('profile.status', 'active')
                ->where('canManage', true));
    }

    public function test_profile_page_works_before_the_profile_exists(): void
    {
        $this->actingAs(User::factory()->vendorAdmin()->create())
            ->get('/vendor-profile')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('profile', null)->where('canManage', true));
    }

    public function test_vendor_staff_can_view_but_not_edit_by_default(): void
    {
        VendorProfile::factory()->create();
        $this->actingAs(User::factory()->vendorStaff()->create());

        $this->get('/vendor-profile')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('canManage', false));

        $this->get('/vendor-profile/edit')->assertForbidden();
        $this->put('/vendor-profile', $this->validPayload())->assertForbidden();
    }

    public function test_vendor_staff_with_explicit_permission_can_edit(): void
    {
        $staff = User::factory()->vendorStaff()->withPermissions(Permission::ManageVendorProfile)->create();

        $this->actingAs($staff)->get('/vendor-profile/edit')->assertOk();
        $this->put('/vendor-profile', $this->validPayload())->assertRedirect('/vendor-profile');

        $this->assertDatabaseHas('vendor_profiles', ['business_name' => 'ABC Catering']);
    }

    #[DataProvider('nonVendorRoles')]
    public function test_client_and_rider_accounts_cannot_access_the_vendor_profile(string $state): void
    {
        VendorProfile::factory()->create();
        $this->actingAs(User::factory()->{$state}()->create());

        $this->get('/vendor-profile')->assertForbidden();
        $this->get('/vendor-profile/edit')->assertForbidden();
        $this->put('/vendor-profile', $this->validPayload())->assertForbidden();
    }

    public static function nonVendorRoles(): array
    {
        return [
            'client admin' => ['clientAdmin'],
            'rider' => ['rider'],
        ];
    }

    public function test_edit_page_provides_form_options(): void
    {
        $this->actingAs(User::factory()->vendorAdmin()->create())
            ->get('/vendor-profile/edit')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('vendor-profile/edit')
                ->where('businessTypes', config('catering.business_types'))
                ->has('statuses', 2)
                ->where('logoRules.max_kb', config('catering.logo.max_kb')));
    }

    public function test_vendor_admin_can_update_the_profile(): void
    {
        VendorProfile::factory()->create();

        $this->actingAs(User::factory()->vendorAdmin()->create())
            ->put('/vendor-profile', $this->validPayload(['vat_tin' => 'TIN-123/45']))
            ->assertSessionHasNoErrors()
            ->assertRedirect('/vendor-profile')
            ->assertInertiaFlash('toast.message', 'Vendor profile saved.');

        $this->assertDatabaseHas('vendor_profiles', [
            'business_name' => 'ABC Catering',
            'owner_name' => 'Abdul Karim',
            'city' => 'Dhaka',
            'vat_tin' => 'TIN-123/45',
        ]);
    }

    public function test_saving_creates_the_profile_once_and_keeps_a_single_row(): void
    {
        $this->actingAs(User::factory()->vendorAdmin()->create());

        $this->put('/vendor-profile', $this->validPayload());
        $this->put('/vendor-profile', $this->validPayload(['business_name' => 'ABC Catering & Events']));

        $this->assertSame(1, VendorProfile::count());
        $this->assertSame('ABC Catering & Events', VendorProfile::first()->business_name);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->actingAs(User::factory()->vendorAdmin()->create())
            ->put('/vendor-profile', [])
            ->assertSessionHasErrors(['business_name', 'owner_name', 'phone', 'email', 'address', 'city', 'status']);

        $this->assertSame(0, VendorProfile::count());
    }

    public function test_invalid_values_are_rejected(): void
    {
        $this->actingAs(User::factory()->vendorAdmin()->create())
            ->put('/vendor-profile', $this->validPayload([
                'email' => 'not-an-email',
                'phone' => 'call me',
                'status' => 'deleted',
                'vat_tin' => '<script>',
                'business_name' => str_repeat('a', 151),
            ]))
            ->assertSessionHasErrors(['email', 'phone', 'status', 'vat_tin', 'business_name']);
    }

    public function test_logo_can_be_uploaded_and_replacing_it_removes_the_old_file(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->vendorAdmin()->create());

        // The browser sends uploads as POST with _method=PUT.
        $this->post('/vendor-profile', $this->validPayload(['_method' => 'PUT', 'logo' => $this->png('first.png')]))
            ->assertSessionHasNoErrors();

        $firstPath = VendorProfile::first()->logo_path;
        Storage::disk('public')->assertExists($firstPath);
        $this->assertStringStartsWith('vendor/', $firstPath);

        $this->post('/vendor-profile', $this->validPayload(['_method' => 'PUT', 'logo' => $this->png('second.png')]))
            ->assertSessionHasNoErrors();

        $secondPath = VendorProfile::first()->logo_path;
        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('public')->assertExists($secondPath);
        Storage::disk('public')->assertMissing($firstPath);
    }

    public function test_saving_without_a_new_logo_keeps_the_current_one(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->vendorAdmin()->create());

        $this->post('/vendor-profile', $this->validPayload(['_method' => 'PUT', 'logo' => $this->png()]));
        $path = VendorProfile::first()->logo_path;

        $this->put('/vendor-profile', $this->validPayload(['city' => 'Chattogram']));

        $this->assertSame($path, VendorProfile::first()->logo_path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_logo_can_be_removed(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->vendorAdmin()->create());

        $this->post('/vendor-profile', $this->validPayload(['_method' => 'PUT', 'logo' => $this->png()]));
        $path = VendorProfile::first()->logo_path;

        $this->put('/vendor-profile', $this->validPayload(['remove_logo' => true]))->assertSessionHasNoErrors();

        $this->assertNull(VendorProfile::first()->logo_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_logo_must_be_an_allowed_image_type(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->vendorAdmin()->create());

        foreach (['logo.pdf' => 'application/pdf', 'logo.svg' => 'image/svg+xml', 'logo.php' => 'text/x-php'] as $name => $mime) {
            $this->post('/vendor-profile', $this->validPayload([
                '_method' => 'PUT',
                'logo' => UploadedFile::fake()->create($name, 10, $mime),
            ]))->assertSessionHasErrors('logo');
        }

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_logo_size_is_limited(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->vendorAdmin()->create())
            ->post('/vendor-profile', $this->validPayload([
                '_method' => 'PUT',
                'logo' => UploadedFile::fake()->create('logo.png', config('catering.logo.max_kb') + 1, 'image/png'),
            ]))
            ->assertSessionHasErrors('logo');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'business_name' => 'ABC Catering',
            'organization_name' => 'ABC Catering Ltd.',
            'owner_name' => 'Abdul Karim',
            'phone' => '+880 1712-345678',
            'email' => 'info@abccatering.test',
            'address' => 'House 12, Road 5, Dhanmondi',
            'city' => 'Dhaka',
            'business_type' => 'Catering Service',
            'vat_tin' => null,
            'status' => 'active',
            ...$overrides,
        ];
    }

    private function png(string $name = 'logo.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(self::PNG));
    }
}
