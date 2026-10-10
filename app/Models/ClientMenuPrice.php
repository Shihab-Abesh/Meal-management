<?php

namespace App\Models;

use App\Casts\DateOnly;
use App\Enums\MealType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A client's price per meal, valid from `effective_from` until a later
 * price for the same meal type takes over.
 */
class ClientMenuPrice extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'meal_type',
        'price',
        'effective_from',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'client_id' => 'integer',
            'meal_type' => MealType::class,
            'price' => 'decimal:2',
            'effective_from' => DateOnly::class,
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
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
