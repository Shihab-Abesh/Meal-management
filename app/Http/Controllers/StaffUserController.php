<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Http\Requests\StaffUserRequest;
use App\Models\User;
use App\Services\StaffUserService;
use App\Support\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Vendor admin and staff accounts with their permissions. Client logins are
 * managed on the client page and rider logins on the rider page. Routes
 * require the users permission.
 */
class StaffUserController extends Controller
{
    public function __construct(private readonly StaffUserService $users) {}

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in([UserRole::VendorAdmin->value, UserRole::VendorStaff->value])],
            'status' => ['nullable', Rule::enum(RecordStatus::class)],
        ]);

        $users = User::query()
            ->whereIn('role', [UserRole::VendorAdmin, UserRole::VendorStaff])
            ->when($filters['search'] ?? null, fn (Builder $query, string $term) => Search::apply($query, $term, ['name', 'email', 'phone']))
            ->when($filters['role'] ?? null, fn (Builder $query, string $role) => $query->where('role', $role))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderByRaw("CASE role WHEN 'vendor_admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $users->through(fn (User $user) => $this->present($user, $request->user()));

        return Inertia::render('users/index', [
            'users' => JsonResource::collection($users),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'role' => $filters['role'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
            'roles' => $this->roleOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('users/form', [...$this->formOptions(), 'account' => null]);
    }

    public function store(StaffUserRequest $request): RedirectResponse
    {
        $user = $this->users->save(null, $request->safe()->except('permissions'), $request->permissions(), $request->user());

        $this->toast("Account created for {$user->name}.");

        return to_route('users.index');
    }

    public function edit(Request $request, User $user): Response
    {
        abort_unless($user->isVendorUser(), 404);

        return Inertia::render('users/form', [
            ...$this->formOptions(),
            'account' => [
                ...$this->present($user, $request->user()),
                'permissions' => array_map(fn (Permission $permission) => $permission->value, $user->effectivePermissions()),
            ],
        ]);
    }

    public function update(StaffUserRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->isVendorUser(), 404);

        $this->users->save($user, $request->safe()->except('permissions'), $request->permissions(), $request->user());

        $this->toast("{$user->name}'s account has been saved.");

        return to_route('users.index');
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isVendorUser(), 404);

        $validated = $request->validate(['status' => ['required', Rule::enum(RecordStatus::class)]]);
        $status = RecordStatus::from($validated['status']);

        $this->users->setStatus($user, $status, $request->user());

        $this->toast($status === RecordStatus::Active ? "{$user->name} can sign in again." : "{$user->name} has been deactivated and signed out.");

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function present(User $user, User $viewer): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role->value,
            'role_label' => $user->role->label(),
            'status' => $user->status->value,
            'status_label' => $user->status->label(),
            'permissions_count' => count($user->effectivePermissions()),
            'last_login_at' => $user->last_login_at?->toIso8601String(),
            'is_self' => $user->is($viewer),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'roles' => $this->roleOptions(),
            'statuses' => RecordStatus::options(),
            'permissions' => Permission::options(),
            'staffDefaults' => array_map(fn (Permission $permission) => $permission->value, UserRole::VendorStaff->defaultPermissions()),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function roleOptions(): array
    {
        return array_map(
            fn (UserRole $role) => ['value' => $role->value, 'label' => $role->label()],
            [UserRole::VendorAdmin, UserRole::VendorStaff],
        );
    }
}
