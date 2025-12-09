<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Frontend\BookingController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Frontend\CustomPackageController;
use App\Http\Controllers\Frontend\BookingReceiptController;
use App\Http\Controllers\Frontend\CheckoutReceiptController;
use App\Http\Controllers\Frontend\AutvPackageNewController; // Consider renaming class to AtvUtvPackageNewController;
use App\Http\Controllers\Frontend\PackageController as FrontendPackageController;

/*
|--------------------------------------------------------------------------
| Frontend Routes (Public & Customer)
|--------------------------------------------------------------------------
*/

// --- Static & Home Pages ---
Route::controller(HomeController::class)->group(function () {
    Route::get('/', 'home')->name('home');
});

Route::view('/about', 'frontend.about')->name('about');
Route::view('/privacy-policy', 'frontend.privacy-policy')->name('frontend.privacy-policy');
Route::view('/terms-conditions', 'frontend.terms-conditions')->name('frontend.terms-conditions');
Route::view('/faq', 'frontend.faq')->name('frontend.faq');
Route::view('/adventure', 'frontend.adventure')->name('adventure');
Route::get('/adventure-packages', function () { // Fixed typo: advanture -> adventure
    $cartService = app(\App\Services\CartService::class);
    $cartCount = $cartService->getCartTotalItems();
    return view('frontend.advanture-2', compact('cartCount'));
})->name('adventure.packages'); 
Route::view('/archery', 'frontend.archery')->name('archery');
Route::view('/payment-failed', 'frontend.payment-failed')->name('payment.failed');


// --- Package Routes ---

// Custom Packages
Route::get('/packages/custom', [CustomPackageController::class, 'index'])->name('packages.custom.index'); // Renamed: custom-packages -> packages.custom.index

// Regular Packages
Route::get('/packages/regular/booking', [CustomPackageController::class, 'booking'])->name('packages.regular.index'); // Renamed: regular-packages-booking -> packages.regular.index

// ATV/UTV Packages
Route::controller(FrontendPackageController::class)->prefix('atv-utv-bookings')->name('packages.atv-utv.')->group(function () {
    Route::get('/', 'atvUtvLandingPage')->name('landing'); // frontend.atv-utv-landing-page -> packages.atv-utv.landing
    Route::get('/list', 'atvUtvPackBookings')->name('list'); // frontend.atv-utv-package-bookings -> packages.atv-utv.list
    Route::get('/details/{package}', 'show')->name('show'); // frontend.atv-utv-package-bookings.show -> packages.atv-utv.show
    
    // API Endpoints for ATV/UTV
    Route::prefix('api')->group(function () {
        Route::get('variants', 'getVariants')->name('api.variants');
        Route::get('availability', 'getAvailability')->name('api.availability');
        Route::get('availability/date', 'getAvailabilityForDate')->name('api.availability.date');
        Route::get('package/details', 'getPackageDetails')->name('api.details');
        Route::get('vehicle-type/details', 'getVehicleTypeDetails')->name('api.vehicle-type.details');
        Route::get('pricing/date', 'getPricingForDate')->name('api.pricing.date');
        Route::get('availability/check', 'checkAvailability')->name('api.availability.check');
        Route::get('schedule-slots/availability', 'getSlotsAvailability')->name('api.slots.availability');
    });
});

// Package Logic (Price & Availability - POST)
Route::controller(AutvPackageNewController::class)->group(function () {
    Route::post('/calculate-package-price', 'calculatePrice')->name('packages.calculate-price');
    Route::post('/check-package-availability', 'checkAvailability')->name('packages.check-availability');
    Route::post('/cart/add-packages', 'addPackagesToCart')->name('cart.add-packages');
});


// --- Cart & Booking Routes ---

