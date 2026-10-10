<?php

namespace App\Http\Requests;

use App\Enums\MealType;
use App\Enums\RecordStatus;
use App\Models\Client;
use App\Support\Phone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create or update a client organization.
 */
class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        $client = $this->route('client');

        return $client instanceof Client
            ? (bool) $this->user()?->can('update', $client)
            : (bool) $this->user()?->can('create', Client::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => filled($this->input('email')) ? mb_strtolower(trim((string) $this->input('email'))) : null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $client = $this->route('client');

        return [
            'name' => [
                'required', 'string', 'max:150',
                Rule::unique('clients', 'name')->ignore($client instanceof Client ? $client->id : null),
            ],
            'contact_person' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'regex:'.Phone::PATTERN],
            'email' => ['nullable', 'string', 'email', 'max:150'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'delivery_address' => ['nullable', 'string', 'max:500'],
            'employee_count' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'meal_types' => ['required', 'array', 'min:1'],
            'meal_types.*' => ['distinct', Rule::enum(MealType::class)],
            'office_start_time' => ['nullable', 'date_format:H:i'],
            'lunch_cutoff_time' => ['nullable', 'date_format:H:i'],
            'dinner_cutoff_time' => ['nullable', 'date_format:H:i'],
            'special_instructions' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'organization name',
            'employee_count' => 'number of employees',
            'meal_types' => 'meal types',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A client with this name already exists (it may be archived).',
            'phone.regex' => 'Enter a valid phone number.',
            'meal_types.required' => 'Choose at least one meal type.',
            'meal_types.min' => 'Choose at least one meal type.',
        ];
    }

    /**
     * Validated values mapped to client columns.
     *
     * @return array<string, mixed>
     */
    public function clientAttributes(): array
    {
        $data = $this->safe()->except('meal_types');
        $mealTypes = $this->validated('meal_types');

        return [
            ...$data,
            'lunch_enabled' => in_array(MealType::Lunch->value, $mealTypes, true),
            'dinner_enabled' => in_array(MealType::Dinner->value, $mealTypes, true),
        ];
    }
}
