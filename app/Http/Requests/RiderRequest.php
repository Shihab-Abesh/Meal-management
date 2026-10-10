<?php

namespace App\Http\Requests;

use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Enums\VehicleType;
use App\Models\Rider;
use App\Rules\UniquePhone;
use App\Support\Phone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RiderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(Permission::ManageRiders->value);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rider = $this->route('rider');
        $rider = $rider instanceof Rider ? $rider : null;
        $hasLogin = $rider?->user_id !== null;

        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => [
                'required', 'string', 'max:20', 'regex:'.Phone::PATTERN,
                function (string $attribute, mixed $value, Closure $fail) use ($rider) {
                    $taken = Rider::query()
                        ->where('phone', Phone::normalize((string) $value))
                        ->when($rider, fn ($query) => $query->whereKeyNot($rider->id))
                        ->exists();

                    if ($taken) {
                        $fail('Another rider already uses this phone number.');
                    }
                },
                // The phone is also the rider's sign-in, so it must be free among all accounts.
                ...($hasLogin || $this->boolean('create_login') ? [new UniquePhone($rider?->user_id)] : []),
            ],
            'nid' => ['nullable', 'string', 'max:30', 'regex:/^[0-9A-Za-z\-]+$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'emergency_contact' => ['nullable', 'string', 'max:150'],
            'vehicle_type' => ['required', Rule::enum(VehicleType::class)],
            'vehicle_number' => ['nullable', 'string', 'max:30'],
            'joining_date' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'create_login' => ['boolean'],
            'password' => [
                Rule::requiredIf(! $hasLogin && $this->boolean('create_login')),
                'nullable', 'string', Password::defaults(),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid phone number.',
            'nid.regex' => 'NID may only contain letters, numbers and dashes.',
            'password.required' => 'Set a password so the rider can sign in.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function riderAttributes(): array
    {
        return $this->safe()->except(['create_login', 'password']);
    }
}
