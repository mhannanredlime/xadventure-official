<?php

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\SlotPresetController;
use App\Http\Controllers\Admin\CustomerContactManage;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\VehicleTypeController;
use App\Http\Controllers\Admin\AvailabilityController;
use App\Http\Controllers\Admin\VehicleAvailabilityController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;

/*
|--------------------------------------------------------------------------
| Backend Routes (Admin)
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider with the "web"
| middleware group and "admin" prefix/name as configured in bootstrap/app.php.
|
*/

// Dashboard
Route::get('/', function () {
    return view('admin.dashboard');
})->name('dashboard');

// Vehicle Types
Route::middleware(['permission:vehicle-types.manage'])->group(function () {
    Route::resource('vehicle-types', VehicleTypeController::class);
});

// Vehicles
Route::middleware(['permission:vehicles.manage'])->group(function () {
    Route::resource('vehicles', VehicleController::class);
    Route::patch('vehicles/{vehicle}/toggle', [VehicleController::class, 'toggleStatus'])->name('vehicles.toggle');
});

// Packages
Route::middleware(['permission:packages.manage'])->group(function () {
    Route::resource('packages', PackageController::class);
    
    // ATV/UTV Packages
    Route::get('/packages/atv-utv/create', [PackageController::class, 'createAtvUtv'])->name('packages.atv-utv.create');
    Route::post('/packages/atv-utv', [PackageController::class, 'storeAtvUtv'])->name('packages.atv-utv.store');
    Route::get('/packages/atv-utv/{package}/edit', [PackageController::class, 'editAtvUtv'])->name('packages.atv-utv.edit');
    Route::put('/packages/atv-utv/{package}', [PackageController::class, 'updateAtvUtv'])->name('packages.atv-utv.update');

    // Regular Packages
    Route::get('/packages/regular/create', [PackageController::class, 'createRegular'])->name('packages.regular.create');
    Route::post('/packages/regular', [PackageController::class, 'storeRegular'])->name('packages.regular.store');
    Route::get('/packages/regular/{package}/edit', [PackageController::class, 'editRegular'])->name('packages.regular.edit');
    Route::put('/packages/regular/{package}', [PackageController::class, 'updateRegular'])->name('packages.regular.update');
});

    // Promo Codes
    Route::middleware(['permission:promo-codes.manage'])->group(function () {
        Route::resource('promo-codes', PromoCodeController::class);
        Route::patch('promo-codes/{promoCode}/toggle', [PromoCodeController::class, 'toggleStatus'])->name('promo.codes.toggle');
        Route::get('promo-codes/filter', [PromoCodeController::class, 'getPromoCodes'])->name('promo.codes.filter');
        Route::post('promo-codes/validate', [PromoCodeController::class, 'validateCode'])->name('promo.codes.validate');
    });

// Reservations
Route::middleware(['permission:reservations.view'])->group(function () {
    Route::resource('reservations', ReservationController::class);
    Route::get('reservations/history/view', [ReservationController::class, 'history'])->name('reservations.history');
    Route::get('reservations/export/pending', [ReservationController::class, 'exportPending'])->name('reservations.export.pending');
    Route::get('reservations/export/history', [ReservationController::class, 'exportHistory'])->name('reservations.export.history');
});

// Customer Contacts
Route::middleware(['permission:contacts.view'])->group(function () {
    Route::get('customer/contacts', [CustomerContactManage::class, 'contacts'])->name('customer.contacts');
});

// Vehicle Availability
Route::middleware(['permission:vehicles.view'])->group(function () {
    Route::get('vehicle-availability', [VehicleAvailabilityController::class, 'index'])->name('vehicle-availability.index');
    Route::get('vehicle-availability/data', [VehicleAvailabilityController::class, 'getAvailabilityForDate'])->name('vehicle-availability.data');
    Route::get('vehicle-availability/breakdown', [VehicleAvailabilityController::class, 'getVehicleTypeBreakdown'])->name('vehicle-availability.breakdown');
});

// Availability
Route::middleware(['permission:calendar.manage'])->group(function () {
    Route::get('availabilities', [AvailabilityController::class, 'index'])->name('availabilities.index');
    Route::post('availabilities', [AvailabilityController::class, 'store'])->name('availabilities.store');
    Route::post('availabilities/bulk', [AvailabilityController::class, 'bulkUpdate'])->name('availabilities.bulk');
    Route::delete('availabilities/{availability}', [AvailabilityController::class, 'destroy'])->name('availabilities.destroy');
});

