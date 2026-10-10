<?php

namespace App\Rules;

use App\Models\User;
use App\Support\Phone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A phone number not already used by another account, compared in the
 * normalized format used for sign-in ("017 1234-5678" = "01712345678").
 * Archived accounts still hold their number.
 */
class UniquePhone implements ValidationRule
{
    public function __construct(private readonly ?int $ignoreUserId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalized = Phone::normalize(is_string($value) ? $value : null);

        if ($normalized === null) {
            return;
        }

        $taken = User::withTrashed()
            ->where('phone', $normalized)
            ->when($this->ignoreUserId, fn ($query) => $query->whereKeyNot($this->ignoreUserId))
            ->exists();

        if ($taken) {
            $fail(__('validation.unique', ['attribute' => 'phone']));
        }
    }
}
