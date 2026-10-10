<?php

namespace App\Services;

use App\Enums\MealPreference;
use App\Enums\RecordStatus;
use App\Http\Requests\EmployeeRequest;
use App\Models\Client;
use App\Models\Employee;
use App\Models\User;
use App\Support\Phone;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use RuntimeException;

/**
 * Imports employees from a CSV file (Excel: "Save as CSV").
 *
 * Every row is validated on its own. Valid rows are inserted together in
 * one transaction; invalid rows and employee IDs that already exist (at
 * the client or earlier in the file) are reported back and skipped.
 */
class EmployeeImporter
{
    public const MAX_ROWS = 2000;

    /**
     * Template columns, in order.
     */
    public const COLUMNS = [
        'employee_id',
        'name',
        'phone',
        'email',
        'department',
        'designation',
        'meal_preference',
        'lunch',
        'dinner',
        'status',
        'joining_date',
        'notes',
    ];

    /**
     * Accepted header spellings for each column.
     */
    private const ALIASES = [
        'employee_id' => ['employee_id', 'employee_code', 'emp_id', 'id', 'staff_id'],
        'name' => ['name', 'employee_name', 'full_name'],
        'phone' => ['phone', 'mobile', 'phone_number'],
        'email' => ['email', 'email_address'],
        'department' => ['department', 'dept'],
        'designation' => ['designation', 'title', 'position'],
        'meal_preference' => ['meal_preference', 'preference'],
        'lunch' => ['lunch', 'lunch_enabled'],
        'dinner' => ['dinner', 'dinner_enabled'],
        'status' => ['status'],
        'joining_date' => ['joining_date', 'join_date', 'date_of_joining'],
        'notes' => ['notes', 'note', 'remarks'],
    ];

    private const TRUE_VALUES = ['yes', 'y', '1', 'true'];

    private const FALSE_VALUES = ['no', 'n', '0', 'false'];

    public function __construct(private readonly ActivityLogger $activity) {}

    /**
     * @return array{total: int, imported: int, duplicates: list<array{row: int, employee_id: string, name: string, reason: string}>, failed: list<array{row: int, employee_id: string, name: string, errors: list<string>}>}
     *
     * @throws RuntimeException when the file itself cannot be used
     */
    public function import(UploadedFile $file, Client $client, User $user): array
    {
        $rows = $this->readRows($file);

        $existingCodes = Employee::query()
            ->where('client_id', $client->id)
            ->pluck('employee_code')
            ->map(fn (string $code) => mb_strtolower($code))
            ->flip();

        $seenCodes = [];
        $valid = [];
        $duplicates = [];
        $failed = [];

        foreach ($rows as $line => $row) {
            $code = $row['employee_id'];
            $key = mb_strtolower($code);

            $errors = $this->validate($row);

            if ($errors !== []) {
                $failed[] = ['row' => $line, 'employee_id' => $code, 'name' => $row['name'], 'errors' => $errors];

                continue;
            }

            if (isset($existingCodes[$key])) {
                $duplicates[] = ['row' => $line, 'employee_id' => $code, 'name' => $row['name'], 'reason' => 'Already exists at this client'];

                continue;
            }

            if (isset($seenCodes[$key])) {
                $duplicates[] = ['row' => $line, 'employee_id' => $code, 'name' => $row['name'], 'reason' => "Same ID as row {$seenCodes[$key]}"];

                continue;
            }

            $seenCodes[$key] = $line;
            $valid[] = $this->attributes($row, $client, $user);
        }

        if ($valid !== []) {
            DB::transaction(function () use ($valid, $client) {
                foreach (array_chunk($valid, 200) as $chunk) {
                    Employee::insert($chunk);
                }

                $this->activity->log(
                    'employees',
                    'imported',
                    'Imported '.count($valid)." employees into {$client->name}",
                    $client,
                    ['imported' => count($valid)],
                );
            });
        }

        return [
            'total' => count($rows),
            'imported' => count($valid),
            'duplicates' => $duplicates,
            'failed' => $failed,
        ];
    }

    /**
     * Header row plus one example row, ready to download.
     */
    public function template(): string
    {
        $lines = [
            self::COLUMNS,
            ['EMP-001', 'Abdullah Al Mamun', '01711-000000', 'abdullah@example.com', 'Engineering', 'Software Engineer', 'regular', 'yes', 'no', 'active', now()->toDateString(), ''],
        ];

        return "\u{FEFF}".implode("\r\n", array_map(fn (array $line) => $this->csvLine($line), $lines))."\r\n";
    }

    /**
     * @return array<int, array<string, string>> rows keyed by their line number in the file
     */
    private function readRows(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            throw new RuntimeException('The file could not be read.');
        }

