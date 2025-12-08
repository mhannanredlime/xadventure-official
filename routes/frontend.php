<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\CustomPackageController;
use App\Http\Controllers\Frontend\RegularPackageBookingController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\PackageController as FrontendPackageController;
use App\Http\Controllers\Frontend\AutvPackageNewController;
use App\Http\Controllers\Frontend\BookingController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\BookingReceiptController;
use App\Http\Controllers\Frontend\CheckoutReceiptController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Customer\DashboardController;

/*
|--------------------------------------------------------------------------
| Frontend Routes (Public & Customer)
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'home'])->name('home');

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
Route::get('atv-utv-landing-page', [FrontendPackageController::class, 'atvUtvLandingPage'])->name('frontend.atv-utv-landing-page');
Route::get('atv-utv-package-bookings', [FrontendPackageController::class, 'atvUtvPackBookings'])->name('frontend.atv-utv-package-bookings');
Route::get('atv-utv-package-bookings/{package}', [FrontendPackageController::class, 'show'])->name('frontend.atv-utv-package-bookings.show');
Route::get('atv-utv-package-bookings/api/variants', [FrontendPackageController::class, 'getVariants'])->name('frontend.atv-utv-package-bookings.variants');
Route::get('atv-utv-package-bookings/api/availability', [FrontendPackageController::class, 'getAvailability'])->name('frontend.atv-utv-package-bookings.availability');
Route::get('atv-utv-package-bookings/api/availability/date', [FrontendPackageController::class, 'getAvailabilityForDate'])->name('frontend.atv-utv-package-bookings.availability.date');
Route::get('atv-utv-package-bookings/api/package/details', [FrontendPackageController::class, 'getPackageDetails'])->name('frontend.atv-utv-package-bookings.details');
Route::get('api/vehicle-type/details', [FrontendPackageController::class, 'getVehicleTypeDetails'])->name('frontend.vehicle-type.details');
Route::get('api/pricing/date', [FrontendPackageController::class, 'getPricingForDate'])->name('frontend.pricing.date');
Route::get('api/availability/check', [FrontendPackageController::class, 'checkAvailability'])->name('frontend.availability.check');
Route::get('api/schedule-slots/availability', [FrontendPackageController::class, 'getSlotsAvailability'])->name('frontend.schedule-slots.availability');

// Package price calculation route (no API, regular web route)
Route::post('/calculate-package-price', [AutvPackageNewController::class, 'calculatePrice'])->name('calculate.package.price');

// Check availability route
Route::post('/check-package-availability', [AutvPackageNewController::class, 'checkAvailability'])->name('check.package.availability');

// Add to cart route
Route::post('/cart/add-packages', [AutvPackageNewController::class, 'addPackagesToCart'])->name('cart.add.packages');

// Booking routes
Route::match(['post'], '/process-to-checkout', [BookingController::class, 'processToCheckout'])->name('frontend.process-to-checkout');

Route::post('cart/add', [BookingController::class, 'addToCart'])->name('frontend.cart.add');
Route::post('cart/update', [BookingController::class, 'updateCart'])->name('frontend.cart.update');
Route::get('cart/update', [BookingController::class, 'updateCart'])->name('frontend.cart.update.get');
Route::get('cart/availability', [BookingController::class, 'getCartItemAvailability'])->name('frontend.cart.availability');
Route::post('cart/remove/{cart_uuid}', [BookingController::class, 'removeFromCart'])->name('frontend.cart.remove');
Route::post('cart/update-datetime', [BookingController::class, 'updateCartDateTime'])->name('frontend.cart.updateDateTime');
Route::post('cart/validate-promo', [BookingController::class, 'validatePromoCode'])->name('frontend.cart.validate-promo');
Route::post('cart/remove-promo', [BookingController::class, 'removePromoCode'])->name('frontend.cart.remove-promo');

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
