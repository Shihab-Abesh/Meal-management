<?php

namespace Tests\Feature\Clients;

use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClientUserTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->vendorAdmin()->create();
        $this->client = Client::factory()->create();
    }

    public function test_vendor_admin_creates_a_client_login(): void
    {
        $this->actingAs($this->admin)
            ->post("/clients/{$this->client->id}/users", $this->validPayload())
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $user = User::where('email', 'rahim@xyz.test')->firstOrFail();
        $this->assertSame(UserRole::ClientAdmin, $user->role);
        $this->assertSame($this->client->id, $user->client_id);
        $this->assertTrue(Hash::check('Secret123', $user->password));

        $this->assertDatabaseHas('activity_logs', ['module' => 'client_users', 'action' => 'created', 'client_id' => $this->client->id]);
    }

    public function test_role_and_client_cannot_be_chosen_by_the_request(): void
    {
        $other = Client::factory()->create();

        $this->actingAs($this->admin)->post("/clients/{$this->client->id}/users", $this->validPayload([
            'role' => 'vendor_admin',
            'client_id' => $other->id,
            'permissions' => ['settings.manage'],
        ]));

        $user = User::where('email', 'rahim@xyz.test')->firstOrFail();
        $this->assertSame(UserRole::ClientAdmin, $user->role);
        $this->assertSame($this->client->id, $user->client_id);
        $this->assertNull($user->permissions);
    }

    public function test_new_client_login_can_sign_in_and_reach_the_dashboard(): void
    {
        $this->actingAs($this->admin)->post("/clients/{$this->client->id}/users", $this->validPayload());
        $this->post('/logout');

        $this->post('/login', ['login' => '01811-223344', 'password' => 'Secret123'])->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertOk();
    }

    public function test_client_login_validation(): void
    {
        User::factory()->create(['email' => 'taken@example.com', 'phone' => '01999000111']);

        $this->actingAs($this->admin)
            ->post("/clients/{$this->client->id}/users", ['name' => '', 'email' => 'TAKEN@example.com', 'phone' => '01999-000111', 'password' => 'short', 'status' => 'x'])
            ->assertSessionHasErrors(['name', 'email', 'phone', 'password', 'status']);

        $this->post("/clients/{$this->client->id}/users", ['name' => 'No Contact', 'password' => 'Secret123', 'status' => 'active'])
            ->assertSessionHasErrors(['email', 'phone']);
    }

    public function test_client_login_can_be_updated_and_password_reset(): void
    {
        $user = User::factory()->clientAdmin($this->client)->create();

        $this->actingAs($this->admin)
            ->put("/clients/{$this->client->id}/users/{$user->id}", [
                'name' => 'Renamed',
                'email' => $user->email,
                'phone' => $user->phone,
                'password' => 'BrandNew456',
                'status' => 'inactive',
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Renamed', $user->name);
        $this->assertSame(RecordStatus::Inactive, $user->status);
        $this->assertTrue(Hash::check('BrandNew456', $user->password));
    }

    public function test_password_is_kept_when_left_empty(): void
    {
        $user = User::factory()->clientAdmin($this->client)->create();

        $this->actingAs($this->admin)->put("/clients/{$this->client->id}/users/{$user->id}", [
            'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone, 'password' => '', 'status' => 'active',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_client_login_can_be_removed(): void
    {
        $user = User::factory()->clientAdmin($this->client)->create();

        $this->actingAs($this->admin)->delete("/clients/{$this->client->id}/users/{$user->id}")->assertRedirect();

        $this->assertSoftDeleted($user);
    }

    public function test_a_login_from_another_client_cannot_be_changed_through_this_client(): void
    {
        $otherUser = User::factory()->clientAdmin()->create();

        $this->actingAs($this->admin);

        $this->put("/clients/{$this->client->id}/users/{$otherUser->id}", $this->validPayload())->assertNotFound();
        $this->delete("/clients/{$this->client->id}/users/{$otherUser->id}")->assertNotFound();
        // Vendor accounts are not client logins either.
        $this->delete("/clients/{$this->client->id}/users/{$this->admin->id}")->assertNotFound();

        $this->assertNotSoftDeleted($otherUser);
    }

    public function test_staff_need_the_client_users_permission(): void
    {
        $this->actingAs(User::factory()->vendorStaff()->create())
            ->post("/clients/{$this->client->id}/users", $this->validPayload())
            ->assertForbidden();

        $granted = User::factory()->vendorStaff()->withPermissions(Permission::ManageClients, Permission::ManageClientUsers)->create();

        $this->actingAs($granted)
            ->post("/clients/{$this->client->id}/users", $this->validPayload())
            ->assertSessionHasNoErrors();
    }

    public function test_client_admin_cannot_create_logins(): void
    {
        $clientAdmin = User::factory()->clientAdmin($this->client)->create();

        $this->actingAs($clientAdmin)
            ->post("/clients/{$this->client->id}/users", $this->validPayload())
            ->assertForbidden();
    }

    public function test_users_of_an_inactive_client_cannot_sign_in(): void
    {
        $user = User::factory()->clientAdmin(Client::factory()->inactive()->create())->create();

        $this->post('/login', ['login' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['login' => __('auth.inactive')]);

        $this->assertGuest();
    }

    public function test_deactivating_or_archiving_a_client_signs_its_users_out(): void
    {
        $user = User::factory()->clientAdmin($this->client)->create();

        $this->actingAs($user)->get('/dashboard')->assertOk();

        // Each real request loads the user afresh from the session.
        $this->client->update(['status' => RecordStatus::Inactive]);
        $this->actingAs($user->fresh())->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();

        $this->client->update(['status' => RecordStatus::Active]);
        $this->client->delete();
        $this->actingAs($user->fresh())->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_successful_sign_in_is_logged(): void
    {
        $user = User::factory()->clientAdmin($this->client)->create();

        $this->post('/login', ['login' => $user->email, 'password' => 'password']);

        $this->assertDatabaseHas('activity_logs', [
            'module' => 'auth',
            'action' => 'login',
            'user_id' => $user->id,
            'client_id' => $this->client->id,
            'ip_address' => '127.0.0.1',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'name' => 'Rahim Uddin',
            'email' => 'Rahim@XYZ.test',
            'phone' => '01811-223344',
            'password' => 'Secret123',
            'status' => 'active',
            ...$overrides,
        ];
    }
}