// Calendar
Route::middleware(['permission:calendar.manage'])->group(function () {
    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendar/test-api', [CalendarController::class, 'testApi'])->name('calendar.test-api');
    Route::get('calendar/package/{package}/data', [CalendarController::class, 'getPackageData'])->name('calendar.package.data');
    Route::post('calendar/availability', [CalendarController::class, 'getAvailabilityForDate'])->name('calendar.availability');
    Route::post('calendar/availability/update', [CalendarController::class, 'updateAvailability'])->name('calendar.availability.update');
    Route::post('calendar/availability/slot/update', [CalendarController::class, 'updateSlotAvailability'])->name('calendar.availability.slot.update');
    Route::post('calendar/availability/bulk', [CalendarController::class, 'bulkUpdate'])->name('calendar.availability.bulk');
    Route::post('calendar/update-price', [CalendarController::class, 'updatePrice'])->name('calendar.update-price');
    Route::post('calendar/ensure-original-amounts', [CalendarController::class, 'ensureOriginalAmounts'])->name('calendar.ensure-original-amounts');
    Route::post('calendar/reset-pricing', [CalendarController::class, 'resetPricingForDate'])->name('calendar.reset-pricing');
    Route::get('calendar/vehicle-availability', [CalendarController::class, 'getVehicleAvailability'])->name('calendar.vehicle-availability');
    Route::get('calendar/slot-presets', [CalendarController::class, 'getSlotPresets'])->name('calendar.slot-presets');
    Route::post('calendar/slot-presets/override', [CalendarController::class, 'setSlotPresetOverride'])->name('calendar.slot-presets.override');
});

// Images
Route::middleware(['permission:gallery.manage'])->group(function () {
    Route::post('images/{image}/primary', [ImageController::class, 'setPrimary'])->name('images.primary');
    Route::post('images/reorder', [ImageController::class, 'reorder'])->name('images.reorder');
    Route::put('images/{image}/alt-text', [ImageController::class, 'updateAltText'])->name('images.alt-text');
    Route::delete('images/{image}', [ImageController::class, 'destroy'])->name('images.destroy');
    Route::get('images', [ImageController::class, 'getImages'])->name('images.get');
});

// Gallery
Route::middleware(['permission:gallery.manage'])->group(function () {
    Route::get('gallery', [GalleryController::class, 'index'])->name('gallery.index');
    Route::post('gallery/upload', [GalleryController::class, 'upload'])->name('gallery.upload');
    Route::get('gallery/images', [GalleryController::class, 'getImages'])->name('gallery.get');
    Route::get('gallery/{gallery}', [GalleryController::class, 'show'])->name('gallery.show');
    Route::put('gallery/{gallery}', [GalleryController::class, 'update'])->name('gallery.update');
    Route::delete('gallery/{gallery}', [GalleryController::class, 'destroy'])->name('gallery.destroy');
    Route::post('gallery/{gallery}/copy', [GalleryController::class, 'copyToLocation'])->name('gallery.copy');
});

// Reports
Route::middleware(['permission:analytics.view'])->group(function () {
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportsController::class, 'export'])->name('reports.export');
});

// Profile Routes (accessible to all admin users)
Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

// Settings Routes
Route::middleware(['permission:settings.view'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
});

// Slot Preset CRUD
Route::middleware(['permission:calendar.manage'])->group(function () {
    Route::resource('slot-presets', SlotPresetController::class)->parameters([
        'slot-presets' => 'slotPreset'
    ]);
    Route::post('slot-presets/{slotPreset}/make-default', [SlotPresetController::class, 'makeDefault'])->name('slot-presets.make-default');
});

// User Management Routes
Route::resource('users', UserController::class);
Route::post('users/{user}/mark-as-admin', [UserController::class, 'markAsAdmin'])->name('users.mark-as-admin');

// Role Management Routes
Route::resource('roles', RoleController::class);
Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.update-permissions');

// Debug route to test permissions
Route::get('/debug-permissions', function () {
    $user = auth()->user();
    return response()->json([
        'user' => $user->name,
        'is_admin' => $user->is_admin ?? 'not set',
        'user_type' => $user->user_type ?? 'not set',
        'roles' => $user->roles->pluck('name'),
        'permissions' => $user->getAllPermissions()->toArray(),
        'has_users_view' => $user->hasPermission('users.view'),
        'has_roles_view' => $user->hasPermission('roles.view'),
        'is_master_admin' => $user->hasRole('master-admin'),
        'can_users_view' => Gate::allows('users.view'),
        'can_roles_view' => Gate::allows('roles.view'),
    ]);
})->name('debug.permissions');
