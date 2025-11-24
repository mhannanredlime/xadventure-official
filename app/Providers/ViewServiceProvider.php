<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\CartService;

class ViewServiceProvider extends ServiceProvider
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
        // Share cart count with all views
        View::composer('*', function ($view) {
            $cartService = app(CartService::class);
            $view->with('cartCount', $cartService->getCartCount());
            $view->with('cartTotalItems', $cartService->getCartTotalItems());
        });
    }
}
