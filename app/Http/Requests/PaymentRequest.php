<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Models\Bill;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Record a payment against a bill.
 */
class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $bill = $this->route('bill');

        return $bill instanceof Bill && (bool) $this->user()?->can('pay', $bill);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            // Bank, bKash and Nagad payments have a transaction ID to trace them.
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_date.before_or_equal' => 'The payment date cannot be in the future.',
            'amount.gt' => 'Enter an amount greater than zero.',
            'amount.decimal' => 'Use at most two decimal places.',
        ];
    }
}
