<?php

namespace App\Services;

use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Vendor-side accounts. Admins hold every permission; staff hold exactly
 * the permissions ticked for them. Nobody can lock themselves out, and
 * there is always at least one active admin.
 */
class StaffUserService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    /**
     * @param  array{name: string, email: ?string, phone: ?string, password: ?string, role: string, status: string}  $data
     * @param  list<Permission>  $permissions
     *
     * @throws ValidationException
     */
    public function save(?User $user, array $data, array $permissions, User $actor): User
    {
        return DB::transaction(function () use ($user, $data, $permissions, $actor) {
            $creating = $user === null;
            $role = UserRole::from($data['role']);
            $status = RecordStatus::from($data['status']);

            if (! $creating) {
                $this->guardChanges($user, $role, $status, $actor);
            }

            $user ??= new User;
            $user->fill(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone']]);

            if (filled($data['password'] ?? null)) {
                $user->password = $data['password'];
            }

            $user->forceFill([
                'role' => $role,
                'status' => $status,
                'client_id' => null,
                // Admins get everything from their role; staff get the exact list.
                'permissions' => $role === UserRole::VendorStaff ? array_map(fn (Permission $p) => $p->value, $permissions) : null,
            ]);

            if ($creating) {
                $user->email_verified_at = now();
            }

            $user->save();

            if ($creating) {
                $this->activity->log('users', 'created', "Created {$role->label()} account for {$user->name}", $user, ['role' => $role->value], $actor);
            } elseif ($changes = $this->activity->changes($user, ['updated_at', 'password', 'remember_token'])) {
                $this->activity->log('users', 'updated', "Updated account of {$user->name}", $user, [
                    ...$changes,
                    'password_changed' => $user->wasChanged('password'),
                ], $actor);
            } elseif ($user->wasChanged('password')) {
                $this->activity->log('users', 'password_reset', "Reset the password of {$user->name}", $user, [], $actor);
            }

            return $user;
        });
    }

    /**
     * @throws ValidationException
     */
    public function setStatus(User $user, RecordStatus $status, User $actor): void
    {
        if ($user->status === $status) {
            return;
        }

        DB::transaction(function () use ($user, $status, $actor) {
            $this->guardChanges($user, $user->role, $status, $actor);

            $user->forceFill(['status' => $status])->save();

            $verb = $status === RecordStatus::Active ? 'Activated' : 'Deactivated';
            $this->activity->log('users', 'status_changed', "{$verb} the account of {$user->name}", $user, ['status' => $status->value], $actor);
        });
    }

    /**
     * @throws ValidationException
     */
    private function guardChanges(User $user, UserRole $role, RecordStatus $status, User $actor): void
    {
        if ($user->is($actor) && ($role !== $user->role || $status !== RecordStatus::Active)) {
            throw ValidationException::withMessages(['role' => 'You cannot change your own role or deactivate your own account.']);
        }

        $losesAdmin = $user->role === UserRole::VendorAdmin
            && $user->isActive()
            && ($role !== UserRole::VendorAdmin || $status !== RecordStatus::Active);

        if ($losesAdmin) {
            $otherAdmins = User::query()
                ->where('role', UserRole::VendorAdmin)
                ->where('status', RecordStatus::Active)
                ->whereKeyNot($user->id)
                ->lockForUpdate()
                ->count();

            if ($otherAdmins === 0) {
                throw ValidationException::withMessages(['role' => 'There must always be at least one active vendor admin.']);
            }
        }
    }
}
