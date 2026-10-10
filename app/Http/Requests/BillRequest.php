<?php

namespace App\Http\Requests;

use App\Models\Bill;
use App\Models\Client;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Generate a bill for a client and billing period.
 */
class BillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Bill::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Archived clients cannot be billed again; inactive ones can (final bill).
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')->whereNull('deleted_at')],
            'period_start' => ['required', 'date_format:Y-m-d'],
            'period_end' => ['required', 'date_format:Y-m-d', 'after_or_equal:period_start'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_id.required' => 'Choose a client.',
            'client_id.exists' => 'Choose a client.',
            'period_end.after_or_equal' => 'The period must end on or after its first day.',
        ];
    }

    public function client(): Client
    {
        return Client::findOrFail($this->validated('client_id'));
    }
}
