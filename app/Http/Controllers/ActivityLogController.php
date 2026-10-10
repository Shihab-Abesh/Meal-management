<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\User;
use App\Support\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The audit trail: who did what, to which record, when and from where.
 * Routes require the activity log permission.
 */
class ActivityLogController extends Controller
{
    /**
     * Module keys as stored, with readable names.
     */
    private const MODULES = [
        'auth' => 'Sign-in',
        'clients' => 'Clients',
        'client_users' => 'Client logins',
        'employees' => 'Employees',
        'meals' => 'Meals',
        'menu' => 'Menu',
        'pricing' => 'Pricing',
        'orders' => 'Orders',
        'deliveries' => 'Deliveries',
        'riders' => 'Riders',
        'billing' => 'Billing',
        'reports' => 'Reports',
        'settings' => 'Settings',
        'users' => 'Users',
        'profile' => 'Own profile',
    ];

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'module' => ['nullable', 'string', 'max:40'],
            'user' => ['nullable', 'integer'],
            'client' => ['nullable', 'integer'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $from = $filters['from'] ?? now()->subDays(29)->toDateString();
        $to = $filters['to'] ?? now()->toDateString();

        $logs = ActivityLog::query()
            ->with(['user:id,name,role', 'client:id,name'])
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $term) => Search::apply($query, $term, ['description']))
            ->when($filters['module'] ?? null, fn (Builder $query, string $module) => $query->where('module', $module))
            ->when($filters['user'] ?? null, fn (Builder $query, int $id) => $query->where('user_id', $id))
            ->when($filters['client'] ?? null, fn (Builder $query, int $id) => $query->where('client_id', $id))
            ->latest('id')
            ->paginate(30)
            ->withQueryString()
            ->through(fn (ActivityLog $log) => [
                'id' => $log->id,
                'module' => $log->module,
                'module_label' => self::MODULES[$log->module] ?? ucfirst(str_replace('_', ' ', $log->module)),
                'action' => $log->action,
                'description' => $log->description,
                'user' => $log->user ? ['name' => $log->user->name, 'role' => $log->user->role->label()] : null,
                'client' => $log->client?->name,
                'record' => $log->subject_type ? class_basename($log->subject_type).' #'.$log->subject_id : null,
                'properties' => $log->properties,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at?->toIso8601String(),
            ]);

        return Inertia::render('activity-logs/index', [
            'logs' => JsonResource::collection($logs),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'module' => $filters['module'] ?? '',
                'user' => isset($filters['user']) ? (string) $filters['user'] : '',
                'client' => isset($filters['client']) ? (string) $filters['client'] : '',
                'from' => $from,
                'to' => $to,
            ],
            'modules' => collect(self::MODULES)->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])->values(),
            'users' => User::withTrashed()
                ->whereIn('role', [UserRole::VendorAdmin, UserRole::VendorStaff, UserRole::ClientAdmin, UserRole::Rider])
                ->orderBy('name')
                ->get(['id', 'name', 'role'])
                ->map(fn (User $user) => ['value' => (string) $user->id, 'label' => "{$user->name} ({$user->role->label()})"]),
            'clients' => Client::withTrashed()->orderBy('name')->get(['id', 'name'])
                ->map(fn (Client $client) => ['value' => (string) $client->id, 'label' => $client->name]),
        ]);
    }
}
