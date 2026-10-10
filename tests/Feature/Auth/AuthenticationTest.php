<?php

namespace Tests\Feature\Auth;

use App\Enums\RecordStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/login')
                ->where('canResetPassword', true));
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/vendor-profile')->assertRedirect('/login');
    }

    public function test_users_can_login_with_email(): void
    {
        $user = User::factory()->vendorAdmin()->create(['email' => 'admin@catering.test']);

        $response = $this->post('/login', ['login' => 'Admin@Catering.test', 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_login_with_phone_number_in_any_format(): void
    {
        $user = User::factory()->rider()->create(['phone' => '01712-345678']);

        $this->assertSame('01712345678', $user->phone);

        $this->post('/login', ['login' => '017 1234 5678', 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_records_time_and_ip(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['login' => $user->email, 'password' => 'password']);

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
        $this->assertSame('127.0.0.1', $user->last_login_ip);
    }

    public function test_users_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['login' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['login' => __('auth.failed')]);

        $this->assertGuest();
    }

    /**
     * Regression: a non-phone identifier normalizes to null, which Eloquent
     * would turn into "WHERE phone IS NULL" and match email-only accounts.
     */
    public function test_identifier_without_digits_cannot_match_accounts_without_a_phone(): void
    {
        User::factory()->create(['phone' => null]);

        $this->post('/login', ['login' => 'not-a-phone', 'password' => 'password'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_login_requires_identifier_and_password(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['login', 'password']);

        $this->assertGuest();
    }

    public function test_inactive_users_cannot_login(): void
    {
        $user = User::factory()->inactive()->create();

        $this->post('/login', ['login' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['login' => __('auth.inactive')]);

        $this->assertGuest();
    }

    public function test_deleted_users_cannot_login(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $this->post('/login', ['login' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post('/login', ['login' => $user->email, 'password' => 'wrong-password']);
        }

        // Even the correct password is refused while locked out.
        $this->post('/login', ['login' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
        $this->assertStringContainsString('Too many login attempts', session('errors')->first('login'));
    }

    public function test_signed_in_users_are_redirected_away_from_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/login')
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_logout(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_user_deactivated_mid_session_is_signed_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertOk();

        $user->forceFill(['status' => RecordStatus::Inactive])->save();

        $this->get('/dashboard')
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['login' => __('auth.inactive')]);

        $this->assertGuest();
    }
}
