<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\SlotPresetController;
use App\Http\Controllers\Frontend\BookingController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Admin\CustomerContactManage;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\VehicleTypeController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Admin\AvailabilityController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Frontend\CustomPackageController;
use App\Http\Controllers\Frontend\BookingReceiptController;
use App\Http\Controllers\Frontend\CheckoutReceiptController;
use App\Http\Controllers\Admin\VehicleAvailabilityController;
use App\Http\Controllers\Frontend\RegularPackageBookingController;
use App\Http\Controllers\Frontend\PackageController as FrontendPackageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


use App\Mail\ContactFormSubmitted;

Route::get('/test-contact-mail', function () {
    $name = 'John Doe';
    $email = 'johndoe@yopmail.com';
    $subject = 'Test Contact Form';
    $user_message = "This is a test message from the contact form.";
    $received_at = now()->format('F j, Y \a\t g:i A');
    
    return new ContactFormSubmitted($name, $email, $subject, $user_message, $received_at);
});



// Frontend Routes (Public)
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/about', function () {
    return view('frontend.about');
})->name('about');

Route::get('/privacy-policy', function () {
    return view('frontend.privacy-policy');
})->name('frontend.privacy-policy');

Route::get('/terms-conditions', function () {
    return view('frontend.terms-conditions');
})->name('frontend.terms-conditions');

Route::get('/faq', function () {
    return view('frontend.faq');
})->name('frontend.faq');

Route::get('/adventure', function () {
    return view('frontend.adventure');
})->name('adventure');

Route::get('/advanture-2', function () {
    $cartService = app(\App\Services\CartService::class);
    $cartCount = $cartService->getCartTotalItems();
    return view('frontend.advanture-2', compact('cartCount'));
})->name('advanture-2');

Route::get('/custom-packages', [CustomPackageController::class, 'index'])->name('custom-packages');
Route::get('/regular-packages-booking', [RegularPackageBookingController::class, 'index'])->name('regular-packages-booking');

Route::get('/archery', function () {
    return view('frontend.archery');
})->name('archery');

// Contact routes
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Package routes
Route::get('/packages', [FrontendPackageController::class, 'index'])->name('frontend.packages.index');
Route::get('/atv-utv', [FrontendPackageController::class, 'atvUtvPage'])->name('frontend.atv-utv');
Route::get('/packages/{package}', [FrontendPackageController::class, 'show'])->name('frontend.packages.show');
Route::get('/api/variants', [FrontendPackageController::class, 'getVariants'])->name('frontend.packages.variants');
Route::get('/api/availability', [FrontendPackageController::class, 'getAvailability'])->name('frontend.packages.availability');
Route::get('/api/availability/date', [FrontendPackageController::class, 'getAvailabilityForDate'])->name('frontend.packages.availability.date');
Route::get('/api/package/details', [FrontendPackageController::class, 'getPackageDetails'])->name('frontend.packages.details');
Route::get('/api/vehicle-type/details', [FrontendPackageController::class, 'getVehicleTypeDetails'])->name('frontend.vehicle-type.details');
Route::get('/api/pricing/date', [FrontendPackageController::class, 'getPricingForDate'])->name('frontend.pricing.date');
Route::get('/api/availability/check', [FrontendPackageController::class, 'checkAvailability'])->name('frontend.availability.check');
Route::get('/api/schedule-slots/availability', [FrontendPackageController::class, 'getSlotsAvailability'])->name('frontend.schedule-slots.availability');


// Booking routes
Route::match(['post'], '/process-to-checkout', [BookingController::class, 'processToCheckout'])->name('frontend.process-to-checkout');

Route::post('/cart/add', [BookingController::class, 'addToCart'])->name('frontend.cart.add');
Route::post('/cart/update', [BookingController::class, 'updateCart'])->name('frontend.cart.update');
Route::get('/cart/update', [BookingController::class, 'updateCart'])->name('frontend.cart.update.get');
Route::get('/cart/availability', [BookingController::class, 'getCartItemAvailability'])->name('frontend.cart.availability');
Route::post('/cart/remove/{cart_uuid}', [BookingController::class, 'removeFromCart'])->name('frontend.cart.remove');
Route::post('/cart/update-datetime', [BookingController::class, 'updateCartDateTime'])->name('frontend.cart.updateDateTime');
Route::post('/cart/validate-promo', [BookingController::class, 'validatePromoCode'])->name('frontend.cart.validate-promo');
Route::post('/cart/remove-promo', [BookingController::class, 'removePromoCode'])->name('frontend.cart.remove-promo');

