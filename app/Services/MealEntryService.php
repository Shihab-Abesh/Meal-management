<?php

namespace App\Services;

use App\Enums\MealEntryStatus;
use App\Enums\MealType;
use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Models\Client;
use App\Models\Employee;
use App\Models\MealEntry;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Saves the daily meal sheet: who takes lunch / dinner for a client on a
 * date. This is the only place meal entries are written.
 */
class MealEntryService
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly MealCutoff $cutoff,
        private readonly ActivityLogger $activity,
        private readonly OrderSync $orders,
    ) {}

    /**
     * Confirm the selected employees for each given meal type. Employees no
     * longer selected have their meal cancelled.
     *
     * @param  array<string, list<int>>  $selections  meal type value => employee ids
     * @return array<string, array{added: int, removed: int, total: int}>
     *
     * @throws ValidationException
     */
    public function saveSheet(Client $client, string $date, array $selections, User $user, bool $override = false): array
    {
        $this->guardCutoffs($client, $date, array_keys($selections), $user, $override);

        $result = [];

        DB::transaction(function () use ($client, $date, $selections, $user, $override, &$result) {
            // Serialize saves for this client so two people confirming at the
            // same moment cannot create duplicate rows.
            Client::query()->whereKey($client->id)->lockForUpdate()->first();

            foreach ($selections as $typeValue => $employeeIds) {
                $type = MealType::from($typeValue);
                $result[$type->value] = $this->saveMealType($client, $date, $type, array_values(array_unique($employeeIds)), $user, $override);
            }
        });

        return $result;
    }

    /**
     * @param  list<int>  $employeeIds
     * @return array{added: int, removed: int, total: int}
     */
    private function saveMealType(Client $client, string $date, MealType $type, array $employeeIds, User $user, bool $override): array
    {
        $existing = MealEntry::query()
            ->where('client_id', $client->id)
            ->where('meal_date', $date)
            ->where('meal_type', $type)
            ->lockForUpdate()
            ->get()
            ->keyBy('employee_id');

        $this->guardEmployees($client, $employeeIds, $existing->keys()->all());

        $price = $this->pricing->priceFor($client, $type, $date)['price'];
        $now = now();
        $selected = array_flip($employeeIds);
        $added = [];
        $removed = [];

        foreach ($employeeIds as $employeeId) {
            $entry = $existing->get($employeeId);

            if ($entry === null) {
                MealEntry::create([
                    'client_id' => $client->id,
                    'employee_id' => $employeeId,
                    'meal_date' => $date,
                    'meal_type' => $type,
                    'status' => MealEntryStatus::Confirmed,
                    'unit_price' => $price,
                    'confirmed_by' => $user->id,
                    'confirmed_at' => $now,
                    'updated_by' => $user->id,
                ]);
                $added[] = $employeeId;
            } elseif ($entry->status === MealEntryStatus::Cancelled) {
                $entry->update([
                    'status' => MealEntryStatus::Confirmed,
                    'unit_price' => $price,
                    'confirmed_by' => $user->id,
                    'confirmed_at' => $now,
                    'updated_by' => $user->id,
                ]);
                $added[] = $employeeId;
            }
        }

        foreach ($existing as $employeeId => $entry) {
            if (! isset($selected[$employeeId]) && $entry->status === MealEntryStatus::Confirmed) {
                $entry->update(['status' => MealEntryStatus::Cancelled, 'updated_by' => $user->id]);
                $removed[] = $employeeId;
            }
        }

        $total = count($employeeIds);

        if ($added !== [] || $removed !== []) {
            // Same transaction: the order always matches the confirmed meals.
            $this->orders->syncFromMeals($client, $date, $type, $user);

            $firstTime = $existing->isEmpty();
            $label = sprintf('%s for %s on %s', $type->label(), $client->name, CarbonImmutable::parse($date)->format('d M Y'));

            $this->activity->log(
                'meals',
                $firstTime ? 'confirmed' : 'edited',
                ($firstTime ? "Confirmed {$total} meals: " : "Edited meals ({$total} now): ").$label,
                $client,
                [
                    'date' => $date,
                    'meal_type' => $type->value,
                    'total' => $total,
                    'added' => count($added),
                    'removed' => count($removed),
                    'added_employee_ids' => array_slice($added, 0, 100),
                    'removed_employee_ids' => array_slice($removed, 0, 100),
                    'override' => $override && $this->cutoff->isClosed($client, $type, $date),
                ],
                $user,
            );
        }

        return ['added' => count($added), 'removed' => count($removed), 'total' => $total];
    }

    /**
     * After the cutoff only users allowed to override may change meals, and
     * only when they confirm they mean to.
     *
     * @param  list<string>  $types
     *
     * @throws ValidationException
     */
    private function guardCutoffs(Client $client, string $date, array $types, User $user, bool $override): void
    {
        $canOverride = $user->hasPermission(Permission::OverrideMealCutoff);

        foreach ($types as $typeValue) {
            $type = MealType::from($typeValue);

            if (! $this->cutoff->isClosed($client, $type, $date)) {
                continue;
            }

            $closedAt = $this->cutoff->closesAt($client, $type, $date)->format('d M Y, g:i A');

            if (! $canOverride) {
                throw ValidationException::withMessages([
                    "meals.{$type->value}" => "{$type->label()} for this day closed at {$closedAt}. Contact the vendor to change it.",
                ]);
            }

            if (! $override) {
                throw ValidationException::withMessages([
                    'override' => "{$type->label()} for this day closed at {$closedAt}. Confirm the override to save changes.",
                ]);
            }
        }
    }

    /**
     * Every employee must belong to the client. New selections must also be
     * active; employees who already have an entry may be unmarked or kept
     * even if they have since been deactivated.
     *
     * @param  list<int>  $employeeIds
     * @param  list<int>  $existingIds
     *
     * @throws ValidationException
     */
    private function guardEmployees(Client $client, array $employeeIds, array $existingIds): void
    {
        $newIds = array_values(array_diff($employeeIds, $existingIds));

        $valid = Employee::query()
            ->where('client_id', $client->id)
            ->whereIn('id', $employeeIds)
            ->get(['id', 'status'])
            ->filter(fn (Employee $employee) => $employee->status === RecordStatus::Active || ! in_array($employee->id, $newIds, true))
            ->modelKeys();

        if (count($valid) !== count($employeeIds)) {
            throw ValidationException::withMessages([
                'meals' => 'Some selected employees do not belong to this client or are inactive. Reload the page and try again.',
            ]);
        }
    }
}
