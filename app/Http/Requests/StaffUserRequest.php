<?php

namespace App\Http\Requests;

use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\User;
use App\Rules\UniquePhone;
use App\Support\Phone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Create or update a vendor-side account (admin or staff) and, for staff,
 * the permissions they hold.
 */
class StaffUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission(Permission::ManageUsers);
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
            'role' => ['required', Rule::in([UserRole::VendorAdmin->value, UserRole::VendorStaff->value])],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'permissions' => ['array'],
            'permissions.*' => ['distinct', Rule::enum(Permission::class)],
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

    /**
     * @return list<Permission>
     */
    public function permissions(): array
    {
        return array_map(fn (string $value) => Permission::from($value), $this->validated('permissions') ?? []);
    }
}
