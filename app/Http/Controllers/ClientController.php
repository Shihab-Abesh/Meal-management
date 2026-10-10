<?php

namespace App\Http\Controllers;

use App\Enums\MealType;
use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Http\Requests\ClientRequest;
use App\Http\Resources\ClientDetailResource;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ClientUserResource;
use App\Models\Client;
use App\Services\ClientOverview;
use App\Services\ClientService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    /**
     * Columns the list can be sorted by.
     */
    private const SORTS = ['name', 'city', 'created_at', 'employees_count'];

    public function __construct(private readonly ClientService $clients) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Client::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'archived'])],
            'meal_type' => ['nullable', Rule::enum(MealType::class)],
            'city' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(self::SORTS)],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $sort = $filters['sort'] ?? 'name';
        $direction = $filters['direction'] ?? ($sort === 'created_at' ? 'desc' : 'asc');

        $clients = Client::query()
            ->withCount(['employees' => fn (Builder $query) => $query->active()])
            ->search($filters['search'] ?? null)
            ->when($filters['status'] ?? null, function (Builder $query, string $status) {
                $status === 'archived' ? $query->onlyTrashed() : $query->where('status', $status);
            })
            ->when($filters['meal_type'] ?? null, fn (Builder $query, string $type) => $query->serving(MealType::from($type)))
            ->when($filters['city'] ?? null, fn (Builder $query, string $city) => $query->where('city', $city))
            ->orderBy($sort, $direction)
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('clients/index', [
            'clients' => ClientResource::collection($clients),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? '',
                'meal_type' => $filters['meal_type'] ?? '',
                'city' => $filters['city'] ?? '',
                'sort' => $sort,
                'direction' => $direction,
            ],
            'cities' => Client::withTrashed()->distinct()->orderBy('city')->pluck('city'),
            'mealTypes' => MealType::options(),
            'can' => ['create' => $request->user()->can('create', Client::class)],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Client::class);

        return Inertia::render('clients/create', $this->formOptions());
    }

    public function store(ClientRequest $request): RedirectResponse
    {
        $client = $this->clients->create($request->clientAttributes(), $request->user());

        $this->toast("{$client->name} has been added.");

        return to_route('clients.show', $client);
    }

    public function show(Request $request, Client $client, ClientOverview $overview): Response
    {
        $this->authorize('view', $client);

        $user = $request->user();
        $canManageUsers = $user->can('manageUsers', $client);

        return Inertia::render('clients/show', [
            'client' => ClientDetailResource::make($client)->resolve($request),
            'overview' => $overview->for($client, $user),
            'users' => $canManageUsers
                ? ClientUserResource::collection($client->users()->orderBy('name')->get())->resolve($request)
                : null,
            'statuses' => RecordStatus::options(),
            'can' => [
                'update' => $user->can('update', $client),
                'delete' => $user->can('delete', $client),
                'restore' => $user->can('restore', $client),
                'manageUsers' => $canManageUsers,
                'managePricing' => $user->hasPermission(Permission::ManagePricing) && ! $client->trashed(),
                'viewOrders' => $user->hasPermission(Permission::ManageOrders),
            ],
        ]);
    }

    public function edit(Request $request, Client $client): Response
    {
        $this->authorize('update', $client);

        return Inertia::render('clients/edit', [
            ...$this->formOptions(),
            'client' => ClientDetailResource::make($client)->resolve($request),
        ]);
    }

    public function update(ClientRequest $request, Client $client): RedirectResponse
    {
        $this->clients->update($client, $request->clientAttributes());

        $this->toast('Client details saved.');

        return to_route('clients.show', $client);
    }

    public function updateStatus(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $validated = $request->validate(['status' => ['required', Rule::enum(RecordStatus::class)]]);
        $status = RecordStatus::from($validated['status']);

        $this->clients->setStatus($client, $status);

        $this->toast($status === RecordStatus::Active ? "{$client->name} is active." : "{$client->name} has been deactivated.");

        return back();
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $this->clients->archive($client);

        $this->toast("{$client->name} has been archived. You can restore it from the archived list.");

        return to_route('clients.index');
    }

    public function restore(Client $client): RedirectResponse
    {
        $this->authorize('restore', $client);

        $this->clients->restore($client);

        $this->toast("{$client->name} has been restored.");

        return to_route('clients.show', $client);
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'mealTypes' => MealType::options(),
            'statuses' => RecordStatus::options(),
            'cities' => Client::withTrashed()->distinct()->orderBy('city')->pluck('city'),
        ];
    }
}
