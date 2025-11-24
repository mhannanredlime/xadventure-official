<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register permission gates
        Gate::before(function ($user, $ability) {
            if ($user && $user->hasPermission($ability)) {
                return true;
            }
        });
    }
}
