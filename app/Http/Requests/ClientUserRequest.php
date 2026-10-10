<?php

namespace App\Http\Requests;

use App\Enums\RecordStatus;
use App\Models\Client;
use App\Models\User;
use App\Rules\UniquePhone;
use App\Support\Phone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Create or update a client login account. The client comes from the URL,
 * never from the submitted data.
 */
class ClientUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $client = $this->route('client');

        return $client instanceof Client && (bool) $this->user()?->can('manageUsers', $client);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => filled($this->input('email')) ? mb_strtolower(trim((string) $this->input('email'))) : null,
            'phone' => filled($this->input('phone')) ? trim((string) $this->input('phone')) : null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        $ignoreId = $user instanceof User ? $user->id : null;

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'nullable', 'required_without:phone', 'string', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($ignoreId),
            ],
            'phone' => [
                'nullable', 'required_without:email', 'string', 'max:20', 'regex:'.Phone::PATTERN,
                new UniquePhone($ignoreId),
            ],
            'password' => [$ignoreId ? 'nullable' : 'required', 'string', Password::defaults()],
            'status' => ['required', Rule::enum(RecordStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required_without' => 'Enter an email address or a phone number.',
            'phone.required_without' => 'Enter a phone number or an email address.',
            'phone.regex' => 'Enter a valid phone number.',
        ];
    }
}