// Cart API routes
Route::get('/api/cart/count', [CartController::class, 'getCartCount'])->name('frontend.cart.count');
Route::get('/api/cart/items', [CartController::class, 'getCartItems'])->name('frontend.cart.items');
Route::get('/api/cart/status', [CartController::class, 'getCartStatus'])->name('frontend.cart.status');

// Booking Receipt routes
Route::get('/receipt/{bookingCode}', [BookingReceiptController::class, 'show'])->name('frontend.receipt.show');
Route::get('/r/{shortlinkId}', [BookingReceiptController::class, 'showByShortlink'])->name('frontend.receipt.shortlink');

// Checkout Receipt routes
Route::get('/checkout/{checkoutId}', [CheckoutReceiptController::class, 'show'])->name('frontend.checkout.receipt');

Route::get('/checkout', [BookingController::class, 'checkout'])->name('frontend.checkout.index');
Route::post('/checkout/process', [BookingController::class, 'processBooking'])->name('frontend.checkout.process');

// Payment routes
Route::get('/payment', [PaymentController::class, 'index'])->name('frontend.payment.index');
Route::post('/payment/initiate', [PaymentController::class, 'initiate'])->name('payment.initiate');
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/fail', [PaymentController::class, 'fail'])->name('payment.fail');
Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
Route::post('/payment/ipn', [PaymentController::class, 'ipn'])->name('payment.ipn');

// Amar Pay specific routes (accept both GET and POST for callbacks, exclude CSRF)
Route::withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])->group(function () {
    Route::match(['get', 'post'], '/payment/amarpay/success', [PaymentController::class, 'amarpaySuccess'])->name('payment.amarpay.success');
    Route::match(['get', 'post'], '/payment/amarpay/fail', [PaymentController::class, 'amarpayFail'])->name('payment.amarpay.fail');
    Route::match(['get', 'post'], '/payment/amarpay/cancel', [PaymentController::class, 'amarpayCancel'])->name('payment.amarpay.cancel');
    Route::post('/payment/amarpay/ipn', [PaymentController::class, 'amarpayIPN'])->name('payment.amarpay.ipn');
});

Route::get('/booking-confirmation', [BookingController::class, 'showConfirmation'])->name('booking.confirmation');
Route::get('/booking-confirmation/{booking_code}', [BookingController::class, 'showConfirmation'])->name('booking.confirmation.code');

// Payment Failed Page
Route::get('/payment-failed', function () {
    return view('frontend.payment-failed');
})->name('payment.failed');

// Test route for payment failed page (remove in production)
Route::get('/test-payment-failed', function () {
    return redirect()->route('payment.failed')
        ->with('error', 'This is a test payment failure message.')
        ->with('payment_details', [
            'reason' => 'Test failure',
            'transaction_id' => 'TEST_TXN_123456',
            'amount' => '1000.00',
            'currency' => 'BDT',
            'pg_error_code' => 'TEST_ERROR_001'
        ]);
})->name('test.payment.failed');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Customer Authentication Routes
Route::get('/customer/login', [CustomerAuthController::class, 'showLoginForm'])->name('customer.login');
Route::post('/customer/login', [CustomerAuthController::class, 'login'])->name('customer.login.post');

// Storage link command route (for development/deployment)
Route::get('/setup/storage-link', function () {
    try {
        // Check if the symbolic link already exists
        if (file_exists(public_path('storage'))) {
            return response()->json([
                'success' => false,
                'message' => 'Storage link already exists',
                'path' => public_path('storage')
            ]);
        }

        // Run the storage:link command
        $result = \Illuminate\Support\Facades\Artisan::call('storage:link');

        if ($result === 0) {
            return response()->json([
                'success' => true,
                'message' => 'Storage link created successfully',
                'path' => public_path('storage')
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create storage link',
                'error_code' => $result
            ], 500);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error creating storage link: ' . $e->getMessage()
        ], 500);
    }
})->name('setup.storage-link');
Route::get('/customer/register', [CustomerAuthController::class, 'showRegistrationForm'])->name('customer.register');
Route::post('/customer/register', [CustomerAuthController::class, 'register']);
Route::post('/customer/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');

// Customer Dashboard Routes (Protected)
Route::middleware(['customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
    Route::get('/reservations', [DashboardController::class, 'reservations'])->name('reservations');
    Route::get('/reservations/{id}', [DashboardController::class, 'reservationDetails'])->name('reservations.details');
});

