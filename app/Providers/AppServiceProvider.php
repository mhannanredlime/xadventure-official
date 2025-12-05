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
        // Register custom asset helper with versioning
        Blade::directive('versionedAsset', function ($expression) {
            return "<?php echo App\Helpers\AssetHelper::versioned($expression); ?>";
        });
        Reservation::observe(ReservationObserver::class);

    }
}
