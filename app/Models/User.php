<?php

namespace App\Models;

use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Support\Phone;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Role, status, permissions and the owning client are intentionally not
     * mass assignable; they are only changed through dedicated, authorized
     * actions.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'permissions',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'client_id' => 'integer',
            'status' => RecordStatus::class,
            'permissions' => 'array',
            'notification_preferences' => 'array',
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function email(): Attribute
    {
        return Attribute::set(fn (?string $value) => filled($value) ? Str::lower(trim($value)) : null);
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function phone(): Attribute
    {
        return Attribute::set(fn (?string $value) => Phone::normalize($value));
    }

    /**
     * @param  Builder<User>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', RecordStatus::Active);
    }

    public function isActive(): bool
    {
        return $this->status === RecordStatus::Active;
    }

    public function hasRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isVendorAdmin(): bool
    {
        return $this->role === UserRole::VendorAdmin;
    }

    public function isVendorUser(): bool
    {
        return $this->role->isVendor();
    }

    public function hasPermission(Permission $permission): bool
    {
        return in_array($permission, $this->effectivePermissions(), true);
    }

    /**
     * The permissions this user actually holds.
     *
     * A vendor staff member with a stored permission list gets exactly that
     * list, so an admin can both grant critical permissions and revoke
     * operational ones. Without a stored list the role defaults apply.
     * Client and rider accounts are restricted to their own areas
     * regardless of what is stored.
     *
     * @return list<Permission>
     */
    public function effectivePermissions(): array
    {
        if ($this->role !== UserRole::VendorStaff || $this->permissions === null) {
            return $this->role->defaultPermissions();
        }

        $permissions = [];

        foreach ($this->permissions as $granted) {
            $permission = Permission::tryFrom((string) $granted);

            if ($permission && ! in_array($permission, $permissions, true)) {
                $permissions[] = $permission;
            }
        }

        return $permissions;
    }

    /**
     * Alert categories the user can turn off, with their current choice.
     * Everything is on until the user changes it.
     *
     * @return array{orders: bool, deliveries: bool, billing: bool}
     */
    public function alertPreferences(): array
    {
        $stored = $this->notification_preferences ?? [];

        return [
            'orders' => (bool) ($stored['orders'] ?? true),
            'deliveries' => (bool) ($stored['deliveries'] ?? true),
            'billing' => (bool) ($stored['billing'] ?? true),
        ];
    }

    public function wantsAlerts(string $category): bool
    {
        return $this->alertPreferences()[$category] ?? true;
    }

    public function isClientAdmin(): bool
    {
        return $this->role === UserRole::ClientAdmin;
    }

    public function isRider(): bool
    {
        return $this->role === UserRole::Rider;
    }

    /**
     * Whether the account may use the app: the user must be active, and a
     * client account also needs its organization to be active.
     */
    public function hasAccess(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if ($this->isClientAdmin()) {
            $this->loadMissing('client');

            return $this->client?->isActive() ?? false;
        }

        if ($this->isRider()) {
            $this->loadMissing('rider');

            return $this->rider?->isActive() ?? false;
        }

        return true;
    }

    /**
     * The rider profile behind a rider account.
     *
     * @return HasOne<Rider, $this>
     */
    public function rider(): HasOne
    {
        return $this->hasOne(Rider::class);
    }

    /**
     * The client organization a client account belongs to.
     *
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function recordLogin(string $ip): void
    {
        $this->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ])->saveQuietly();
    }
}
