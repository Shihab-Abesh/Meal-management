<?php

namespace App\Http\Requests\Profile;

use App\Models\User;
use App\Rules\UniquePhone;
use App\Support\Phone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Normalize before validating so uniqueness is checked against the
     * stored format.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => filled($this->input('email')) ? Str::lower(trim((string) $this->input('email'))) : null,
            'phone' => filled($this->input('phone')) ? trim((string) $this->input('phone')) : null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'nullable', 'required_without:phone', 'string', 'email', 'max:150',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => [
                'nullable', 'required_without:email', 'string', 'regex:'.Phone::PATTERN,
                new UniquePhone($this->user()->id),
            ],
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
