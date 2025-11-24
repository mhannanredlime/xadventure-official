<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
    }
}
