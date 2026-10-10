<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Delivery;
use App\Models\OrderItem;
use App\Models\Rider;
use App\Models\User;
use App\Services\OrderService;
use App\Support\Time;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The rider's own deliveries. A rider only ever sees deliveries assigned to
 * them, and never amounts, other clients or employee details.
 */
class RiderPanelController extends Controller
{
    public function index(Request $request): Response
    {
        $rider = $this->rider($request->user());
        $today = now()->toDateString();

        // Today's deliveries plus anything still open from earlier days.
        $deliveries = $this->deliveriesOf($rider)
            ->whereHas('order', fn (Builder $query) => $query
                ->where('order_date', $today)
                ->orWhereIn('status', [OrderStatus::Assigned, OrderStatus::PickedUp, OrderStatus::OnTheWay]))
            ->get()
            ->sortBy(fn (Delivery $delivery) => [
                $delivery->order->status === OrderStatus::Delivered || $delivery->order->status === OrderStatus::Cancelled ? 1 : 0,
                $delivery->order->delivery_time ?? '99:99',
            ])
            ->values();

        return Inertia::render('rider/deliveries', [
            'deliveries' => $deliveries->map(fn (Delivery $delivery) => $this->present($delivery))->all(),
            'today' => $today,
            'riderName' => $rider->name,
        ]);
    }

    public function history(Request $request): Response
    {
        $rider = $this->rider($request->user());

        $filters = $request->validate(['status' => ['nullable', Rule::in(['delivered', 'cancelled'])]]);

        $deliveries = $this->deliveriesOf($rider)
            ->whereHas('order', fn (Builder $query) => $query
                ->whereIn('status', [OrderStatus::Delivered, OrderStatus::Cancelled])
                ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status)))
            ->latest('assigned_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Delivery $delivery) => $this->present($delivery));

        return Inertia::render('rider/history', [
            'deliveries' => JsonResource::collection($deliveries),
            'filters' => ['status' => $filters['status'] ?? ''],
        ]);
    }

    public function show(Request $request, Delivery $delivery): Response
    {
        $this->authorizeOwn($request->user(), $delivery);

        $delivery->load(['order.client:id,name,contact_person,phone,special_instructions', 'order.items']);

        return Inertia::render('rider/delivery', [
            'delivery' => [
                ...$this->present($delivery),
                'contact_person' => $delivery->order->client->contact_person,
                'contact_phone' => $delivery->order->client->phone,
                'special_instructions' => $delivery->order->client->special_instructions,
                'notes' => $delivery->notes,
                'items' => $delivery->order->items->map(fn (OrderItem $item) => ['id' => $item->id, 'name' => $item->name])->all(),
            ],
        ]);
    }

    public function updateStatus(Request $request, Delivery $delivery, OrderService $orders): RedirectResponse
    {
        $this->authorizeOwn($request->user(), $delivery);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_map(fn (OrderStatus $status) => $status->value, OrderStatus::riderSteps()))],
        ]);

        $status = OrderStatus::from($validated['status']);
        $orders->riderStep($delivery->load('order'), $status, $request->user());

        $this->toast($status === OrderStatus::Delivered ? 'Delivered. Thank you!' : "Marked {$status->label()}.");

        return back();
    }

    private function rider(User $user): Rider
    {
        $rider = $user->loadMissing('rider')->rider;
        abort_if($rider === null, 403, 'This account is not linked to a rider.');

        return $rider;
    }

    private function authorizeOwn(User $user, Delivery $delivery): void
    {
        abort_unless($delivery->rider_id === $this->rider($user)->id, 403);
    }

    /**
     * @return Builder<Delivery>
     */
    private function deliveriesOf(Rider $rider)
    {
        return Delivery::query()
            ->with(['order:id,order_number,client_id,order_date,meal_type,quantity,status,delivery_address,delivery_time', 'order.client:id,name'])
            ->where('rider_id', $rider->id);
    }

    /**
     * Only what a rider needs on the road: no prices, no employee data.
     *
     * @return array<string, mixed>
     */
    private function present(Delivery $delivery): array
    {
        $order = $delivery->order;
        $next = array_values(array_filter($order->status->next(), fn (OrderStatus $status) => in_array($status, OrderStatus::riderSteps(), true)));

        return [
            'id' => $delivery->id,
            'order_number' => $order->order_number,
            'order_date' => $order->order_date->toDateString(),
            'client' => $order->client->name,
            'meal_type' => $order->meal_type->value,
            'meal_type_label' => $order->meal_type->label(),
            'quantity' => $order->quantity,
            'delivery_address' => $order->delivery_address,
            'delivery_time' => Time::short($order->delivery_time),
            'status' => $order->status->value,
            'status_label' => $order->status->label(),
            'next_step' => $next ? ['value' => $next[0]->value, 'label' => $next[0]->label()] : null,
            'delivered_at' => $delivery->delivered_at?->toIso8601String(),
        ];
    }
}
