<?php

namespace App\Http\Requests;

use App\Enums\Permission;
use App\Models\Client;
use App\Services\ReportService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Which report to show and for what period. Vendor users need the reports
 * permission; client accounts get their own organization's meal summary and
 * employee report, whatever client is asked for.
 */
class ReportRequest extends FormRequest
{
    /**
     * Longest date range a report may cover.
     */
    public const MAX_RANGE_DAYS = 366;

    private const LABELS = [
        'daily' => 'Daily',
        'monthly' => 'Monthly',
        'client' => 'Client',
        'employees' => 'Employees',
        'menu' => 'Menu',
        'riders' => 'Riders',
    ];

    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) ($user?->isClientAdmin() || $user?->hasPermission(Permission::ViewReports));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['nullable', Rule::in($this->allowedTypes())],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'date_format:Y-m'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'client' => ['nullable', 'integer'],
            'format' => ['nullable', Rule::in(['csv', 'pdf'])],
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                [$from, $to] = [$this->from(), $this->to()];

                if (CarbonImmutable::parse($from)->diffInDays(CarbonImmutable::parse($to)) + 1 > self::MAX_RANGE_DAYS) {
                    $validator->errors()->add('to', 'Choose a range of at most one year.');
                }
            },
        ];
    }

    /**
     * @return list<string>
     */
    public function allowedTypes(): array
    {
        return $this->user()?->isClientAdmin() ? ['client', 'employees'] : ReportService::TYPES;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function typeOptions(): array
    {
        $client = $this->user()?->isClientAdmin();

        return array_map(fn (string $type) => [
            'value' => $type,
            'label' => $client && $type === 'client' ? 'Meal summary' : self::LABELS[$type],
        ], $this->allowedTypes());
    }

    public function type(): string
    {
        return $this->validated('type') ?? $this->allowedTypes()[0];
    }

    public function day(): string
    {
        return $this->validated('date') ?? now()->toDateString();
    }

    public function month(): string
    {
        return $this->validated('month') ?? now()->format('Y-m');
    }

    public function from(): string
    {
        return $this->input('from') ?: now()->startOfMonth()->toDateString();
    }

    public function to(): string
    {
        return $this->input('to') ?: now()->toDateString();
    }

    /**
     * The client the report is about. Client accounts always get their own.
     */
    public function client(): ?Client
    {
        $user = $this->user();
        $id = $user->isClientAdmin() ? $user->client_id : $this->validated('client');

        return $id ? Client::withTrashed()->find($id) : null;
    }

    /**
     * Client accounts see what they pay; vendor users need financial access.
     */
    public function showMoney(): bool
    {
        $user = $this->user();

        return $user->isClientAdmin() || $user->hasPermission(Permission::ViewFinancials);
    }
}
