<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminPackageController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    
    // Dashboard (Placeholder)
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Packages
    Route::resource('packages', AdminPackageController::class);

    // Bookings
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::put('/bookings/{id}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.update-status');

    // Vehicles (Placeholder for VehicleResource)
    // Route::resource('vehicles', AdminVehicleController::class);
});
