<?php

namespace App\Services;

use App\Models\VendorProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class VendorProfileService
{
    /**
     * The vendor profile for this installation, or null before it is set up.
     */
    public function current(): ?VendorProfile
    {
        return VendorProfile::query()->oldest('id')->first();
    }

    /**
     * Create or update the single vendor profile.
     *
     * The new logo is stored before the transaction and removed again if the
     * save fails; the previous logo is only deleted once the save succeeded.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function save(array $attributes, ?UploadedFile $logo = null, bool $removeLogo = false): VendorProfile
    {
        $disk = Storage::disk(VendorProfile::FILES_DISK);
        $newLogoPath = $logo?->store(config('catering.logo.directory'), VendorProfile::FILES_DISK);
        $oldLogoPath = null;

        try {
            $profile = DB::transaction(function () use ($attributes, $newLogoPath, $removeLogo, &$oldLogoPath) {
                $profile = VendorProfile::query()->oldest('id')->lockForUpdate()->first() ?? new VendorProfile;
                $oldLogoPath = $profile->logo_path;

                $profile->fill($attributes);

                if ($newLogoPath) {
                    $profile->logo_path = $newLogoPath;
                } elseif ($removeLogo) {
                    $profile->logo_path = null;
                }

                $profile->save();

                return $profile;
            });
        } catch (Throwable $e) {
            if ($newLogoPath) {
                $disk->delete($newLogoPath);
            }

            throw $e;
        }

        if ($oldLogoPath && $oldLogoPath !== $profile->logo_path) {
            $disk->delete($oldLogoPath);
        }

        return $profile;
    }
}
