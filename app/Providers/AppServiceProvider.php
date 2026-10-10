<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Models\User;
use App\Services\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One instance per request (or job), so settings are read once but
        // never go stale in long-running workers.
        $this->app->scoped(Settings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureModels();
        $this->configureSecurity();
        $this->registerPermissionGates();

        Vite::prefetch(concurrency: 3);
    }

    /**
     * Outside production, fail loudly on lazy loading (N+1 queries),
     * unknown attributes and silently discarded mass assignment.
     */
    private function configureModels(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
    }

    private function configureSecurity(): void
    {
        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        // Block migrate:fresh, db:wipe and similar commands on production data.
        DB::prohibitDestructiveCommands($this->app->isProduction());
    }

    /**
     * Each permission becomes a Gate ability, usable via $user->can(),
     * the `can:` middleware and policies.
     */
    private function registerPermissionGates(): void
    {
        foreach (Permission::cases() as $permission) {
            Gate::define($permission->value, fn (User $user) => $user->hasPermission($permission));
        }
    }
}
