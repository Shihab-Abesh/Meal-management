<?php

namespace App\Models;

use App\Enums\MenuCategory;
use App\Enums\RecordStatus;
use App\Support\Search;
use Database\Factories\MenuItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A dish or drink that can be put on a daily menu (Rice, Chicken Curry, Dal…).
 */
class MenuItem extends Model
{
    /** @use HasFactory<MenuItemFactory> */
    use HasFactory;

    public const FILES_DISK = 'public';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'category',
        'description',
        'default_price',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => MenuCategory::class,
            'status' => RecordStatus::class,
            'default_price' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsToMany<DailyMenu, $this>
     */
    public function dailyMenus(): BelongsToMany
    {
        return $this->belongsToMany(DailyMenu::class, 'daily_menu_items');
    }

    /**
     * @param  Builder<MenuItem>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', RecordStatus::Active);
    }

    /**
     * @param  Builder<MenuItem>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        Search::apply($query, $term, ['name', 'description']);
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image_path ? asset('storage/'.$this->image_path) : null);
    }
}
