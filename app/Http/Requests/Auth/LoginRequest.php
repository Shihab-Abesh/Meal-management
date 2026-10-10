<?php

namespace App\Http\Requests\Auth;

use App\Support\Phone;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Failed attempts allowed per login + IP before the user is locked out.
     */
    private const MAX_ATTEMPTS = 5;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['login' => 'email or phone'];
    }

    /**
     * Attempt to sign in with an email address or phone number.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $identifier = $this->identifier();

        $credentials = [
            ...$identifier,
            'password' => $this->string('password')->value(),
        ];

        // An empty identifier would become "WHERE phone IS NULL" and match
        // email-only accounts, so it must never reach the user provider.
        if (blank(reset($identifier)) || ! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => __('auth.failed'),
            ]);
        }

        // The password was correct, so it is safe to say why access is denied.
        if (! Auth::user()->hasAccess()) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'login' => __('auth.inactive'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * @return array{email: string}|array{phone: string|null}
     */
    private function identifier(): array
    {
        $login = trim($this->string('login')->value());

        return filter_var($login, FILTER_VALIDATE_EMAIL)
            ? ['email' => Str::lower($login)]
            : ['phone' => Phone::normalize($login)];
    }

    /**
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => __('auth.throttle', ['seconds' => $seconds]),
        ]);
    }

    private function throttleKey(): string
    {
        return Str::transliterate(Str::lower(trim($this->string('login')->value())).'|'.$this->ip());
    }
}