// Admin Routes (Protected)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
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
    });

    // Promo Codes
    Route::middleware(['permission:promo-codes.manage'])->group(function () {
        Route::resource('promo-codes', PromoCodeController::class);
        Route::patch('promo-codes/{promoCode}/toggle', [PromoCodeController::class, 'toggleStatus'])->name('promo-codes.toggle');
        Route::get('promo-codes/filter', [PromoCodeController::class, 'getPromoCodes'])->name('promo-codes.filter');
        Route::post('promo-codes/validate', [PromoCodeController::class, 'validateCode'])->name('promo-codes.validate');
    });

    // Reservations
    Route::middleware(['permission:reservations.view'])->group(function () {
        Route::resource('reservations', ReservationController::class);
        Route::get('reservations/history', [ReservationController::class, 'history'])->name('reservations.history');
        Route::get('reservations/export/pending', [ReservationController::class, 'exportPending'])->name('reservations.export.pending');
        Route::get('reservations/export/history', [ReservationController::class, 'exportHistory'])->name('reservations.export.history');
    });

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
        Route::get('gallery', [App\Http\Controllers\Admin\GalleryController::class, 'index'])->name('gallery.index');
        Route::post('gallery/upload', [App\Http\Controllers\Admin\GalleryController::class, 'upload'])->name('gallery.upload');
        Route::get('gallery/images', [App\Http\Controllers\Admin\GalleryController::class, 'getImages'])->name('gallery.get');
        Route::get('gallery/{gallery}', [App\Http\Controllers\Admin\GalleryController::class, 'show'])->name('gallery.show');
        Route::put('gallery/{gallery}', [App\Http\Controllers\Admin\GalleryController::class, 'update'])->name('gallery.update');
        Route::delete('gallery/{gallery}', [App\Http\Controllers\Admin\GalleryController::class, 'destroy'])->name('gallery.destroy');
        Route::post('gallery/{gallery}/copy', [App\Http\Controllers\Admin\GalleryController::class, 'copyToLocation'])->name('gallery.copy');
    });

    // Admin pages (existing) - Protected with permissions
    Route::middleware(['permission:vehicles.manage'])->group(function () {
        Route::get('/vehical-management', [VehicleController::class, 'index'])->name('vehical-management');
    });

    Route::middleware(['permission:vehicle-types.manage'])->group(function () {
        Route::get('/vehical-setup', [VehicleTypeController::class, 'index'])->name('vehical-setup');
    });

    Route::middleware(['permission:packages.manage'])->group(function () {
        Route::get('/atvutv-packege-management', [PackageController::class, 'createAtvUtv'])->name('atvutv-packege-management');
        Route::post('/atvutv-packege-management', [PackageController::class, 'storeAtvUtv'])->name('atvutv-packege-management.store');
        Route::get('/atvutv-packege-management/{package}/edit', [PackageController::class, 'editAtvUtv'])->name('atvutv-packege-management.edit');
        Route::put('/atvutv-packege-management/{package}', [PackageController::class, 'updateAtvUtv'])->name('atvutv-packege-management.update');

        Route::get('/regular-packege-management', [PackageController::class, 'createRegular'])->name('regular-packege-management');
        Route::post('/regular-packege-management', [PackageController::class, 'storeRegular'])->name('regular-packege-management.store');
        Route::get('/regular-packege-management/{package}/edit', [PackageController::class, 'editRegular'])->name('regular-packege-management.edit');
        Route::put('/regular-packege-management/{package}', [PackageController::class, 'updateRegular'])->name('regular-packege-management.update');

        Route::get('/packege/list', [PackageController::class, 'index'])->name('packege.list');
    });

    Route::middleware(['permission:reservations.view'])->group(function () {
        Route::get('/reservation-dashboard', [ReservationController::class, 'index'])->name('reservation-dashboard');
        Route::get('/view-reservation-dashboard', [ReservationController::class, 'history'])->name('view-reservation-dashboard');
    });

    Route::middleware(['permission:promo-codes.manage'])->group(function () {
        Route::get('/promo', [PromoCodeController::class, 'index'])->name('promo');
        Route::get('/promo-popup', function () {
            return view('admin.promo-popup');
        })->name('promo-popup');
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

    // User Management Routes (temporarily without permission middleware for testing)
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::post('users/{user}/mark-as-admin', [\App\Http\Controllers\Admin\UserController::class, 'markAsAdmin'])->name('users.mark-as-admin');

    // Role Management Routes (temporarily without permission middleware for testing)
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    Route::get('roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'permissions'])->name('roles.permissions');
    Route::put('roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'updatePermissions'])->name('roles.update-permissions');
});

// Error handling routes
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
