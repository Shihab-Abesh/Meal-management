<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The signed-in user's own account (name, contact details, password).
 */
class ProfileController extends Controller
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('profile/edit', [
            'account' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role_label' => $user->role->label(),
                'last_login_at' => $user->last_login_at?->toIso8601String(),
                'created_at' => $user->created_at?->toIso8601String(),
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        if ($changes = $this->activity->changes($user)) {
            $this->activity->log('profile', 'updated', "{$user->name} updated their profile", $user, $changes);
        }

        $this->toast('Your profile has been updated.');

        return to_route('profile.edit');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->update(['password' => $request->validated('password')]);

        $this->activity->log('profile', 'password_changed', "{$user->name} changed their password", $user);

        $this->toast('Your password has been changed.');

        return to_route('profile.edit');
    }
}
