<?php

namespace App\Http\Controllers;

use App\Enums\MealType;
use App\Models\Client;
use App\Models\ClientMenuPrice;
use App\Services\ActivityLogger;
use App\Services\PricingService;
use App\Services\Settings;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Meal prices: vendor defaults and client-specific prices with history.
 * Routes require the pricing permission.
 */
class PricingController extends Controller
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly Settings $settings,
    ) {}

    public function index(Request $request): Response
    {
        $validated = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $today = now()->toDateString();

        $clients = Client::query()
            ->search($validated['search'] ?? null)
            ->orderBy('name')
            ->get(['id', 'name', 'status', 'lunch_enabled', 'dinner_enabled', 'deleted_at']);

        $clientPrices = $this->pricing->clientPricesOn($clients->modelKeys(), $today);
        $defaults = $this->defaults();

        return Inertia::render('pricing/index', [
            'defaults' => $defaults,
            'clients' => $clients->map(fn (Client $client) => [
                'id' => $client->id,
                'name' => $client->name,
                'status' => $client->status->value,
                'meal_types' => array_map(fn (MealType $type) => $type->value, $client->mealTypes()),
                'prices' => collect(MealType::cases())->mapWithKeys(fn (MealType $type) => [
                    $type->value => isset($clientPrices[$client->id][$type->value])
                        ? ['price' => $clientPrices[$client->id][$type->value]->price, 'custom' => true]
                        : ['price' => $defaults[$type->value], 'custom' => false],
                ]),
            ]),
            'filters' => ['search' => $validated['search'] ?? ''],
        ]);
    }

    public function updateDefaults(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lunch_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999.99'],
            'dinner_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999.99'],
        ]);

        $this->settings->set([
            'meals.lunch_price' => Money::of($validated['lunch_price']),
            'meals.dinner_price' => Money::of($validated['dinner_price']),
        ], $request->user(), 'Updated default meal prices');

        $this->toast('Default prices saved.');

        return back();
    }

    public function show(Request $request, Client $client): Response
    {
        $today = now()->toDateString();

        $history = ClientMenuPrice::query()
            ->with('creator:id,name')
            ->where('client_id', $client->id)
            ->orderByDesc('effective_from')
            ->orderBy('meal_type')
            ->get();

        $current = collect(MealType::cases())->mapWithKeys(function (MealType $type) use ($client, $today) {
            $resolved = $this->pricing->priceFor($client, $type, $today);

            return [$type->value => $resolved];
        });

        return Inertia::render('pricing/show', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'meal_types' => array_map(fn (MealType $type) => $type->value, $client->mealTypes()),
            ],
            'current' => $current,
            'history' => $history->map(fn (ClientMenuPrice $price) => [
                'id' => $price->id,
                'meal_type' => $price->meal_type->value,
                'meal_type_label' => $price->meal_type->label(),
                'price' => $price->price,
                'effective_from' => $price->effective_from->toDateString(),
                'notes' => $price->notes,
                'created_by' => $price->creator?->name,
                'created_at' => $price->created_at?->toIso8601String(),
                'upcoming' => $price->effective_from->toDateString() > $today,
            ]),
            'mealTypes' => MealType::options(),
            'today' => $today,
        ]);
    }

    public function store(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'meal_type' => ['required', Rule::enum(MealType::class)],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999.99'],
            'effective_from' => ['required', 'date_format:Y-m-d'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'price.decimal' => 'Use at most two decimal places.',
        ]);

        $this->pricing->setClientPrice(
            $client,
            MealType::from($validated['meal_type']),
            (string) $validated['price'],
            $validated['effective_from'],
            $validated['notes'] ?? null,
            $request->user(),
        );

        $this->toast('Price saved.');

        return back();
    }

    /**
     * Only prices that have not taken effect yet can be removed, so the
     * history of what a client was charged stays intact.
     */
    public function destroy(Client $client, ClientMenuPrice $price, ActivityLogger $activity): RedirectResponse
    {
        if ($price->effective_from->toDateString() <= now()->toDateString()) {
            throw ValidationException::withMessages([
                'price' => 'Prices already in effect cannot be deleted. Add a new price with a later date instead.',
            ]);
        }

        DB::transaction(function () use ($client, $price, $activity) {
            $price->delete();
            $activity->log(
                'pricing',
                'client_price_removed',
                sprintf('Removed %s %s price %s scheduled for %s', $client->name, mb_strtolower($price->meal_type->label()), $price->price, $price->effective_from->format('d M Y')),
                $client,
            );
        });

        $this->toast('Scheduled price removed.');

        return back();
    }

    /**
     * @return array{lunch: string, dinner: string}
     */
    private function defaults(): array
    {
        return [
            'lunch' => $this->settings->defaultMealPrice(MealType::Lunch),
            'dinner' => $this->settings->defaultMealPrice(MealType::Dinner),
        ];
    }
}