        try {
            $firstLine = (string) fgets($handle);
            rewind($handle);

            // Excel in some locales saves CSV with semicolons.
            $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

            $header = fgetcsv($handle, null, $delimiter, '"', '');

            if (! is_array($header)) {
                throw new RuntimeException('The file is empty.');
            }

            $columns = $this->mapHeader($header);

            $rows = [];
            $line = 1;

            while (($values = fgetcsv($handle, null, $delimiter, '"', '')) !== false) {
                $line++;

                if ($values === [null] || implode('', array_map('trim', array_map('strval', $values))) === '') {
                    continue;
                }

                if (count($rows) >= self::MAX_ROWS) {
                    throw new RuntimeException('The file has more than '.self::MAX_ROWS.' rows. Split it into smaller files.');
                }

                $row = array_fill_keys(self::COLUMNS, '');

                foreach ($columns as $index => $column) {
                    $row[$column] = trim((string) ($values[$index] ?? ''));
                }

                $rows[$line] = $row;
            }

            if ($rows === []) {
                throw new RuntimeException('The file has a header row but no employees.');
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param  array<int, string|null>  $header
     * @return array<int, string> column index => template column
     */
    private function mapHeader(array $header): array
    {
        $columns = [];

        foreach ($header as $index => $label) {
            // Strip a UTF-8 byte order mark and normalize "Employee ID" to "employee_id".
            $normalized = preg_replace('/[^a-z0-9]+/', '_', mb_strtolower(trim(str_replace("\u{FEFF}", '', (string) $label))));
            $normalized = trim((string) $normalized, '_');

            foreach (self::ALIASES as $column => $aliases) {
                if (in_array($normalized, $aliases, true) && ! in_array($column, $columns, true)) {
                    $columns[$index] = $column;
                }
            }
        }

        $missing = array_diff(['employee_id', 'name'], $columns);

        if ($missing !== []) {
            throw new RuntimeException('The header row must include these columns: '.implode(', ', $missing).'. Download the template to see the expected format.');
        }

        return $columns;
    }

    /**
     * @param  array<string, string>  $row
     * @return list<string>
     */
    private function validate(array $row): array
    {
        $validator = Validator::make($row, [
            'employee_id' => ['required', 'string', 'max:50', 'regex:'.EmployeeRequest::CODE_PATTERN],
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:'.Phone::PATTERN],
            'email' => ['nullable', 'email', 'max:150'],
            'department' => ['nullable', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'employee_id.required' => 'Employee ID is missing.',
            'employee_id.regex' => 'Employee ID may only use letters, numbers, dashes, slashes, dots or underscores.',
            'name.required' => 'Employee name is missing.',
            'phone.regex' => "Phone number \"{$row['phone']}\" is not valid.",
            'email.email' => "Email \"{$row['email']}\" is not valid.",
        ]);

        $errors = $validator->errors()->all();

        if (MealPreference::fromInput($row['meal_preference']) === null) {
            $allowed = implode(', ', array_map(fn (MealPreference $p) => $p->value, MealPreference::cases()));
            $errors[] = "Meal preference \"{$row['meal_preference']}\" is not valid. Use one of: {$allowed}.";
        }

        foreach (['lunch', 'dinner'] as $meal) {
            if ($row[$meal] !== '' && $this->toBoolean($row[$meal]) === null) {
                $errors[] = ucfirst($meal)." must be yes or no (got \"{$row[$meal]}\").";
            }
        }

        if ($row['status'] !== '' && RecordStatus::tryFrom(mb_strtolower($row['status'])) === null) {
            $errors[] = "Status must be active or inactive (got \"{$row['status']}\").";
        }

        if ($row['joining_date'] !== '' && $this->toDate($row['joining_date']) === null) {
            $errors[] = "Joining date \"{$row['joining_date']}\" is not valid. Use YYYY-MM-DD or DD/MM/YYYY.";
        }

        return $errors;
    }

    /**
     * @param  array<string, string>  $row
     * @return array<string, mixed>
     */
    private function attributes(array $row, Client $client, User $user): array
    {
        $now = now();

        return [
            'client_id' => $client->id,
            'employee_code' => $row['employee_id'],
            'name' => $row['name'],
            'phone' => $row['phone'] ?: null,
            'email' => $row['email'] !== '' ? mb_strtolower($row['email']) : null,
            'department' => $row['department'] ?: null,
            'designation' => $row['designation'] ?: null,
            'meal_preference' => MealPreference::fromInput($row['meal_preference'])->value,
            'lunch_enabled' => $row['lunch'] === '' ? true : $this->toBoolean($row['lunch']),
            'dinner_enabled' => $row['dinner'] === '' ? false : $this->toBoolean($row['dinner']),
            'status' => $row['status'] === '' ? RecordStatus::Active->value : mb_strtolower($row['status']),
            'joining_date' => $row['joining_date'] === '' ? null : $this->toDate($row['joining_date']),
            'notes' => $row['notes'] ?: null,
            'created_by' => $user->id,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function toBoolean(string $value): ?bool
    {
        $value = mb_strtolower(trim($value));

        return match (true) {
            in_array($value, self::TRUE_VALUES, true) => true,
            in_array($value, self::FALSE_VALUES, true) => false,
            default => null,
        };
    }

    private function toDate(string $value): ?string
    {
        foreach (['Y-m-d', 'd/m/Y', 'j/n/Y', 'd-m-Y', 'j-n-Y', 'd.m.Y'] as $format) {
            try {
                $date = CarbonImmutable::createFromFormat('!'.$format, $value);
            } catch (InvalidArgumentException) {
                continue;
            }

            // Re-formatting rejects overflow such as 31/02/2026 becoming 03/03/2026.
            if ($date && $date->format($format) === $value) {
                return $date->toDateString();
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $values
     */
    private function csvLine(array $values): string
    {
        return implode(',', array_map(
            fn (string $value) => preg_match('/[",\r\n]/', $value) ? '"'.str_replace('"', '""', $value).'"' : $value,
            $values,
        ));
    }
}
