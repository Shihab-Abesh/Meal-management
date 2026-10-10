<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Records important actions to the activity log.
 *
 * The acting user and IP address come from the current request. The client
 * a record belongs to is detected from the subject, so client detail pages
 * can show their own recent activity.
 */
class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function log(
        string $module,
        string $action,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?User $user = null,
    ): ActivityLog {
        // Resolved per call so the service is safe to reuse across requests.
        $request = request();
        $user ??= $request->user();

        return ActivityLog::create([
            'user_id' => $user?->getKey(),
            'module' => $module,
            'action' => $action,
            'description' => mb_substr($description, 0, 500),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'client_id' => $this->clientIdFor($subject),
            'properties' => $properties ?: null,
            'ip_address' => $request->ip(),
        ]);
    }

    /**
     * Old and new values of the attributes that changed in the last save,
     * ready to store as log properties.
     *
     * @param  list<string>  $ignore
     * @return array{old?: array<string, mixed>, new?: array<string, mixed>}
     */
    public function changes(Model $model, array $ignore = ['updated_at']): array
    {
        $new = array_diff_key($model->getChanges(), array_flip($ignore));

        if ($new === []) {
            return [];
        }

        $old = array_intersect_key($model->getPrevious(), $new);

        return ['old' => $old, 'new' => $new];
    }

    private function clientIdFor(?Model $subject): ?int
    {
        if ($subject instanceof Client) {
            return $subject->getKey();
        }

        $clientId = $subject?->getAttributes()['client_id'] ?? null;

        return $clientId === null ? null : (int) $clientId;
    }
}
