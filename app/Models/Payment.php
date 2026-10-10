<?php

namespace App\Models;

use App\Casts\DateOnly;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Money received against a bill. Written only by App\Services\BillingService;
 * removed payments are soft deleted.
 */
class Payment extends Model
{
    use SoftDeletes;

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
            'bill_id' => 'integer',
            'client_id' => 'integer',
            'payment_date' => DateOnly::class,
            'amount' => 'decimal:2',
            'method' => PaymentMethod::class,
        ];
    }

    /**
     * @return BelongsTo<Bill, $this>
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
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
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by')->withTrashed();
    }

    /**
     * @param  Builder<Payment>  $query
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
