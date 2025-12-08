<?php

namespace App\Providers;

use App\Models\Reservation;
use Illuminate\Support\Facades\Blade;
use App\Observers\ReservationObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Blade Directive: Only register this when not running in the console (during cache clearing)
        if (! $this->app->runningInConsole()) { // <--- ADD THIS LINE
            // Register custom asset helper with versioning
            Blade::directive('versionedAsset', function ($expression) {
                return "<?php echo App\Helpers\AssetHelper::versioned($expression); ?>";
            });
        }
        
        // 2. Observer: This is fine to run always
        Reservation::observe(ReservationObserver::class);
    }
}