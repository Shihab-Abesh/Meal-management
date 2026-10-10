<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_shows_the_users_own_details(): void
    {
        $user = User::factory()->clientAdmin()->create(['name' => 'Rahim Uddin']);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('profile/edit')
                ->where('account.name', 'Rahim Uddin')
                ->where('account.role_label', 'Client Admin')
                ->missing('account.password'));
    }

    public function test_user_can_update_their_details(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', ['name' => 'New Name', 'email' => 'NEW@Example.com', 'phone' => '01811-223344'])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile')
            ->assertInertiaFlash('toast.message', 'Your profile has been updated.');

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertSame('01811223344', $user->phone);
    }

    public function test_user_can_keep_their_own_email_and_phone(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', ['name' => 'Same Contacts', 'email' => $user->email, 'phone' => $user->phone])
            ->assertSessionHasNoErrors();
    }

    public function test_email_and_phone_must_be_unique(): void
    {
        User::factory()->create(['email' => 'taken@example.com', 'phone' => '01711111111']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', ['name' => 'X', 'email' => 'TAKEN@example.com', 'phone' => '01711-111111'])
            ->assertSessionHasErrors(['email', 'phone']);
    }

    public function test_either_email_or_phone_is_required(): void
    {
        $this->actingAs(User::factory()->create())
            ->put('/profile', ['name' => 'X', 'email' => '', 'phone' => ''])
            ->assertSessionHasErrors(['email', 'phone']);
    }

    public function test_phone_only_account_is_allowed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', ['name' => 'Phone Only', 'email' => '', 'phone' => '01999888777'])
            ->assertSessionHasNoErrors();

        $this->assertNull($user->fresh()->email);
    }

    public function test_role_and_status_cannot_be_changed_through_the_profile_form(): void
    {
        $user = User::factory()->clientAdmin()->create();

        $this->actingAs($user)->put('/profile', [
            'name' => 'Sneaky',
            'email' => $user->email,
            'role' => 'vendor_admin',
            'status' => 'inactive',
            'permissions' => ['vendor_profile.manage'],
        ]);

        $user->refresh();
        $this->assertSame(UserRole::ClientAdmin, $user->role);
        $this->assertTrue($user->isActive());
        $this->assertNull($user->permissions);
    }

    public function test_password_can_be_changed_with_the_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'password',
                'password' => 'BetterPass123',
                'password_confirmation' => 'BetterPass123',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('BetterPass123', $user->fresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'wrong-password',
                'password' => 'BetterPass123',
                'password_confirmation' => 'BetterPass123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_new_password_must_meet_the_rules(): void
    {
        $this->actingAs(User::factory()->create())
            ->put('/profile/password', [
                'current_password' => 'password',
                'password' => 'onlyletters',
                'password_confirmation' => 'onlyletters',
            ])
            ->assertSessionHasErrors('password');
    }
}
