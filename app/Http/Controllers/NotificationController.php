<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Models\User;
use App\Notifications\Vendor\BillsOverdue;
use App\Notifications\Vendor\ClientOrderCancelled;
use App\Notifications\Vendor\ClientOrderChanged;
use App\Notifications\Vendor\ClientOrderPlaced;
use App\Notifications\Vendor\DeliveryCompleted;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The signed-in vendor user's own notifications: the bell's live feed, the
 * full list, read state and which alerts they want. A notification is only
 * ever looked up among the user's own.
 */
class NotificationController extends Controller
{
    /**
     * Alert classes in each category.
     */
    private const CATEGORIES = [
        'orders' => [ClientOrderPlaced::class, ClientOrderChanged::class, ClientOrderCancelled::class],
        'deliveries' => [DeliveryCompleted::class],
        'billing' => [BillsOverdue::class],
    ];

    /**
     * How many recent notifications the bell shows.
     */
    private const FEED_SIZE = 8;

    public function index(Request $request): Response
    {
        $user = $request->user();
        $filters = $request->validate([
            'show' => ['nullable', Rule::in(['all', 'unread'])],
            'category' => ['nullable', Rule::in(array_keys(self::CATEGORIES))],
        ]);

        $notifications = $user->notifications()
            ->when(($filters['show'] ?? 'all') === 'unread', fn (Builder $query) => $query->whereNull('read_at'))
            ->when($filters['category'] ?? null, fn (Builder $query, string $category) => $query->whereIn('type', self::CATEGORIES[$category]))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (DatabaseNotification $notification) => $this->present($notification));

        return Inertia::render('notifications/index', [
            'notifications' => JsonResource::collection($notifications),
            'filters' => ['show' => $filters['show'] ?? 'all', 'category' => $filters['category'] ?? ''],
            'counts' => [
                'all' => $user->notifications()->count(),
                'unread' => $user->unreadNotifications()->count(),
            ],
            'preferences' => $user->alertPreferences(),
            'categories' => $this->availableCategories($user),
        ]);
    }

    /**
     * Unread count and newest notifications, polled by the bell.
     */
    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
            'items' => $user->notifications()->orderByDesc('id')->limit(self::FEED_SIZE)->get()
                ->map(fn (DatabaseNotification $notification) => $this->present($notification))
                ->all(),
        ]);
    }

    /**
     * Mark as read and go to what the notification is about.
     */
    public function open(Request $request, string $notification): RedirectResponse
    {
        $notification = $this->find($request->user(), $notification);
        $notification->markAsRead();

        $url = (string) ($notification->data['url'] ?? '');

        // Only paths inside the app, never another site.
        return str_starts_with($url, '/') && ! str_starts_with($url, '//')
            ? redirect()->to($url)
            : to_route('notifications.index');
    }

    public function update(Request $request, string $notification): JsonResponse|RedirectResponse
    {
        $validated = $request->validate(['read' => ['required', 'boolean']]);
        $notification = $this->find($request->user(), $notification);

        $validated['read'] ? $notification->markAsRead() : $notification->markAsUnread();

        return $this->done($request);
    }

    public function readAll(Request $request): JsonResponse|RedirectResponse
    {
        $count = $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return $this->done($request, $count ? 'All notifications marked as read.' : null);
    }

    public function clearRead(Request $request): RedirectResponse
    {
        $count = $request->user()->readNotifications()->delete();

        $this->toast($count ? "{$count} read notifications cleared." : 'There were no read notifications to clear.', $count ? 'success' : 'info');

        return back();
    }

    public function preferences(Request $request): RedirectResponse
    {
        $user = $request->user();
        $rules = collect($this->availableCategories($user))
            ->mapWithKeys(fn (array $category) => [$category['value'] => ['required', 'boolean']])
            ->all();

        $validated = $request->validate($rules);

        $user->forceFill([
            'notification_preferences' => [...$user->alertPreferences(), ...array_map('boolval', $validated)],
        ])->save();

        $this->toast('Notification settings saved.');

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function present(DatabaseNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'kind' => $notification->data['kind'] ?? 'info',
            'title' => $notification->data['title'] ?? '',
            'body' => $notification->data['body'] ?? '',
            'read' => $notification->read_at !== null,
            'created_at' => $notification->created_at?->toIso8601String(),
        ];
    }

    private function find(User $user, string $id): DatabaseNotification
    {
        return $user->notifications()->whereKey($id)->firstOrFail();
    }

    private function done(Request $request, ?string $message = null): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['unread' => $request->user()->unreadNotifications()->count()]);
        }

        if ($message) {
            $this->toast($message);
        }

        return back();
    }

    /**
     * Alert categories the user can receive, for the preference toggles.
     *
     * @return list<array{value: string, label: string, description: string}>
     */
    private function availableCategories(User $user): array
    {
        return array_values(array_filter([
            $user->hasPermission(Permission::ManageOrders) ? ['value' => 'orders', 'label' => 'Client orders', 'description' => 'When a client places, changes or cancels meals.'] : null,
            $user->hasPermission(Permission::ManageDeliveries) ? ['value' => 'deliveries', 'label' => 'Deliveries', 'description' => 'When an order is delivered.'] : null,
            ($user->hasPermission(Permission::ManageBilling) || $user->hasPermission(Permission::ViewFinancials))
                ? ['value' => 'billing', 'label' => 'Overdue bills', 'description' => 'When bills pass their due date unpaid.'] : null,
        ]));
    }
}
