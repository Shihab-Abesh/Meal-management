<?php

namespace App\Models;

use App\Enums\MealType;
use App\Enums\RecordStatus;
use App\Support\Search;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A client organization (office) the vendor supplies meals to.
 */
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'delivery_address',
        'employee_count',
        'lunch_enabled',
        'dinner_enabled',
        'office_start_time',
        'lunch_cutoff_time',
        'dinner_cutoff_time',
        'special_instructions',
        'status',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RecordStatus::class,
            'lunch_enabled' => 'boolean',
            'dinner_enabled' => 'boolean',
            'employee_count' => 'integer',
        ];
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<Employee, $this>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Client-specific meal prices, including past and scheduled ones.
     *
     * @return HasMany<ClientMenuPrice, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ClientMenuPrice::class);
    }

    /**
     * @return HasMany<Bill, $this>
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @param  Builder<Client>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', RecordStatus::Active);
    }

    /**
     * @param  Builder<Client>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        Search::apply($query, $term, ['name', 'contact_person', 'phone', 'email', 'city']);
    }

    /**
     * @param  Builder<Client>  $query
     */
    public function scopeServing(Builder $query, MealType $type): void
    {
        $query->where($type === MealType::Lunch ? 'lunch_enabled' : 'dinner_enabled', true);
    }

    public function isActive(): bool
    {
        return $this->status === RecordStatus::Active && ! $this->trashed();
    }

    public function serves(MealType $type): bool
    {
        return $type === MealType::Lunch ? $this->lunch_enabled : $this->dinner_enabled;
    }

    /**
     * @return list<MealType>
     */
    public function mealTypes(): array
    {
        return array_values(array_filter(MealType::cases(), fn (MealType $type) => $this->serves($type)));
    }

    /**
     * Where riders take the meals: the delivery address, or the office.
     */
    public function deliveryAddress(): string
    {
        return filled($this->delivery_address) ? $this->delivery_address : $this->address;
    }
}
