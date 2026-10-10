<?php

namespace App\Services;

use App\Enums\MealEntryStatus;
use App\Enums\MealType;
use App\Enums\Permission;
use App\Models\Client;
use App\Models\Employee;
use App\Models\MealEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Builds what the daily meal pages show: the vendor's overview of every
 * client for a day, and one client's meal sheet.
 */
class MealSheetService
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly MealCutoff $cutoff,
    ) {}

    /**
     * Every active client with its confirmed meal counts for the date.
     *
     * @return list<array<string, mixed>>
     */
    public function overview(string $date, User $viewer): array
    {
        $clients = Client::query()
            ->active()
            ->withCount(['employees' => fn (Builder $query) => $query->active()])
            ->orderBy('name')
            ->get();

        $counts = MealEntry::query()
            ->confirmed()
            ->where('meal_date', $date)
            ->whereIn('client_id', $clients->modelKeys())
            ->selectRaw('client_id, meal_type, COUNT(*) as meals')
            ->groupBy('client_id', 'meal_type')
            ->toBase()
            ->get()
            ->groupBy('client_id');

        return $clients->map(function (Client $client) use ($counts, $date) {
            $clientCounts = $counts->get($client->id)?->pluck('meals', 'meal_type') ?? collect();

            return [
                'id' => $client->id,
                'name' => $client->name,
                'employees' => $client->employees_count,
                'meals' => array_map(fn (MealType $type) => [
                    'meal_type' => $type->value,
                    'label' => $type->label(),
                    'count' => (int) ($clientCounts[$type->value] ?? 0),
                    'cutoff' => $this->cutoff->time($client, $type),
                    'closed' => $this->cutoff->isClosed($client, $type, $date),
                ], $client->mealTypes()),
            ];
        })->all();
    }

    /**
     * Everything the meal sheet page needs for one client and date.
     *
     * @return array<string, mixed>
     */
    public function sheet(Client $client, string $date, User $viewer): array
    {
        $types = $client->mealTypes();
        // Clients see what they pay; vendor staff only with financial access.
        $showAmounts = $viewer->isClientAdmin() || $viewer->hasPermission(Permission::ViewFinancials);

        $entries = MealEntry::query()
            ->with('confirmer:id,name')
            ->where('client_id', $client->id)
            ->where('meal_date', $date)
            ->get();

        $confirmed = $entries->where('status', MealEntryStatus::Confirmed);

        // Active employees, plus anyone who already has a meal that day so it
        // can still be seen and unmarked after they were deactivated.
        $employees = Employee::query()
            ->where('client_id', $client->id)
            ->where(fn (Builder $query) => $query
                ->active()
                ->orWhereIn('id', $confirmed->pluck('employee_id')->unique()->all()))
            ->orderBy('name')
            ->get(['id', 'name', 'employee_code', 'department', 'meal_preference', 'lunch_enabled', 'dinner_enabled', 'status']);

        return [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'meal_types' => array_map(fn (MealType $type) => $type->value, $types),
            ],
            'date' => $date,
            'showAmounts' => $showAmounts,
            'canOverride' => $viewer->hasPermission(Permission::OverrideMealCutoff),
            'meals' => array_map(function (MealType $type) use ($client, $date, $entries, $confirmed, $showAmounts) {
                $typeEntries = $entries->where('meal_type', $type);
                $lastChange = $typeEntries->sortByDesc('updated_at')->first();

                return [
                    'meal_type' => $type->value,
                    'label' => $type->label(),
                    'cutoff' => $this->cutoff->time($client, $type),
                    'closes_at' => $this->cutoff->closesAt($client, $type, $date)->toIso8601String(),
                    'closed' => $this->cutoff->isClosed($client, $type, $date),
                    'price' => $showAmounts ? $this->pricing->priceFor($client, $type, $date)['price'] : null,
                    // employee id => unit price of their confirmed meal
                    'selected' => $confirmed->where('meal_type', $type)
                        ->mapWithKeys(fn (MealEntry $entry) => [$entry->employee_id => $showAmounts ? $entry->unit_price : null])
                        ->all() ?: (object) [],
                    'saved' => $typeEntries->isNotEmpty(),
                    'last_saved_at' => $lastChange?->updated_at?->toIso8601String(),
                    'last_confirmed_by' => $lastChange?->confirmer?->name,
                ];
            }, $types),
            'employees' => $employees->map(fn (Employee $employee) => [
                'id' => $employee->id,
                'name' => $employee->name,
                'employee_code' => $employee->employee_code,
                'department' => $employee->department,
                'meal_preference' => $employee->meal_preference->value,
                'meal_preference_label' => $employee->meal_preference->label(),
                'takes' => ['lunch' => $employee->lunch_enabled, 'dinner' => $employee->dinner_enabled],
                'active' => $employee->isActive(),
            ])->all(),
        ];
    }
}
