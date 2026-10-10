<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\ClientUserRequest;
use App\Models\Client;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * Login accounts for a client organization. Routes use scoped bindings, so
 * {user} always belongs to {client}.
 */
class ClientUserController extends Controller
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function store(ClientUserRequest $request, Client $client): RedirectResponse
    {
        $user = DB::transaction(function () use ($request, $client) {
            $user = new User($request->safe()->only(['name', 'email', 'phone', 'password']));
            $user->forceFill([
                'role' => UserRole::ClientAdmin,
                'client_id' => $client->id,
                'status' => $request->validated('status'),
            ])->save();

            $this->activity->log('client_users', 'created', "Created login for {$user->name} ({$client->name})", $user);

            return $user;
        });

        $this->toast("Login created for {$user->name}.");

        return back();
    }

    public function update(ClientUserRequest $request, Client $client, User $user): RedirectResponse
    {
        DB::transaction(function () use ($request, $client, $user) {
            $user->fill($request->safe()->only(['name', 'email', 'phone']));
            $user->status = $request->validated('status');

            if (filled($request->validated('password'))) {
                $user->password = $request->validated('password');
            }

            $user->save();

            $changes = $this->activity->changes($user, ['updated_at', 'password', 'remember_token']);

            if ($changes || $user->wasChanged('password')) {
                $this->activity->log(
                    'client_users',
                    'updated',
                    "Updated login for {$user->name} ({$client->name})",
                    $user,
                    [...$changes, 'password_changed' => $user->wasChanged('password')],
                );
            }
        });

        $this->toast("{$user->name}'s login has been updated.");

        return back();
    }

    public function destroy(Client $client, User $user): RedirectResponse
    {
        $this->authorize('manageUsers', $client);

        DB::transaction(function () use ($client, $user) {
            $user->delete();

            $this->activity->log('client_users', 'deleted', "Removed login for {$user->name} ({$client->name})", $user);
        });

        $this->toast("{$user->name}'s login has been removed.");

        return back();
    }
}
