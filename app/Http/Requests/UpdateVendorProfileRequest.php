<?php

namespace App\Http\Requests;

use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Support\Phone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(Permission::ManageVendorProfile->value);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:150'],
            'organization_name' => ['nullable', 'string', 'max:150'],
            'owner_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'regex:'.Phone::PATTERN],
            'email' => ['required', 'string', 'email', 'max:150'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'business_type' => ['nullable', 'string', 'max:100'],
            'vat_tin' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9\-\/ ]+$/'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'logo' => [
                'nullable',
                'image',
                'mimes:'.implode(',', config('catering.logo.mimes')),
                'max:'.config('catering.logo.max_kb'),
            ],
            'remove_logo' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'vat_tin' => 'VAT/TIN',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid phone number.',
            'vat_tin.regex' => 'VAT/TIN may only contain letters, numbers, spaces, dashes and slashes.',
        ];
    }

    /**
     * Validated profile fields, without the upload controls.
     *
     * @return array<string, mixed>
     */
    public function profileAttributes(): array
    {
        return $this->safe()->except(['logo', 'remove_logo']);
    }
}
