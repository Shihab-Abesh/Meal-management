<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\RecordStatus;
use App\Enums\VehicleType;
use App\Http\Requests\RiderRequest;
use App\Http\Resources\RiderResource;
use App\Models\Delivery;
use App\Models\Rider;
use App\Services\RiderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Rider management. Routes require the riders permission.
 */
class RiderController extends Controller
{
    public function __construct(private readonly RiderService $riders) {}

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(RecordStatus::class)],
            'vehicle_type' => ['nullable', Rule::enum(VehicleType::class)],
        ]);

        $riders = Rider::query()
            ->withCount('openDeliveries')
            ->search($filters['search'] ?? null)
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['vehicle_type'] ?? null, fn (Builder $query, string $type) => $query->where('vehicle_type', $type))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('riders/index', [
            'riders' => RiderResource::collection($riders),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? '',
                'vehicle_type' => $filters['vehicle_type'] ?? '',
            ],
            'vehicleTypes' => VehicleType::options(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('riders/form', [...$this->formOptions(), 'rider' => null]);
    }

    public function store(RiderRequest $request): RedirectResponse
    {
        $rider = $this->riders->save(null, $request->riderAttributes(), $request->boolean('create_login'), $request->validated('password'), $request->user());

        $this->toast("{$rider->name} has been added.");

        return to_route('riders.show', $rider);
    }

    public function show(Request $request, Rider $rider): Response
    {
        $counts = Delivery::query()
            ->join('orders', 'orders.id', '=', 'deliveries.order_id')
            ->where('deliveries.rider_id', $rider->id)
            ->selectRaw('orders.status as status, COUNT(*) as total')
            ->groupBy('orders.status')
            ->pluck('total', 'status');

        $deliveries = Delivery::query()
            ->with(['order.client:id,name'])
            ->where('rider_id', $rider->id)
            ->latest('assigned_at')
            ->paginate(15)
            ->withQueryString();

        $deliveries->through(fn (Delivery $delivery) => [
            'id' => $delivery->id,
            'order_id' => $delivery->order->id,
            'order_number' => $delivery->order->order_number,
            'order_date' => $delivery->order->order_date->toDateString(),
            'client' => $delivery->order->client->name,
            'meal_type_label' => $delivery->order->meal_type->label(),
            'quantity' => $delivery->order->quantity,
            'status' => $delivery->order->status->value,
            'status_label' => $delivery->order->status->label(),
            'delivered_at' => $delivery->delivered_at?->toIso8601String(),
        ]);

        $open = collect([OrderStatus::Assigned, OrderStatus::PickedUp, OrderStatus::OnTheWay])
            ->sum(fn (OrderStatus $status) => (int) ($counts[$status->value] ?? 0));

        return Inertia::render('riders/show', [
            'rider' => RiderResource::make($rider)->resolve($request),
            'stats' => [
                'assigned' => (int) $counts->sum(),
                'completed' => (int) ($counts[OrderStatus::Delivered->value] ?? 0),
                'cancelled' => (int) ($counts[OrderStatus::Cancelled->value] ?? 0),
                'pending' => $open,
            ],
            'deliveries' => JsonResource::collection($deliveries),
        ]);
    }

    public function edit(Request $request, Rider $rider): Response
    {
        return Inertia::render('riders/form', [...$this->formOptions(), 'rider' => RiderResource::make($rider)->resolve($request)]);
    }

    public function update(RiderRequest $request, Rider $rider): RedirectResponse
    {
        $this->riders->save($rider, $request->riderAttributes(), $request->boolean('create_login'), $request->validated('password'), $request->user());

        $this->toast('Rider details saved.');

        return to_route('riders.show', $rider);
    }

    public function updateStatus(Request $request, Rider $rider): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', Rule::enum(RecordStatus::class)]]);
        $status = RecordStatus::from($validated['status']);

        $this->riders->setStatus($rider, $status);

        $this->toast($status === RecordStatus::Active ? "{$rider->name} is active." : "{$rider->name} has been deactivated and signed out.");

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'vehicleTypes' => VehicleType::options(),
            'statuses' => RecordStatus::options(),
        ];
    }
}
