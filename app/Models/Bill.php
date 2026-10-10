<?php

namespace App\Models;

use App\Casts\DateOnly;
use App\Enums\BillStatus;
use Database\Factories\BillFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * What a client owes for a billing period. Written only by
 * App\Services\BillingService.
 */
class Bill extends Model
{
    /** @use HasFactory<BillFactory> */
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
            'period_start' => DateOnly::class,
            'period_end' => DateOnly::class,
            'issue_date' => DateOnly::class,
            'due_date' => DateOnly::class,
            'lunch_meals' => 'integer',
            'dinner_meals' => 'integer',
            'total_meals' => 'integer',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
            'status' => BillStatus::class,
            'lines' => 'array',
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
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('payment_date')->latest('id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * Bills the user may see: all for vendor users, their own for client
     * accounts, none otherwise. Vendor access is checked by the controller.
     *
     * @param  Builder<Bill>  $query
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
     * Bills with money still owed.
     *
     * @param  Builder<Bill>  $query
     */
    public function scopeOutstanding(Builder $query): void
    {
        $query->where('due_amount', '>', 0);
    }

    /**
     * Filter by status. "Overdue" is worked out from the due date, so it is
     * right even before the nightly job has updated the stored status.
     *
     * @param  Builder<Bill>  $query
     */
    public function scopeWithStatus(Builder $query, BillStatus $status, string $today): void
    {
        match ($status) {
            BillStatus::Paid => $query->where('due_amount', '<=', 0),
            BillStatus::Overdue => $query->where('due_amount', '>', 0)->where('due_date', '<', $today),
            BillStatus::PartiallyPaid => $query->where('due_amount', '>', 0)->where('paid_amount', '>', 0)->where('due_date', '>=', $today),
            BillStatus::Unpaid => $query->where('due_amount', '>', 0)->where('paid_amount', '<=', 0)->where('due_date', '>=', $today),
        };
    }

    /**
     * The status as of today (a stored "unpaid" bill past its due date is
     * shown as overdue).
     */
    public function currentStatus(?string $today = null): BillStatus
    {
        return BillStatus::resolve($this->total_amount, $this->paid_amount, $this->due_date->toDateString(), $today ?? now()->toDateString());
    }

    public static function numberFor(int $id, string $issueDate): string
    {
        return sprintf('BILL-%s-%05d', str_replace('-', '', substr($issueDate, 2, 5)), $id);
    }
}
