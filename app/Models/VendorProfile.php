<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Database\Factories\VendorProfileFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * The single catering business that owns this installation.
 */
class VendorProfile extends Model
{
    /** @use HasFactory<VendorProfileFactory> */
    use HasFactory;

    /**
     * Uploaded files live on the public disk, served from /storage.
     */
    public const FILES_DISK = 'public';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'business_name',
        'organization_name',
        'owner_name',
        'phone',
        'email',
        'address',
        'city',
        'business_type',
        'vat_tin',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RecordStatus::class,
        ];
    }

    /**
     * Public URL of the uploaded logo, resolved against the current request
     * host so it works whatever URL the app is served from.
     *
     * @return Attribute<string|null, never>
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->logo_path ? asset('storage/'.$this->logo_path) : null,
        );
    }
}
