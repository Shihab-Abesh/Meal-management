<?php

namespace App\Models;

use App\Casts\DateOnly;
use App\Enums\MealEntryStatus;
use App\Enums\MealType;
use Database\Factories\MealEntryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One meal for one employee on one date. Written only by MealEntryService.
 */
class MealEntry extends Model
{
    /** @use HasFactory<MealEntryFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'client_id' => 'integer',
            'employee_id' => 'integer',
            'meal_date' => DateOnly::class,
            'meal_type' => MealType::class,
            'status' => MealEntryStatus::class,
            'unit_price' => 'decimal:2',
            'confirmed_at' => 'datetime',
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
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by')->withTrashed();
    }

    /**
     * @param  Builder<MealEntry>  $query
     */
    public function scopeConfirmed(Builder $query): void
    {
        $query->where('status', MealEntryStatus::Confirmed);
    }

    /**
     * Only the meals the user may see (same rule as employees).
     *
     * @param  Builder<MealEntry>  $query
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
}