Route::controller(BookingController::class)->group(function () {
    // Booking Flow
    Route::match(['post'], '/checkout/initiate', 'processToCheckout')->name('booking.process-checkout');
    Route::get('/checkout', 'checkout')->name('checkout.index');
    Route::post('/checkout/process', 'processBooking')->name('checkout.process');
    Route::get('/booking-confirmation', 'showConfirmation')->name('booking.confirmation');
    Route::get('/booking-confirmation/{booking_code}', 'showConfirmation')->name('booking.confirmation.code');

    // Cart Actions
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::post('add', 'addToCart')->name('add'); // frontend.cart.add -> cart.add
        Route::match(['get', 'post'], 'update', 'updateCart')->name('update'); // Combined update
        Route::get('availability', 'getCartItemAvailability')->name('availability');
        Route::post('remove/{cart_uuid}', 'removeFromCart')->name('remove');
        Route::post('update-datetime', 'updateCartDateTime')->name('update-datetime');
        Route::post('validate-promo', 'validatePromoCode')->name('validate-promo');
        Route::post('remove-promo', 'removePromoCode')->name('remove-promo');
        Route::post('remove-package', 'removePackageFromCart')->name('remove-package');
    });
});

// Cart API (Visual/Floater)
Route::controller(CartController::class)->prefix('api/cart')->name('api.cart.')->group(function () {
    Route::get('count', 'getCartCount')->name('count');
    Route::get('items', 'getCartItems')->name('items');
    Route::get('status', 'getCartStatus')->name('status');
});


// --- Receipts ---

Route::get('/receipt/{bookingCode}', [BookingReceiptController::class, 'show'])->name('receipt.show');
Route::get('/r/{shortlinkId}', [BookingReceiptController::class, 'showByShortlink'])->name('receipt.shortlink');
Route::get('/checkout/receipt/{checkoutId}', [CheckoutReceiptController::class, 'show'])->name('checkout.receipt');


// --- Contact ---

Route::controller(ContactController::class)->prefix('contact')->name('contact.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
});


// --- Payment Routes ---

Route::controller(PaymentController::class)->prefix('payment')->name('payment.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/initiate', 'initiate')->name('initiate');
    Route::get('/success', 'success')->name('success');
    Route::get('/fail', 'fail')->name('fail');
    Route::get('/cancel', 'cancel')->name('cancel');
    Route::post('/ipn', 'ipn')->name('ipn');
    
    // AmarPay Callbacks (No CSRF)
    Route::withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])->group(function () {
        Route::match(['get', 'post'], '/amarpay/success', 'amarpaySuccess')->name('amarpay.success');
        Route::match(['get', 'post'], '/amarpay/fail', 'amarpayFail')->name('amarpay.fail');
        Route::match(['get', 'post'], '/amarpay/cancel', 'amarpayCancel')->name('amarpay.cancel');
        Route::post('/amarpay/ipn', 'amarpayIPN')->name('amarpay.ipn');
    });
});


// --- Authentication (Admin/User) ---

Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'store');
    Route::post('/logout', 'logout')->name('logout');
});


// --- Customer Authentication & Dashboard ---

Route::prefix('customer')->name('customer.')->group(function () {
    // Auth
    Route::controller(CustomerAuthController::class)->group(function () {
        Route::get('login', 'showLoginForm')->name('login');
        Route::post('login', 'login')->name('login.post');
        Route::get('register', 'showRegistrationForm')->name('register');
        Route::post('register', 'register');
        Route::post('logout', 'logout')->name('logout');
    });

    // Dashboard (Protected)
    Route::middleware(['customer'])->controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/profile', 'profile')->name('profile');
        Route::put('/profile', 'updateProfile')->name('profile.update');
        Route::get('/reservations', 'reservations')->name('reservations');
        Route::get('/reservations/{id}', 'reservationDetails')->name('reservations.details');
    });
});


// --- Development Utilities ---

// Storage Link (Dev/Deployment only)
Route::get('/setup/storage-link', function () {
    if (app()->isProduction()) {
        abort(404);
    }
    
    try {
        if (file_exists(public_path('storage'))) {
            return response()->json(['success' => false, 'message' => 'Storage link already exists']);
        }
        
        Artisan::call('storage:link');
        return response()->json(['success' => true, 'message' => 'Storage link created']);
        
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
})->name('setup.storage-link');

// Test Payment Fail (Dev only)
if (!app()->isProduction()) {
    Route::get('/test-payment-failed', function () {
        return redirect()->route('payment.failed')
            ->with('error', 'Test failure message')
            ->with('payment_details', ['reason' => 'Test', 'amount' => '1000.00']);
    })->name('test.payment.failed');
}


// --- Error Handling ---

Route::fallback(fn () => response()->view('errors.404', [], 404));

