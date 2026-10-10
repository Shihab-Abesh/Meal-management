<?php

namespace App\Services;

use App\Enums\MealType;
use App\Models\Client;
use Carbon\CarbonImmutable;

/**
 * When meals for a client, meal type and date stop being editable.
 *
 * The client's own cutoff time applies, otherwise the vendor default. It
 * is a time on the meal date itself in the app time zone (APP_TIMEZONE),
 * so every past date is closed and future dates are open.
 */
class MealCutoff
{
    public function __construct(private readonly Settings $settings) {}

    /**
     * "HH:MM"
     */
    public function time(Client $client, MealType $type): string
    {
        $own = $type === MealType::Lunch ? $client->lunch_cutoff_time : $client->dinner_cutoff_time;

        return substr((string) ($own ?: $this->settings->defaultCutoff($type)), 0, 5);
    }

    public function closesAt(Client $client, MealType $type, string $date): CarbonImmutable
    {
        return CarbonImmutable::parse($date.' '.$this->time($client, $type), config('app.timezone'));
    }

    public function isClosed(Client $client, MealType $type, string $date): bool
    {
        return now()->greaterThanOrEqualTo($this->closesAt($client, $type, $date));
    }
}
