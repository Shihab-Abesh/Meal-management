<?php

namespace App\Services;

use App\Enums\MealType;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Vendor-wide settings: stored values over the defaults in
 * config/catering.php. Values are loaded once per request.
 */
class Settings
{
    /** @var array<string, mixed>|null */
    private ?array $stored = null;

    public function __construct(private readonly ActivityLogger $activity) {}

    public function get(string $key, mixed $default = null): mixed
    {
        $stored = $this->stored();

        if (array_key_exists($key, $stored)) {
            return $stored[$key];
        }

        // Keys contain dots ("meals.lunch_price"), so read the defaults array
        // directly rather than through config()'s dot notation.
        return config('catering.defaults', [])[$key] ?? $default;
    }

    /**
     * Save several settings at once and record what changed.
     *
     * @param  array<string, mixed>  $values
     */
    public function set(array $values, ?User $user = null, string $description = 'Updated settings'): void
    {
        $changes = [];

        foreach ($values as $key => $value) {
            $old = $this->get($key);

            if ($old !== $value) {
                $changes[$key] = ['old' => $old, 'new' => $value];
            }
        }

        if ($changes === []) {
            return;
        }

        DB::transaction(function () use ($values, $user, $changes, $description) {
            foreach ($values as $key => $value) {
                $setting = Setting::query()->firstOrNew(['key' => $key]);
                $setting->value = $value;
                $setting->updated_by = $user?->id;
                $setting->save();
            }

            $this->activity->log('settings', 'updated', $description, null, ['changes' => $changes], $user);
        });

        $this->stored = null;
    }

    public function defaultMealPrice(MealType $type): string
    {
        return (string) $this->get("meals.{$type->value}_price");
    }

    /**
     * "HH:MM" cutoff used when a client has none of its own.
     */
    public function defaultCutoff(MealType $type): string
    {
        return (string) $this->get("meals.{$type->value}_cutoff");
    }

    public function billDueDays(): int
    {
        return max(0, (int) $this->get('billing.due_days', 15));
    }

    /**
     * Whether a notification email is switched on, e.g. "bill_issued".
     */
    public function notifies(string $event): bool
    {
        return (bool) $this->get("notifications.{$event}", false);
    }

    /**
     * @return array<string, mixed>
     */
    private function stored(): array
    {
        return $this->stored ??= Setting::query()->pluck('value', 'key')->all();
    }
}
