<?php

namespace App\Http\Requests;

use App\Enums\MealPreference;
use App\Enums\RecordStatus;
use App\Models\Client;
use App\Models\Employee;
use App\Support\Phone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create or update an employee.
 *
 * The client is never taken from the request for client accounts (it is
 * their own organization) or for updates (an employee cannot change
 * client). Only vendor users choose the client, and only when creating.
 */
class EmployeeRequest extends FormRequest
{
    /**
     * Characters allowed in an employee ID, e.g. "EMP-001", "HR/22".
     */
    public const CODE_PATTERN = '/^[A-Za-z0-9][A-Za-z0-9\-_\/.]*$/';

    public function authorize(): bool
    {
        $employee = $this->route('employee');

        return $employee instanceof Employee
            ? (bool) $this->user()?->can('update', $employee)
            : (bool) $this->user()?->can('create', Employee::class);
    }

    protected function prepareForValidation(): void
    {
        $employee = $this->route('employee');
        $user = $this->user();

        $clientId = match (true) {
            $employee instanceof Employee => $employee->client_id,
            $user?->isClientAdmin() => $user->client_id,
            default => $this->input('client_id'),
        };

        $this->merge([
            'client_id' => $clientId,
            'employee_code' => trim((string) $this->input('employee_code')),
            'email' => filled($this->input('email')) ? mb_strtolower(trim((string) $this->input('email'))) : null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employee = $this->route('employee');

        return [
            'client_id' => [
                'required', 'integer',
                Rule::exists('clients', 'id')->whereNull('deleted_at')->where('status', RecordStatus::Active->value),
            ],
            'employee_code' => [
                'required', 'string', 'max:50', 'regex:'.self::CODE_PATTERN,
                Rule::unique('employees', 'employee_code')
                    ->where('client_id', (int) $this->input('client_id'))
                    ->ignore($employee instanceof Employee ? $employee->id : null),
            ],
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:'.Phone::PATTERN],
            'email' => ['nullable', 'string', 'email', 'max:150'],
            'department' => ['nullable', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
            'meal_preference' => ['required', Rule::enum(MealPreference::class)],
            'lunch_enabled' => ['boolean'],
            'dinner_enabled' => ['boolean'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'joining_date' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:+1 year'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'client_id' => 'client',
            'employee_code' => 'employee ID',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_id.exists' => 'Choose an active client.',
            'employee_code.unique' => 'This employee ID is already used at this client.',
            'employee_code.regex' => 'Use letters, numbers, dashes, slashes, dots or underscores.',
            'phone.regex' => 'Enter a valid phone number.',
        ];
    }

    public function client(): Client
    {
        return Client::findOrFail((int) $this->validated('client_id'));
    }

    /**
     * @return array<string, mixed>
     */
    public function employeeAttributes(): array
    {
        return [
            ...$this->safe()->except('client_id'),
            'lunch_enabled' => $this->boolean('lunch_enabled'),
            'dinner_enabled' => $this->boolean('dinner_enabled'),
        ];
    }
}
