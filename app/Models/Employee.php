<?php

namespace App\Models;

use App\Casts\DateOnly;
use App\Enums\MealPreference;
use App\Enums\MealType;
use App\Enums\RecordStatus;
use App\Support\Search;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An employee of a client organization who can receive meals.
 */
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    /**
     * client_id is not fillable: it is set from the authorized context,
     * never from request data.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_code',
        'name',
        'phone',
        'email',
        'department',
        'designation',
        'meal_preference',
        'lunch_enabled',
        'dinner_enabled',
        'status',
        'joining_date',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'client_id' => 'integer',
            'meal_preference' => MealPreference::class,
            'status' => RecordStatus::class,
            'lunch_enabled' => 'boolean',
            'dinner_enabled' => 'boolean',
            'joining_date' => DateOnly::class,
        ];
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    /**
     * Only the employees the given user may see: every employee for vendor
     * users, their own organization's for client accounts, none otherwise.
     *
     * @param  Builder<Employee>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if ($user->isVendorUser()) {
            return;
        }

        if ($user->isClientAdmin()) {
            $query->where('client_id', $user->client_id);

            return;
        }

        $query->whereRaw('1 = 0');
    }

    /**
     * @param  Builder<Employee>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', RecordStatus::Active);
    }

    /**
     * @param  Builder<Employee>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        Search::apply($query, $term, ['name', 'employee_code', 'phone', 'email', 'department', 'designation']);
    }

    /**
     * @param  Builder<Employee>  $query
     */
    public function scopeTakes(Builder $query, MealType $type): void
    {
        $query->where($type === MealType::Lunch ? 'lunch_enabled' : 'dinner_enabled', true);
    }

    public function isActive(): bool
    {
        return $this->status === RecordStatus::Active;
    }

    public function takes(MealType $type): bool
    {
        return $type === MealType::Lunch ? $this->lunch_enabled : $this->dinner_enabled;
    }
}
