<?php

namespace App\Services;

use App\Enums\MealType;
use App\Models\Client;
use App\Models\ClientMenuPrice;
use App\Models\DailyMenu;
use App\Models\User;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Works out the price of one meal.
 *
 * Order of precedence for a client, meal type and date:
 *   1. the client's own price in effect on that date
 *   2. the price on that day's menu
 *   3. the vendor's default price for the meal type (settings)
 */
class PricingService
{
    public const SOURCE_CLIENT = 'client';

    public const SOURCE_MENU = 'menu';

    public const SOURCE_DEFAULT = 'default';

    public function __construct(
        private readonly Settings $settings,
        private readonly ActivityLogger $activity,
    ) {}

    /**
     * @return array{price: string, source: string}
     */
    public function priceFor(Client|int $client, MealType $type, CarbonInterface|string $date): array
    {
        $date = $this->dateString($date);
        $clientId = $client instanceof Client ? $client->id : $client;

        $clientPrice = $this->clientPriceRecord($clientId, $type, $date);

        if ($clientPrice) {
            return ['price' => Money::of($clientPrice->price), 'source' => self::SOURCE_CLIENT];
        }

        $menuPrice = DailyMenu::query()
            ->where('menu_date', $date)
            ->where('meal_type', $type)
            ->whereNotNull('price')
            ->value('price');

        if ($menuPrice !== null) {
            return ['price' => Money::of($menuPrice), 'source' => self::SOURCE_MENU];
        }

        return ['price' => Money::of($this->settings->defaultMealPrice($type)), 'source' => self::SOURCE_DEFAULT];
    }

    /**
     * The client price row in effect on a date, if the client has one.
     */
    public function clientPriceRecord(int $clientId, MealType $type, string $date): ?ClientMenuPrice
    {
        return ClientMenuPrice::query()
            ->where('client_id', $clientId)
            ->where('meal_type', $type)
            ->where('effective_from', '<=', $date)
            ->orderByDesc('effective_from')
            ->first();
    }

    /**
     * Client prices in effect on a date for many clients at once.
     *
     * @param  list<int>  $clientIds
     * @return array<int, array<string, ClientMenuPrice>> client id => meal type => price row
     */
    public function clientPricesOn(array $clientIds, string $date): array
    {
        $result = [];

        ClientMenuPrice::query()
            ->whereIn('client_id', $clientIds)
            ->where('effective_from', '<=', $date)
            ->orderBy('effective_from')
            ->get()
            // Later effective dates overwrite earlier ones.
            ->each(function (ClientMenuPrice $price) use (&$result) {
                $result[$price->client_id][$price->meal_type->value] = $price;
            });

        return $result;
    }

    /**
     * Schedule a new client price. An existing row for the same day is
     * replaced, so a typo can be corrected the same way it was entered.
     */
    public function setClientPrice(Client $client, MealType $type, string $price, string $effectiveFrom, ?string $notes, User $user): ClientMenuPrice
    {
        return DB::transaction(function () use ($client, $type, $price, $effectiveFrom, $notes, $user) {
            $previous = $this->clientPriceRecord($client->id, $type, $effectiveFrom);

            // client_id is not fillable, so look the row up instead of firstOrNew().
            $record = ClientMenuPrice::query()
                ->where('client_id', $client->id)
                ->where('meal_type', $type)
                ->where('effective_from', $effectiveFrom)
                ->first() ?? new ClientMenuPrice(['meal_type' => $type, 'effective_from' => $effectiveFrom]);

            $record->client_id = $client->id;
            $record->fill(['price' => Money::of($price), 'notes' => $notes]);
            $record->created_by = $user->id;
            $record->save();

            $this->activity->log(
                'pricing',
                'client_price_set',
                sprintf('Set %s %s price to %s from %s', $client->name, mb_strtolower($type->label()), $record->price, CarbonImmutable::parse($effectiveFrom)->format('d M Y')),
                $record,
                ['old' => $previous?->price, 'new' => $record->price, 'effective_from' => $effectiveFrom],
                $user,
            );

            return $record;
        });
    }

    private function dateString(CarbonInterface|string $date): string
    {
        return $date instanceof CarbonInterface ? $date->toDateString() : CarbonImmutable::parse($date)->toDateString();
    }
}
