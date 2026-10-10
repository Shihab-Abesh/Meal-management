<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Http\Requests\UpdateVendorProfileRequest;
use App\Http\Resources\VendorProfileResource;
use App\Services\ActivityLogger;
use App\Services\VendorProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorProfileController extends Controller
{
    public function __construct(private readonly VendorProfileService $vendorProfiles) {}

    public function show(Request $request): Response
    {
        $profile = $this->vendorProfiles->current();

        return Inertia::render('vendor-profile/show', [
            'profile' => $profile ? VendorProfileResource::make($profile)->resolve($request) : null,
            'canManage' => $request->user()->can(Permission::ManageVendorProfile->value),
        ]);
    }

    public function edit(Request $request): Response
    {
        $profile = $this->vendorProfiles->current();

        return Inertia::render('vendor-profile/edit', [
            'profile' => $profile ? VendorProfileResource::make($profile)->resolve($request) : null,
            'businessTypes' => config('catering.business_types'),
            'statuses' => RecordStatus::options(),
            'logoRules' => [
                'max_kb' => config('catering.logo.max_kb'),
                'mimes' => config('catering.logo.mimes'),
            ],
        ]);
    }

    public function update(UpdateVendorProfileRequest $request, ActivityLogger $activity): RedirectResponse
    {
        $profile = $this->vendorProfiles->save(
            $request->profileAttributes(),
            $request->file('logo'),
            $request->boolean('remove_logo'),
        );

        if ($profile->wasRecentlyCreated || ($changes = $activity->changes($profile))) {
            $activity->log('settings', 'vendor_profile_updated', 'Updated the vendor profile', $profile, $changes ?? []);
        }

        $this->toast('Vendor profile saved.');

        return to_route('vendor-profile.show');
    }
}
