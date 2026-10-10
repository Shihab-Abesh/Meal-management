<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Writes to client organizations, each recorded in the activity log.
 */
class ClientService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes, User $creator): Client
    {
        return DB::transaction(function () use ($attributes, $creator) {
            $client = new Client($attributes);
            $client->created_by = $creator->id;
            $client->save();

            $this->activity->log('clients', 'created', "Created client {$client->name}", $client);

            return $client;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Client $client, array $attributes): Client
    {
        return DB::transaction(function () use ($client, $attributes) {
            $client->fill($attributes)->save();

            if ($changes = $this->activity->changes($client)) {
                $this->activity->log('clients', 'updated', "Updated client {$client->name}", $client, $changes);
            }

            return $client;
        });
    }

    public function setStatus(Client $client, RecordStatus $status): void
    {
        if ($client->status === $status) {
            return;
        }

        DB::transaction(function () use ($client, $status) {
            $client->update(['status' => $status]);

            $verb = $status === RecordStatus::Active ? 'Activated' : 'Deactivated';
            $this->activity->log('clients', 'status_changed', "{$verb} client {$client->name}", $client, ['status' => $status->value]);
        });
    }

    /**
     * Archive (soft delete). The client's users lose access immediately.
     */
    public function archive(Client $client): void
    {
        DB::transaction(function () use ($client) {
            $client->delete();

            $this->activity->log('clients', 'archived', "Archived client {$client->name}", $client);
        });
    }

    public function restore(Client $client): void
    {
        DB::transaction(function () use ($client) {
            $client->restore();

            $this->activity->log('clients', 'restored', "Restored client {$client->name}", $client);
        });
    }
}
