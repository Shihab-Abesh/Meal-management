<?php

namespace App\Models;

use App\Casts\DateOnly;
use App\Enums\MealType;
use App\Enums\OrderStatus;
use App\Support\Money;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * The meals for one client, date and meal type, from kitchen to delivery.
 * Created and kept in step with the meal sheet by App\Services\OrderSync.
 */
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
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
            'order_date' => DateOnly::class,
            'meal_type' => MealType::class,
            'quantity' => 'integer',
            'amount' => 'decimal:2',
            'status' => OrderStatus::class,
            'cancelled_at' => 'datetime',
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
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->orderBy('sort_order');
    }

    /**
     * @return HasOne<Delivery, $this>
     */
    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    /**
     * @param  Builder<Order>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', OrderStatus::open());
    }

    /**
     * Average price per meal, for display ("22 × ৳120").
     */
    public function unitPrice(): string
    {
        return $this->quantity > 0 ? Money::divide($this->amount, $this->quantity) : '0.00';
    }

    public static function numberFor(int $id, string $date): string
    {
        return sprintf('ORD-%s-%05d', str_replace('-', '', substr($date, 2)), $id);
    }
}
