<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Writes to riders. A rider's login account (role "rider") follows the
 * rider's name, phone and status.
 */
class RiderService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function save(?Rider $rider, array $attributes, bool $createLogin, ?string $password, User $actor): Rider
    {
        return DB::transaction(function () use ($rider, $attributes, $createLogin, $password, $actor) {
            $creating = $rider === null;
            $rider ??= new Rider;
            $rider->fill($attributes);

            if ($creating) {
                $rider->created_by = $actor->id;
            }

            $rider->save();
            $rider->load('user');

            if ($rider->user) {
                $this->syncLogin($rider, $password);
            } elseif ($createLogin && $password) {
                $this->createLogin($rider, $password);
            }

            if ($creating) {
                $this->activity->log('riders', 'created', "Added rider {$rider->name}", $rider);
            } elseif ($changes = $this->activity->changes($rider)) {
                $this->activity->log('riders', 'updated', "Updated rider {$rider->name}", $rider, $changes);
            }

            return $rider;
        });
    }

    public function setStatus(Rider $rider, RecordStatus $status): void
    {
        if ($rider->status === $status) {
            return;
        }

        DB::transaction(function () use ($rider, $status) {
            $rider->update(['status' => $status]);
            $rider->load('user');

            if ($rider->user) {
                $this->syncLogin($rider, null);
            }

            $verb = $status === RecordStatus::Active ? 'Activated' : 'Deactivated';
            $this->activity->log('riders', 'status_changed', "{$verb} rider {$rider->name}", $rider, ['status' => $status->value]);
        });
    }

    private function createLogin(Rider $rider, string $password): void
    {
        $user = new User(['name' => $rider->name, 'phone' => $rider->phone, 'password' => $password]);
        $user->forceFill(['role' => UserRole::Rider, 'status' => $rider->status])->save();

        $rider->user()->associate($user)->save();

        $this->activity->log('riders', 'login_created', "Created a login for rider {$rider->name}", $rider);
    }

    private function syncLogin(Rider $rider, ?string $password): void
    {
        $user = $rider->user;
        $user->fill(['name' => $rider->name, 'phone' => $rider->phone]);
        $user->status = $rider->status;

        if ($password) {
            $user->password = $password;
        }

        $user->save();
    }
}
