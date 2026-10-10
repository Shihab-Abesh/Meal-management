<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Rider;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The day's delivery board: assign riders and follow each order to the
 * client's door. Routes require the deliveries permission.
 */
class DeliveryController extends Controller
{
    /**
     * Board tabs and the statuses they contain.
     */
    private const TABS = [
        'to_assign' => [OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Preparing, OrderStatus::Ready],
        'assigned' => [OrderStatus::Assigned],
        'on_the_way' => [OrderStatus::PickedUp, OrderStatus::OnTheWay],
        'delivered' => [OrderStatus::Delivered],
    ];

    public function __construct(private readonly OrderService $orders) {}

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'tab' => ['nullable', Rule::in([...array_keys(self::TABS), 'all'])],
        ]);

        $date = $filters['date'] ?? now()->toDateString();
        $tab = $filters['tab'] ?? 'to_assign';

        $dayOrders = Order::query()
            ->with(['client:id,name', 'delivery.rider:id,name,phone'])
            ->where('order_date', $date)
            ->where('status', '!=', OrderStatus::Cancelled)
            ->orderByRaw("CASE meal_type WHEN 'lunch' THEN 0 ELSE 1 END")
            ->orderBy('delivery_time')
            ->orderBy('client_id')
            ->get();

        $counts = collect(self::TABS)->map(fn (array $statuses) => $dayOrders->filter(
            fn (Order $order) => in_array($order->status, $statuses, true),
        )->count());

        $shown = $tab === 'all'
            ? $dayOrders
            : $dayOrders->filter(fn (Order $order) => in_array($order->status, self::TABS[$tab], true))->values();

        return Inertia::render('deliveries/index', [
            'date' => $date,
            'today' => now()->toDateString(),
            'tab' => $tab,
            'counts' => [...$counts->all(), 'all' => $dayOrders->count()],
            'orders' => OrderResource::collection($shown)->resolve($request),
            'riders' => Rider::active()->withCount('openDeliveries')->orderBy('name')->get()
                ->map(fn (Rider $rider) => ['value' => (string) $rider->id, 'label' => $rider->name, 'open' => $rider->open_deliveries_count]),
        ]);
    }

    public function assign(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'rider_id' => ['required', 'integer', Rule::exists('riders', 'id')],
            'delivery_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $rider = Rider::findOrFail($validated['rider_id']);
        $order = $this->orders->assignRider($order, $rider, $request->user(), $validated['delivery_time'] ?? null, $validated['notes'] ?? null);

        $this->toast("{$rider->name} will deliver order {$order->order_number}.");

        return back();
    }

    public function unassign(Request $request, Order $order): RedirectResponse
    {
        $order = $this->orders->unassignRider($order, $request->user());

        $this->toast("Rider removed from order {$order->order_number}.");

        return back();
    }
}
