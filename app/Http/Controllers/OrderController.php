<?php

namespace App\Http\Controllers;

use App\Enums\MealType;
use App\Enums\OrderStatus;
use App\Enums\Permission;
use App\Http\Resources\OrderResource;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Order;
use App\Models\Rider;
use App\Services\ActivityLogger;
use App\Services\OrderService;
use App\Support\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Orders generated from confirmed meals. Viewing and kitchen steps need the
 * orders permission; rider steps need the deliveries permission.
 */
class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'client' => ['nullable', 'integer'],
            'meal_type' => ['nullable', Rule::enum(MealType::class)],
            'status' => ['nullable', Rule::enum(OrderStatus::class)],
            'search' => ['nullable', 'string', 'max:50'],
        ]);

        $date = $filters['date'] ?? now()->toDateString();

        $base = Order::query()
            ->where('order_date', $date)
            ->when($filters['client'] ?? null, fn (Builder $query, int $id) => $query->where('client_id', $id))
            ->when($filters['meal_type'] ?? null, fn (Builder $query, string $type) => $query->where('meal_type', $type));

        $statusCounts = (clone $base)->selectRaw('status, COUNT(*) as total')->groupBy('status')->toBase()->pluck('total', 'status');

        $orders = $base
            ->with(['client:id,name', 'delivery.rider:id,name,phone'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, fn (Builder $query, string $term) => Search::apply($query, $term, ['order_number']))
            ->orderByRaw("CASE meal_type WHEN 'lunch' THEN 0 ELSE 1 END")
            ->orderBy('client_id')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('orders/index', [
            'orders' => OrderResource::collection($orders),
            'filters' => [
                'date' => $date,
                'client' => (string) ($filters['client'] ?? ''),
                'meal_type' => $filters['meal_type'] ?? '',
                'status' => $filters['status'] ?? '',
                'search' => $filters['search'] ?? '',
            ],
            'statusCounts' => (object) $statusCounts->map(fn ($total) => (int) $total)->all(),
            'clients' => Client::withTrashed()->orderBy('name')->get(['id', 'name'])
                ->map(fn (Client $client) => ['value' => (string) $client->id, 'label' => $client->name]),
            'mealTypes' => MealType::options(),
            'statuses' => OrderStatus::options(),
            'today' => now()->toDateString(),
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        $user = $request->user();
        abort_unless($user->hasPermission(Permission::ManageOrders) || $user->hasPermission(Permission::ManageDeliveries), 403);

        $order->load(['client:id,name,contact_person,phone,special_instructions', 'items', 'delivery.rider:id,name,phone']);

        return Inertia::render('orders/show', [
            'order' => [
                ...OrderResource::make($order)->resolve($request),
                'client_contact' => [
                    'contact_person' => $order->client->contact_person,
                    'phone' => $order->client->phone,
                    'special_instructions' => $order->client->special_instructions,
                ],
            ],
            'timeline' => ActivityLog::query()
                ->with('user:id,name')
                ->where('subject_type', $order->getMorphClass())
                ->where('subject_id', $order->id)
                ->latest('id')
                ->limit(30)
                ->get()
                ->map(fn (ActivityLog $log) => [
                    'id' => $log->id,
                    'description' => $log->description,
                    'user' => $log->user?->name,
                    'created_at' => $log->created_at?->toIso8601String(),
                ]),
            'riders' => $user->hasPermission(Permission::ManageDeliveries)
                ? Rider::active()->withCount('openDeliveries')->orderBy('name')->get()
                    ->map(fn (Rider $rider) => ['value' => (string) $rider->id, 'label' => $rider->name, 'open' => $rider->open_deliveries_count])
                : null,
            'can' => [
                'manageOrders' => $user->hasPermission(Permission::ManageOrders),
                'manageDeliveries' => $user->hasPermission(Permission::ManageDeliveries),
            ],
        ]);
    }

    public function update(Request $request, Order $order, ActivityLogger $activity): RedirectResponse
    {
        abort_unless($request->user()->hasPermission(Permission::ManageOrders), 403);

        if (in_array($order->status, [OrderStatus::Delivered, OrderStatus::Cancelled], true)) {
            throw ValidationException::withMessages([
                'delivery_address' => "A {$order->status->label()} order can no longer be changed.",
            ]);
        }

        $validated = $request->validate([
            'delivery_address' => ['required', 'string', 'max:500'],
            'delivery_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $order->update($validated);

        if ($changes = $activity->changes($order)) {
            $activity->log('orders', 'details_updated', "Order {$order->order_number}: delivery details updated", $order, $changes);
        }

        $this->toast('Order details saved.');

        return back();
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $status = OrderStatus::from($validated['status']);
        $user = $request->user();

        // Rider steps belong to delivery management; everything else to orders.
        $permission = in_array($status, OrderStatus::riderSteps(), true) ? Permission::ManageDeliveries : Permission::ManageOrders;
        abort_unless($user->hasPermission($permission), 403);

        $order = $this->orders->changeStatus($order, $status, $user, $validated['reason'] ?? null);

        $this->toast("Order {$order->order_number} is now {$status->label()}.");

        return back();
    }
}
