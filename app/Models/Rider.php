<?php

namespace App\Models;

use App\Casts\DateOnly;
use App\Enums\OrderStatus;
use App\Enums\RecordStatus;
use App\Enums\VehicleType;
use App\Support\Phone;
use App\Support\Search;
use Database\Factories\RiderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A delivery rider. May have a login (user with the rider role) to use the
 * rider panel.
 */
class Rider extends Model
{
    /** @use HasFactory<RiderFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'nid',
        'address',
        'emergency_contact',
        'vehicle_type',
        'vehicle_number',
        'joining_date',
        'status',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'vehicle_type' => VehicleType::class,
            'status' => RecordStatus::class,
            'joining_date' => DateOnly::class,
        ];
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function phone(): Attribute
    {
        return Attribute::set(fn (?string $value) => Phone::normalize($value));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return HasMany<Delivery, $this>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    /**
     * Deliveries assigned to the rider that are not finished yet.
     *
     * @return HasMany<Delivery, $this>
     */
    public function openDeliveries(): HasMany
    {
        return $this->deliveries()->whereHas('order', fn (Builder $query) => $query->whereIn('status', [
            OrderStatus::Assigned, OrderStatus::PickedUp, OrderStatus::OnTheWay,
        ]));
    }

    /**
     * @param  Builder<Rider>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', RecordStatus::Active);
    }

    /**
     * @param  Builder<Rider>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        Search::apply($query, $term, ['name', 'phone', 'vehicle_number', 'nid']);
    }

    public function isActive(): bool
    {
        return $this->status === RecordStatus::Active;
    }
}
