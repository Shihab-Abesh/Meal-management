<?php

namespace App\Models;

use App\Casts\DateOnly;
use App\Enums\MealType;
use Database\Factories\DailyMenuFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * What is served for one meal on one date, e.g. lunch on 06 Oct 2026:
 * Rice, Chicken Curry, Dal, Vegetable.
 */
class DailyMenu extends Model
{
    /** @use HasFactory<DailyMenuFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'menu_date',
        'meal_type',
        'price',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'menu_date' => DateOnly::class,
            'meal_type' => MealType::class,
            'price' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsToMany<MenuItem, $this>
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'daily_menu_items')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
