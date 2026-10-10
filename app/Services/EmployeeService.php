<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Models\Client;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Writes to employees, each recorded in the activity log.
 */
class EmployeeService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(Client $client, array $attributes, User $creator): Employee
    {
        return DB::transaction(function () use ($client, $attributes, $creator) {
            $employee = new Employee($attributes);
            $employee->client_id = $client->id;
            $employee->created_by = $creator->id;
            $employee->save();

            $this->activity->log('employees', 'created', "Added employee {$employee->name} ({$employee->employee_code}) to {$client->name}", $employee);

            return $employee;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Employee $employee, array $attributes): Employee
    {
        return DB::transaction(function () use ($employee, $attributes) {
            $employee->fill($attributes)->save();

            if ($changes = $this->activity->changes($employee)) {
                $this->activity->log('employees', 'updated', "Updated employee {$employee->name} ({$employee->employee_code})", $employee, $changes);
            }

            return $employee;
        });
    }

    public function setStatus(Employee $employee, RecordStatus $status): void
    {
        if ($employee->status === $status) {
            return;
        }

        DB::transaction(function () use ($employee, $status) {
            $employee->update(['status' => $status]);

            $verb = $status === RecordStatus::Active ? 'Activated' : 'Deactivated';
            $this->activity->log('employees', 'status_changed', "{$verb} employee {$employee->name} ({$employee->employee_code})", $employee, ['status' => $status->value]);
        });
    }
}
