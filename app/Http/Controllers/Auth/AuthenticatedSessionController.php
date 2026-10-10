<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('auth/login', [
            'canResetPassword' => Route::has('password.request'),
        ]);
    }

    public function store(LoginRequest $request, ActivityLogger $activity): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        $user->recordLogin($request->ip());
        $activity->log('auth', 'login', "{$user->name} signed in", $user);

        return redirect()->intended(route($user->role->homeRoute(), absolute: false));
    }

    public function destroy(Request $request, ActivityLogger $activity): RedirectResponse
    {
        if ($user = $request->user()) {
            $activity->log('auth', 'logout', "{$user->name} signed out", $user);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
