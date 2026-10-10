<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SecurityHeaders::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            EnsureUserIsActive::class,
        ]);

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Friendly pages instead of bare error screens. 500s keep the debug
        // page while APP_DEBUG is on so problems can still be diagnosed.
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            $status = $response->getStatusCode();
            $friendly = [403, 404, 429, 503, ...(config('app.debug') ? [] : [500])];

            // A form sent after the session expired or too quickly: go back to it with a message.
            if (in_array($status, [419, 429], true) && ! $request->isMethod('GET') && $request->hasSession()) {
                Inertia::flash('toast', [
                    'type' => 'error',
                    'message' => $status === 419
                        ? 'Your session expired, so nothing was saved. Please try again.'
                        : 'Too many attempts. Please wait a minute and try again.',
                ]);

                return back();
            }

            if (in_array($status, [...$friendly, 419], true) && ! $request->expectsJson()) {
                return Inertia::render('error', ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }

            return $response;
        });
    })->create();
