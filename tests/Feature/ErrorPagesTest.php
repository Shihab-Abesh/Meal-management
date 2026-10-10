<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use RuntimeException;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_forbidden_and_missing_pages_are_friendly(): void
    {
        $client = User::factory()->clientAdmin()->create();

        $this->actingAs($client)->get('/clients')
            ->assertForbidden()
            ->assertInertia(fn (Assert $page) => $page->component('error')->where('status', 403));

        $this->actingAs($client)->get('/no-such-page')
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page->component('error')->where('status', 404));
    }

    public function test_server_errors_are_friendly_when_debug_is_off(): void
    {
        Route::middleware('web')->get('/_boom', fn () => throw new RuntimeException('Database exploded'));
        config(['app.debug' => false]);

        $response = $this->get('/_boom');

        $response->assertStatus(500)->assertInertia(fn (Assert $page) => $page->component('error')->where('status', 500));
        $this->assertStringNotContainsString('Database exploded', $response->getContent());
    }

    public function test_an_expired_form_goes_back_with_a_message(): void
    {
        Route::middleware('web')->post('/_expired', fn () => throw new TokenMismatchException);

        $response = $this->from('/profile')->post('/_expired');

        $response->assertRedirect('/profile');
        $this->assertSame('error', $response->inertiaFlash('toast.type'));
        $this->assertStringContainsString('session expired', $response->inertiaFlash('toast.message'));
    }

    public function test_json_requests_keep_plain_errors(): void
    {
        $this->getJson('/no-such-page')->assertNotFound()->assertJsonStructure(['message']);
    }
}
