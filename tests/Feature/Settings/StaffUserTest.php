<?php

namespace Tests\Feature\Settings;

use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffUserTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->vendorAdmin()->create(['name' => 'Owner']);
    }

    public function test_list_shows_vendor_accounts_only(): void
    {
        User::factory()->vendorStaff()->create(['name' => 'Kitchen Staff']);
        User::factory()->clientAdmin()->create();
        User::factory()->rider()->create();

        $this->actingAs($this->admin)->get('/users')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('users/index')
                ->has('users.data', 2)
                ->where('users.data.0.name', 'Owner')
                ->where('users.data.0.is_self', true));
    }

    public function test_staff_account_gets_exactly_the_ticked_permissions(): void
    {
        $this->actingAs($this->admin)->post('/users', [
            'name' => 'Accountant',
            'email' => 'Accounts@Example.com',
            'phone' => null,
            'password' => 'secret123',
            'role' => 'vendor_staff',
            'status' => 'active',
            'permissions' => ['billing.manage', 'financials.view', 'reports.view'],
        ])->assertSessionHasNoErrors()->assertRedirect('/users');

        $staff = User::where('email', 'accounts@example.com')->sole();
        $this->assertSame(UserRole::VendorStaff, $staff->role);
        $this->assertEqualsCanonicalizing([Permission::ManageBilling, Permission::ViewFinancials, Permission::ViewReports], $staff->effectivePermissions());

        $this->actingAs($staff)->get('/bills')->assertOk();
        $this->actingAs($staff)->get('/reports')->assertOk();
        $this->actingAs($staff)->get('/clients')->assertForbidden();
        $this->actingAs($staff)->get('/meals')->assertForbidden();
        $this->assertDatabaseHas('activity_logs', ['module' => 'users', 'action' => 'created', 'subject_id' => $staff->id]);
    }

    public function test_operational_permissions_can_be_revoked(): void
    {
        $staff = User::factory()->vendorStaff()->create();
        $this->actingAs($staff)->get('/clients')->assertOk();

        $this->actingAs($this->admin)->put("/users/{$staff->id}", [
            'name' => $staff->name, 'email' => $staff->email, 'phone' => $staff->phone,
            'role' => 'vendor_staff', 'status' => 'active', 'permissions' => ['meals.manage'],
        ])->assertSessionHasNoErrors();

        $this->actingAs($staff->fresh())->get('/clients')->assertForbidden();
        $this->actingAs($staff->fresh())->get('/meals')->assertOk();
    }

    public function test_admins_hold_every_permission_whatever_is_sent(): void
    {
        $this->actingAs($this->admin)->post('/users', [
            'name' => 'Co-owner', 'phone' => '01812345678', 'password' => 'secret123',
            'role' => 'vendor_admin', 'status' => 'active', 'permissions' => ['meals.manage'],
        ])->assertSessionHasNoErrors();

        $coOwner = User::where('phone', '01812345678')->sole();
        $this->assertNull($coOwner->permissions);
        $this->assertSame(Permission::cases(), $coOwner->effectivePermissions());
    }

    public function test_validation(): void
    {
        User::factory()->create(['email' => 'taken@example.com', 'phone' => '01700000099']);

        $this->actingAs($this->admin)->post('/users', [
            'name' => '', 'email' => 'taken@example.com', 'phone' => '0170-0000099', 'password' => 'short',
            'role' => 'client_admin', 'status' => 'gone', 'permissions' => ['everything'],
        ])->assertSessionHasErrors(['name', 'email', 'phone', 'password', 'role', 'status', 'permissions.0']);

        $this->actingAs($this->admin)->post('/users', ['name' => 'No contact', 'password' => 'secret123', 'role' => 'vendor_staff', 'status' => 'active'])
            ->assertSessionHasErrors(['email', 'phone']);
    }

    public function test_nobody_can_lock_themselves_out_and_one_admin_always_remains(): void
    {
        $payload = ['name' => 'Owner', 'email' => $this->admin->email, 'phone' => $this->admin->phone, 'status' => 'active', 'permissions' => []];

        $this->actingAs($this->admin)->put("/users/{$this->admin->id}", [...$payload, 'role' => 'vendor_staff'])->assertSessionHasErrors('role');
        $this->actingAs($this->admin)->patch("/users/{$this->admin->id}/status", ['status' => 'inactive'])->assertSessionHasErrors('role');

        $this->assertSame(UserRole::VendorAdmin, $this->admin->fresh()->role);
        $this->assertTrue($this->admin->fresh()->isActive());

        // Another admin may deactivate them, since an active admin remains.
        $second = User::factory()->vendorAdmin()->create();
        $this->actingAs($second)->patch("/users/{$this->admin->id}/status", ['status' => 'inactive'])->assertSessionHasNoErrors();
        $this->assertSame(RecordStatus::Inactive, $this->admin->fresh()->status);
    }

    public function test_the_last_active_admin_cannot_be_deactivated_by_another_user_manager(): void
    {
        $manager = User::factory()->vendorStaff()->withPermissions(Permission::ManageUsers)->create();

        $this->actingAs($manager)->patch("/users/{$this->admin->id}/status", ['status' => 'inactive'])->assertSessionHasErrors('role');
        $this->actingAs($manager)->put("/users/{$this->admin->id}", [
            'name' => 'Owner', 'email' => $this->admin->email, 'phone' => $this->admin->phone,
            'role' => 'vendor_staff', 'status' => 'active', 'permissions' => [],
        ])->assertSessionHasErrors('role');

        $this->assertSame(UserRole::VendorAdmin, $this->admin->fresh()->role);
        $this->assertTrue($this->admin->fresh()->isActive());
    }

    public function test_deactivated_users_are_signed_out(): void
    {
        $staff = User::factory()->vendorStaff()->create();

        $this->actingAs($this->admin)->patch("/users/{$staff->id}/status", ['status' => 'inactive'])->assertSessionHasNoErrors();

        $this->actingAs($staff->fresh())->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_password_reset_by_an_admin(): void
    {
        $staff = User::factory()->vendorStaff()->create();

        $this->actingAs($this->admin)->put("/users/{$staff->id}", [
            'name' => $staff->name, 'email' => $staff->email, 'phone' => $staff->phone,
            'password' => 'newpass123', 'role' => 'vendor_staff', 'status' => 'active',
            'permissions' => array_map(fn (Permission $p) => $p->value, Permission::operational()),
        ])->assertSessionHasNoErrors();

        $this->assertTrue(password_verify('newpass123', $staff->fresh()->password));
        $this->assertDatabaseHas('activity_logs', ['module' => 'users', 'subject_id' => $staff->id]);
    }

    public function test_access_requires_the_users_permission(): void
    {
        $client = User::factory()->clientAdmin()->create();

        foreach ([User::factory()->vendorStaff()->create(), $client, User::factory()->rider()->create()] as $user) {
            $this->actingAs($user)->get('/users')->assertForbidden();
            $this->actingAs($user)->post('/users', [])->assertForbidden();
        }

        // Client and rider logins are not edited here.
        $this->actingAs($this->admin)->get("/users/{$client->id}/edit")->assertNotFound();
    }
}
