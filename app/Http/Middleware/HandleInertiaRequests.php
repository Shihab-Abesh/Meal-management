<?php

namespace App\Http\Middleware;

use App\Http\Resources\AuthUserResource;
use App\Services\VendorProfileService;
use App\Support\Navigation;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    public function __construct(
        private readonly VendorProfileService $vendorProfiles,
        private readonly Navigation $navigation,
    ) {}

    /**
     * Props available to every page. Keep this payload small and free of
     * sensitive data: it is sent with every response.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'app' => fn () => $this->branding(),
            'auth' => [
                'user' => fn () => $request->user()
                    ? AuthUserResource::make($request->user())->resolve($request)
                    : null,
            ],
            'navigation' => fn () => $this->navigation->for($request),
            // Starting count for the bell; the bell then keeps itself up to date.
            'notifications' => fn () => $request->user()?->isVendorUser()
                ? ['unread' => $request->user()->unreadNotifications()->count()]
                : null,
        ];
    }

    /**
     * Vendor branding shown in the header, sidebar and login screen.
     *
     * @return array{name: string, logo_url: string|null, currency: array{code: string, symbol: string}}
     */
    private function branding(): array
    {
        $vendor = $this->vendorProfiles->current();

        return [
            'name' => $vendor->business_name ?? config('app.name'),
            'logo_url' => $vendor?->logo_url,
            'currency' => config('catering.currency'),
        ];
    }
}
