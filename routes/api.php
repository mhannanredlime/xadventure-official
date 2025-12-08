<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\PackageController; // Assuming exists or added
use App\Http\Controllers\Api\AvailabilityController; // Assuming exists or added

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public Routes
Route::prefix('v1')->group(function () {
    // Packages
    // Route::get('/packages', [PackageController::class, 'index']);
    // Route::get('/packages/{id}', [PackageController::class, 'show']);

    // Availability
    // Route::get('/availability/check', [AvailabilityController::class, 'check']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'store']);
    Route::delete('/cart/remove/{key}', [CartController::class, 'destroy']);

    // Checkout
    Route::post('/checkout', [CheckoutController::class, 'process']);
});
