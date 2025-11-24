<?php

namespace App\Http\Controllers\Frontend;

// Event imports removed - no notifications sent at checkout time
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\PackageVariant;
use App\Models\ScheduleSlot;
use App\Models\PromoCode;
use App\Models\User;
use App\Services\PromoCodeService;
use App\Services\PhoneNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Payment; // Added this import for Payment model
use App\Models\ReservationItem; // Added this import for ReservationItem model
use Exception;

class BookingController extends Controller
{
    public function cart(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return view('frontend.shopping-cart', compact('cart'));
        }

        $packages = [];
        $packagesByDate = [];
        $priceService = app(\App\Services\PriceCalculationService::class);
        $cartErrors = [];

        foreach ($cart as $key => $item) {
            try {
                $variant = PackageVariant::with(['package.primaryImage', 'package.images'])->find($item['variant_id']);
                $slot = ScheduleSlot::find($item['slot_id']);

                if (!$variant || !$slot) {
                    $cartErrors[] = 'Invalid package or time slot found in cart. Item has been removed.';
                    // Remove invalid item from cart
                    unset($cart[$key]);
                    continue;
                }

                $package = $variant->package;

                // Only check availability for ATV/UTV packages, not regular packages
                if (
                    $package->type === 'atv' || $package->type === 'utv' ||
                    str_contains(strtolower($package->name), 'atv') ||
                    str_contains(strtolower($package->name), 'utv') ||
                    $package->name === 'ATV/UTV Trail Rides' ||
                    $package->name === 'UTV Trail Rides 2'
                ) {
                    // Final availability recheck to avoid oversell in concurrent cases
                    $availability = $priceService->getPricingAndAvailabilityForDate($variant, $item['date'], $slot->id);
                    if ($item['quantity'] > $availability['total_vehicles']) {
                        $cartErrors[] = 'Only ' . $availability['total_vehicles'] . ' available for ' . $variant->package->name . ' on ' . $item['date'] . ' at selected time. Item has been removed.';
                        // Remove unavailable item from cart
                        unset($cart[$key]);
                        continue;
                    }
                }

                $price = $priceService->getPriceForDate($variant, $item['date']);

                $packageItem = [
                    'key' => $key,
                    'variant' => $variant,
                    'quantity' => $item['quantity'],
                    'date' => $item['date'],
                    'slot' => $slot,
                    'price' => $price
                ];

                // Group by date only (not by time slot)
                $dateKey = $item['date'];
                if (!isset($packagesByDate[$dateKey])) {
                    $packagesByDate[$dateKey] = [
                        'date' => $item['date'],
                        'slots' => [], // Store all slots for this date
                        'packages' => []
                    ];
                }
                
                // Add slot to slots array if not already present
                $slotExists = false;
                foreach ($packagesByDate[$dateKey]['slots'] as $existingSlot) {
                    if ($existingSlot->id === $slot->id) {
                        $slotExists = true;
                        break;
                    }
                }
                if (!$slotExists) {
                    $packagesByDate[$dateKey]['slots'][] = $slot;
                }
                
                $packagesByDate[$dateKey]['packages'][] = $packageItem;

                // Keep the original flat array for backward compatibility
                $packages[] = $packageItem;
            } catch (\Exception $e) {
                Log::error('Error processing cart item', [
                    'item' => $item,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                $cartErrors[] = 'Error processing item: ' . $e->getMessage() . '. Item has been removed.';
                // Remove problematic item from cart
                unset($cart[$key]);
            }
        }

        // Update cart session if items were removed
        if (count($cart) !== count(session()->get('cart', []))) {
            session()->put('cart', $cart);
        }

        // If all items were removed due to errors, return empty cart
        if (empty($packages)) {
            return view('frontend.shopping-cart', compact('cart', 'cartErrors'));
        }

        // Get applied promo code data from session
        $appliedPromoCode = session()->get('applied_promo_code');
        $promoDiscount = session()->get('promo_discount', 0);

        return view('frontend.shopping-cart', compact('cart', 'packages', 'packagesByDate', 'appliedPromoCode', 'promoDiscount', 'cartErrors'));
    }

    public function addToCart(Request $request)
    {
        // Debug logging
        Log::info('addToCart called', [
            'isJson' => $request->isJson(),
            'contentType' => $request->header('Content-Type'),
            'method' => $request->method(),
            'data' => $request->all()
        ]);

        // Check if this is an AJAX request with multiple items
        if ($request->isJson() || $request->header('Content-Type') === 'application/json') {
            // Handle multiple items from AJAX
            $items = $request->all();
            $cart = session()->get('cart', []);

            // If it's a single item array, wrap it
            if (!isset($items[0]) && isset($items['variant_id'])) {
                $items = [$items];
            }

            foreach ($items as $item) {
                $validated = validator($item, [
                    'variant_id' => 'required|exists:package_variants,id',
                    'quantity' => 'required|integer|min:1',
                    'date' => 'required|date|after_or_equal:today',
                    'slot_id' => 'required|exists:schedule_slots,id',
                    'acknowledgment_data' => 'nullable|array',
                    'signature_data' => 'nullable|string',
                ])->validate();

                // Re-check availability server-side before adding to cart
                $variant = PackageVariant::find($validated['variant_id']);
                $package = $variant->package;

                // Only check availability for ATV/UTV packages, not regular packages
                if (
                    $package->type === 'atv' || $package->type === 'utv' ||
                    str_contains(strtolower($package->name), 'atv') ||
                    str_contains(strtolower($package->name), 'utv') ||
                    $package->name === 'ATV/UTV Trail Rides' ||
                    $package->name === 'UTV Trail Rides 2'
                ) {
                    // Validate acknowledgment data for ATV/UTV packages
                    if (empty($validated['acknowledgment_data']) || empty($validated['signature_data'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Acknowledgment and signature are required for ATV/UTV bookings.',
                        ], 422);
                    }

                    // Validate acknowledgment data structure
                    $acknowledgmentData = $validated['acknowledgment_data'];
                    if (!isset($acknowledgmentData['driver_license_requirement']) || 
                        !isset($acknowledgmentData['license_show_requirement']) ||
                        !$acknowledgmentData['driver_license_requirement'] ||
                        !$acknowledgmentData['license_show_requirement']) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Both acknowledgment checkboxes must be checked for ATV/UTV bookings.',
                        ], 422);
                    }

                    $priceService = app(\App\Services\PriceCalculationService::class);
                    $availability = $priceService->getPricingAndAvailabilityForDate($variant, $validated['date'], $validated['slot_id']);
                    if ($validated['quantity'] > $availability['total_vehicles']) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Only ' . $availability['total_vehicles'] . ' available for the selected slot.',
                            'available_quantity' => $availability['total_vehicles'],
                        ], 422);
                    }
                }

                $key = $validated['variant_id'] . '_' . $validated['date'] . '_' . $validated['slot_id'];
                $cart[$key] = $validated;
            }

            session()->put('cart', $cart);

            return response()->json([
                'success' => true,
                'message' => 'Items added to cart successfully.',
                'cart_count' => count($cart),
                'cart_total_items' => array_sum(array_column($cart, 'quantity'))
            ]);
        }

        // Handle single item (existing logic)
        $validated = $request->validate([
            'variant_id' => 'required|exists:package_variants,id',
            'quantity' => 'required|integer|min:1',
            'date' => 'required|date|after_or_equal:today',
            'slot_id' => 'required|exists:schedule_slots,id',
            'acknowledgment_data' => 'nullable|array',
            'signature_data' => 'nullable|string',
        ]);

        // Re-check availability server-side before adding to cart
        $variant = PackageVariant::find($validated['variant_id']);
        $package = $variant->package;

        // Only check availability for ATV/UTV packages, not regular packages
        if (
            $package->type === 'atv' || $package->type === 'utv' ||
            str_contains(strtolower($package->name), 'atv') ||
            str_contains(strtolower($package->name), 'utv') ||
            $package->name === 'ATV/UTV Trail Rides' ||
            $package->name === 'UTV Trail Rides 2'
        ) {
            // Validate acknowledgment data for ATV/UTV packages
            if (empty($validated['acknowledgment_data']) || empty($validated['signature_data'])) {
                return redirect()->back()->with('error', 'Acknowledgment and signature are required for ATV/UTV bookings.');
            }

            // Validate acknowledgment data structure
            $acknowledgmentData = $validated['acknowledgment_data'];
            if (!isset($acknowledgmentData['driver_license_requirement']) || 
                !isset($acknowledgmentData['license_show_requirement']) ||
                !$acknowledgmentData['driver_license_requirement'] ||
                !$acknowledgmentData['license_show_requirement']) {
                return redirect()->back()->with('error', 'Both acknowledgment checkboxes must be checked for ATV/UTV bookings.');
            }

            $priceService = app(\App\Services\PriceCalculationService::class);
            $availability = $priceService->getPricingAndAvailabilityForDate($variant, $validated['date'], $validated['slot_id']);
            if ($validated['quantity'] > $availability['total_available']) {
                return redirect()->back()->with('error', 'Only ' . $availability['total_available'] . ' available for the selected slot.');
            }
        }

        $cart = session()->get('cart', []);
        $key = $validated['variant_id'] . '_' . $validated['date'] . '_' . $validated['slot_id'];

        $cart[$key] = $validated;
        session()->put('cart', $cart);

        return redirect()->route('frontend.cart.index')
            ->with('success', 'Item added to cart successfully.');
    }

    public function updateCart(Request $request)
    {
        // Handle both GET and POST requests for simplicity
        $cartKey = $request->get('cart_key') ?? $request->input('cart_key');
        $quantity = $request->get('quantity') ?? $request->input('quantity');

        if (!$cartKey || !$quantity) {
            return redirect()->route('frontend.cart.index')
                ->with('error', 'Invalid request parameters.');
        }

        $quantity = (int) $quantity;
        if ($quantity < 1) {
            return redirect()->route('frontend.cart.index')
                ->with('error', 'Quantity must be at least 1.');
        }

        $cart = session()->get('cart', []);

        if (!isset($cart[$cartKey])) {
            return redirect()->route('frontend.cart.index')
                ->with('error', 'Item not found in cart.');
        }

        $cartItem = $cart[$cartKey];
        $variant = \App\Models\PackageVariant::find($cartItem['variant_id']);

        if (!$variant) {
            return redirect()->route('frontend.cart.index')
                ->with('error', 'Package variant not found.');
        }

        $package = $variant->package;

        // Check availability for ATV/UTV packages
        if (
            $package->type === 'atv' || $package->type === 'utv' ||
            str_contains(strtolower($package->name), 'atv') ||
            str_contains(strtolower($package->name), 'utv') ||
            $package->name === 'ATV/UTV Trail Rides' ||
            $package->name === 'UTV Trail Rides 2'
        ) {

            $priceService = app(\App\Services\PriceCalculationService::class);
            $availability = $priceService->getPricingAndAvailabilityForDate(
                $variant,
                $cartItem['date'],
                $cartItem['slot_id']
            );

            if ($quantity > $availability['total_vehicles']) {
                return redirect()->route('frontend.cart.index')
                    ->with('error', 'Only ' . $availability['total_vehicles'] . ' vehicles available for the selected slot.');
            }
        }

        // Update the quantity
        $cart[$cartKey]['quantity'] = $quantity;
        session()->put('cart', $cart);

        return redirect()->route('frontend.cart.index')
            ->with('success', 'Quantity updated successfully.');
    }

    /**
     * Get availability data for cart item editing
     */
    public function getCartItemAvailability(Request $request)
    {
        $cartKey = $request->get('cart_key');
        $cart = session()->get('cart', []);

        if (!isset($cart[$cartKey])) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in cart.'
            ], 404);
        }

        $cartItem = $cart[$cartKey];
        $variant = \App\Models\PackageVariant::find($cartItem['variant_id']);

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Package variant not found.'
            ], 404);
        }

        $package = $variant->package;

        // Get real availability data
        $priceService = app(\App\Services\PriceCalculationService::class);
        $availability = $priceService->getPricingAndAvailabilityForDate(
            $variant,
            $cartItem['date'],
            $cartItem['slot_id']
        );

        return response()->json([
            'success' => true,
            'data' => [
                'title' => $package->name . ' - ' . $variant->variant_name,
                'image' => $package->display_image_url ?? asset('admin/images/pack-1.png'),
                'unit_price' => $availability['final_price'],
                'current_quantity' => $cartItem['quantity'],
                'available_vehicles' => $availability['total_vehicles'], // Use total_vehicles like admin, not total_available
                'available_capacity' => $availability['available_capacity'],
                'price_tag' => $availability['price_tag'],
                'has_discount' => $availability['has_discount'],
                'default_price' => $availability['default_price']
            ]
        ]);
    }

    public function removeFromCart(Request $request)
    {
        $key = $request->get('key');
        $cart = session()->get('cart', []);

        if (isset($cart[$key])) {
            unset($cart[$key]);
            session()->put('cart', $cart);

            return redirect()->route('frontend.cart.index')
                ->with('success', 'Item removed from cart successfully.');
        }

        return redirect()->route('frontend.cart.index')
            ->with('error', 'Item not found in cart.');
    }

    public function updateCartDateTime(Request $request)
    {
        $updates = $request->all();
        $cart = session()->get('cart', []);

        foreach ($updates as $update) {
            $cartKey = $update['cart_key'] ?? null;
            $date = $update['date'] ?? null;
            $timeSlot = $update['time_slot'] ?? null;

            if ($cartKey && isset($cart[$cartKey])) {
                $item = $cart[$cartKey];

                // Create new cart key with updated slot_id
                $newCartKey = $item['variant_id'] . '_' . $date . '_' . $timeSlot;

                // Update the item with new date and slot_id
                $item['date'] = $date;
                $item['slot_id'] = $timeSlot;

                // Remove old cart item and add with new key
                unset($cart[$cartKey]);
                $cart[$newCartKey] = $item;
            }
        }

        session()->put('cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Booking details updated successfully.'
        ]);
    }

    public function validatePromoCode(Request $request)
    {
        $request->validate([
            'promo_code' => 'required|string|max:50',
        ]);

        $promoCode = $request->input('promo_code');

        // Calculate cart subtotal
        $cart = session()->get('cart', []);
        $subtotal = 0;
        $priceService = app(\App\Services\PriceCalculationService::class);

        foreach ($cart as $item) {
            $variant = PackageVariant::find($item['variant_id']);
            if ($variant) {
                $price = $priceService->getPriceForDate($variant, $item['date']);
                $subtotal += $price * $item['quantity'];
            }
        }

        // Validate promo code
        $promoCodeService = app(\App\Services\PromoCodeService::class);
        $result = $promoCodeService->validatePromoCode($promoCode, $subtotal);

        if ($result['valid']) {
            // Store promo code in session
            session()->put('applied_promo_code', $result['promo_code']);
            session()->put('promo_discount', $result['discount']);

            return response()->json([
                'success' => true,
                'message' => 'Promo code applied successfully!',
                'discount' => $result['discount'],
                'discount_formatted' => 'TK ' . number_format($result['discount']),
                'total_after_discount' => $subtotal - $result['discount'],
                'total_after_discount_formatted' => 'TK ' . number_format($subtotal - $result['discount'])
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 422);
        }
    }

    public function removePromoCode(Request $request)
    {
        // Remove promo code from session
        session()->forget('applied_promo_code');
        session()->forget('promo_discount');

        return response()->json([
            'success' => true,
            'message' => 'Promo code removed successfully!'
        ]);
    }

    public function checkout()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('frontend.cart.index')->with('info', 'Your cart is empty. Please add some items before proceeding to checkout.');
        }

        $packages = [];
        $total = 0;
        $priceService = app(\App\Services\PriceCalculationService::class);

        foreach ($cart as $item) {
            $variant = PackageVariant::with(['package.primaryImage', 'package.images'])->find($item['variant_id']);
            $slot = ScheduleSlot::find($item['slot_id']);

            if ($variant && $slot) {
                $price = $priceService->getPriceForDate($variant, $item['date']);
                $subtotal = $price * $item['quantity'];
                $total += $subtotal;

                $packages[] = [
                    'variant' => $variant,
                    'quantity' => $item['quantity'],
                    'date' => $item['date'],
                    'slot' => $slot,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }
        }

        return view('frontend.checkout.index', compact('packages', 'total'));
    }

    public function processBooking(Request $request)
    {
        // Initialize phone number service
        $phoneService = new PhoneNumberService();

        // Custom validation for password fields based on create_account checkbox
        $createAccount = $request->has('create_account') && $request->create_account;

        $validationRules = [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'nullable|string|max:500',
            'payment_method' => 'required|in:credit_card,check_payment,amarpay',
            'create_account' => 'nullable|boolean',
        ];

        // Only add password validation if create_account is checked
        if ($createAccount) {
            $validationRules['password'] = 'required|string|min:8|confirmed';
            $validationRules['password_confirmation'] = 'required|string|min:8';
        } else {
            $validationRules['password'] = 'nullable|string|min:8';
            $validationRules['password_confirmation'] = 'nullable|string|min:8';
        }

        $request->validate($validationRules);

        // Validate and format phone number
        Log::info('Starting checkout process', [
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'payment_method' => $request->payment_method,
        ]);

        $phoneValidation = $phoneService->validateAndFormat($request->customer_phone);
        Log::info('Phone validation result', [
            'valid' => $phoneValidation['valid'],
            'formatted' => $phoneValidation['formatted'] ?? null,
            'error' => $phoneValidation['error'] ?? null,
        ]);

        if (!$phoneValidation['valid']) {
            Log::warning('Phone validation failed during checkout', [
                'original_phone' => $request->customer_phone,
                'error' => $phoneValidation['error'],
            ]);
            return redirect()->back()
                ->withErrors(['customer_phone' => $phoneValidation['error']])
                ->withInput();
        }

        // Additional validation for credit card payment
        if ($request->payment_method === 'credit_card') {
            $request->validate([
                'card_holder' => 'required|string|max:255',
                'card_number' => 'required|string|max:19',
                'expiry_date' => 'required|string|max:5',
                'cvv' => 'required|string|max:4',
            ]);
        }

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('frontend.cart.index')
                ->with('error', 'Your cart is empty.');
        }

        try {
            // Debug logging
            Log::info('processBooking started', [
                'request_data' => $request->all(),
                'cart' => session()->get('cart', [])
            ]);

            // Validate cart has items
            if (empty($cart)) {
                throw new \Exception('Cart is empty');
            }

            DB::beginTransaction();

            // Handle user account creation/login
            $user = null;
            $wasGuestCheckout = false;

            if (Auth::check()) {
                // User is already logged in
                $user = Auth::user();
                Log::info('User already logged in', ['user_id' => $user->id]);
            } else {
                // Mark this as a guest checkout
                $wasGuestCheckout = true;
                session()->put('guest_checkout', true);

                // Check if user exists with this email
                $user = User::where('email', $request->customer_email)->first();

                if ($user) {
                    // User exists, check if they want to create account with password
                    if ($createAccount && $request->password) {
                        // User wants to set a password for existing account
                        $user->update([
                            'password' => bcrypt($request->password),
                        ]);
                        Log::info('Password updated for existing user', ['user_id' => $user->id]);
                        // Don't login the user here - we'll login the customer instead
                    } else {
                        // User exists but doesn't want to create account - don't login
                        Log::info('Existing user found but not logging in (guest checkout)', ['user_id' => $user->id]);
                    }
                } else {
                    // Check if user wants to create an account
                    if ($createAccount && $request->password) {
                        // Create new user account with provided password
                        $user = User::create([
                            'name' => $request->customer_name,
                            'email' => $request->customer_email,
                            'phone' => $phoneValidation['formatted'], // Use formatted phone number
                            'address' => $request->customer_address,
                            'password' => bcrypt($request->password), // Use provided password
                            'is_admin' => false, // Explicitly set as customer, not admin
                        ]);

                        // Don't log in the user here - we'll log in the customer instead
                        Log::info('New user account created', ['user_id' => $user->id]);
                    } else {
                        // Guest checkout - don't create user account or login
                        $user = null;
                        Log::info('Guest checkout - no user account created');
                    }
                }
            }

            // Create or find customer and link to user account (if exists)
            Log::info('Creating customer', [
                'email' => $request->customer_email,
                'phone' => $phoneValidation['formatted'],
                'user_id' => $user ? $user->id : null,
            ]);

            $customer = Customer::updateOrCreate(
                ['email' => $request->customer_email],
                [
                    'name' => $request->customer_name,
                    'phone' => $phoneValidation['formatted'], // Use formatted phone number
                    'address' => $request->customer_address,
                    'user_id' => $user ? $user->id : null, // Link to user account if exists
                    'password' => $user && $createAccount ? $user->password : null, // Copy password from user if account was created
                ]
            );

            Log::info('Customer created/found', [
                'customer_id' => $customer->id,
                'user_id' => $user ? $user->id : null,
                'phone_stored' => $customer->phone,
            ]);

            // Log in the customer if they created an account or updated their password
            if ($createAccount && $customer->password) {
                Auth::guard('customer')->login($customer);
                Log::info('Customer logged in after account creation/update', ['customer_id' => $customer->id]);
            }

            // Store customer ID in session for future use
            session()->put('customer_id', $customer->id);

            $priceService = app(\App\Services\PriceCalculationService::class);
            $promoService = app(\App\Services\PromoCodeService::class);

            $totalAmount = 0;
            $reservations = [];

            // Ensure we have cart items to process
            if (empty($cart)) {
                throw new \Exception('Cart is empty - cannot create reservations.');
            }

            foreach ($cart as $key => $item) {
                Log::info('Processing cart item', ['item' => $item]);

                $variant = PackageVariant::find($item['variant_id']);
                $slot = ScheduleSlot::find($item['slot_id']);

                if (!$variant || !$slot) {
                    throw new \Exception('Invalid package or time slot.');
                }

                Log::info('Found variant and slot', [
                    'variant_id' => $variant->id,
                    'slot_id' => $slot->id
                ]);

                $price = $priceService->getPriceForDate($variant, $item['date']);
                $itemTotal = $price * $item['quantity'];
                $totalAmount += $itemTotal;

                Log::info('Calculated price', [
                    'price' => $price,
                    'quantity' => $item['quantity'],
                    'itemTotal' => $itemTotal
                ]);

                // Create reservation
                Log::info('Creating reservation');

                // Get report time from schedule slot
                $reportTime = $slot->start_time ?? '09:00:00';

                $reservation = Reservation::create([
                    'booking_code' => $this->generateBookingCode(),
                    'customer_id' => $customer->id,
                    'package_variant_id' => $variant->id,
                    'schedule_slot_id' => $slot->id,
                    'date' => $item['date'],
                    'report_time' => $reportTime,
                    'party_size' => $item['quantity'] * $variant->capacity, // Total number of people
                    'subtotal' => $itemTotal,
                    'total_amount' => $itemTotal,
                    'booking_status' => in_array($request->payment_method, ['check_payment', 'amarpay']) ? 'pending' : 'confirmed',
                    'payment_status' => in_array($request->payment_method, ['check_payment', 'amarpay']) ? 'pending' : 'paid',
                    'notes' => 'Booking created from checkout',
                    'acknowledgment_data' => $item['acknowledgment_data'] ?? null,
                    'signature_data' => $item['signature_data'] ?? null,
                ]);

                Log::info('Reservation created', ['reservation_id' => $reservation->id]);

                // Create reservation item to track the actual vehicles booked
                $reservationItem = ReservationItem::create([
                    'reservation_id' => $reservation->id,
                    'package_variant_id' => $variant->id,
                    'qty' => $item['quantity'], // Number of vehicles booked
                    'unit_price' => $price,
                    'line_total' => $itemTotal,
                ]);

                Log::info('Reservation item created', [
                    'reservation_item_id' => $reservationItem->id,
                    'qty' => $item['quantity'],
                    'variant_capacity' => $variant->capacity
                ]);

                $reservations[] = $reservation;
                
                Log::info('Reservation added to array', [
                    'reservation_id' => $reservation->id,
                    'total_reservations' => count($reservations)
                ]);
            }

            // Final validation - ensure we created at least one reservation
            if (empty($reservations)) {
                throw new \Exception('Failed to create any reservations. Please check your cart items and try again.');
            }

            // Apply promo code if available
            $promoDiscount = 0;
            $appliedPromoCode = session()->get('applied_promo_code');

            if ($appliedPromoCode) {
                $promoValidation = $promoService->validatePromoCode(
                    $appliedPromoCode->code,
                    $totalAmount,
                    $customer->id
                );

                if ($promoValidation['valid']) {
                    $promoDiscount = $promoValidation['discount'];

                    // Record promo code redemption (use first reservation)
                    if (!empty($reservations)) {
                        $promoService->recordRedemption(
                            $appliedPromoCode->id,
                            $reservations[0]->id, // Use first reservation for promo record
                            $customer->id,
                            $promoDiscount
                        );
                    }
                }
            }

            // Calculate final amounts
            $subtotal = $totalAmount;
            $tax = ($subtotal - $promoDiscount) * 0.15; // 15% VAT
            $finalTotal = $subtotal - $promoDiscount + $tax;

            // Calculate per-reservation amounts (distribute tax and discount proportionally based on each reservation's contribution)
            $reservationCount = count($reservations);
            $distributedDiscount = 0;
            $distributedTax = 0;
            
            foreach ($reservations as $index => $reservation) {
                // Calculate this reservation's proportion of the total
                $reservationProportion = $reservation->total_amount / $totalAmount;
                
                // Distribute tax and discount proportionally
                $reservationDiscount = $promoDiscount * $reservationProportion;
                $reservationTax = $tax * $reservationProportion;
                
                // For the last reservation, ensure we distribute exactly the remaining amounts to avoid rounding errors
                if ($index === $reservationCount - 1) {
                    $reservationDiscount = $promoDiscount - $distributedDiscount;
                    $reservationTax = $tax - $distributedTax;
                }
                
                $reservationFinalTotal = $reservation->total_amount - $reservationDiscount + $reservationTax;
                
                $reservation->update([
                    'subtotal' => $reservation->total_amount, // Keep original subtotal
                    'discount_amount' => $reservationDiscount,
                    'tax_amount' => $reservationTax,
                    'total_amount' => $reservationFinalTotal, // Update with final total including tax and discount
                ]);
                
                // Track distributed amounts
                $distributedDiscount += $reservationDiscount;
                $distributedTax += $reservationTax;
            }


            // Generate transaction ID based on payment method
            $transactionId = null;
            $paymentDetails = [];
            
            if ($request->payment_method === 'check_payment') {
                $transactionId = 'CHK-' . strtoupper(uniqid());
                $paymentDetails = [
                    'method' => 'check_payment',
                    'status' => 'pending',
                    'instructions' => 'Please mail your check to complete the payment'
                ];
            } elseif ($request->payment_method === 'amarpay') {
                $transactionId = 'AMAR-' . strtoupper(uniqid());
                $paymentDetails = [
                    'method' => 'amarpay',
                    'status' => 'pending',
                    'gateway' => 'amarpay'
                ];
            } else {
                $transactionId = 'CC-' . strtoupper(uniqid());
                $paymentDetails = [
                    'method' => 'credit_card',
                    'status' => 'pending',
                    'gateway' => 'credit_card'
                ];
            }

            // Validate that we have reservations before proceeding
            if (empty($reservations)) {
                throw new \Exception('No reservations were created. Please try again.');
            }

            // Create ONE payment for the entire checkout (total amount)
            $totalAmount = array_sum(array_map(fn($r) => $r->total_amount, $reservations));
            $payment = Payment::create([
                'reservation_id' => $reservations[0]->id, // Use first reservation ID to maintain schema
                'method' => $request->payment_method,
                'amount' => $totalAmount, // Total amount for entire checkout
                'currency' => 'BDT',
                'status' => in_array($request->payment_method, ['check_payment', 'amarpay']) ? 'pending' : 'completed',
                'transaction_id' => $transactionId,
                'payment_details' => $paymentDetails
            ]);

            Log::info('Single payment created for entire checkout', [
                'payment_id' => $payment->id,
                'total_amount' => $totalAmount,
                'reservation_count' => count($reservations)
            ]);

            // Update all reservations with payment status
            foreach ($reservations as $reservation) {
                $reservation->update([
                    'payment_status' => in_array($request->payment_method, ['check_payment', 'amarpay']) ? 'pending' : 'paid'
                ]);
            }

            Log::info('All reservations updated with payment status', ['count' => count($reservations)]);
            session()->put('last_booking_code', $reservations[0]->booking_code ?? 'UNKNOWN');

            // Clear cart and promo code from session
            session()->forget(['cart', 'applied_promo_code', 'promo_discount']);

            DB::commit();

            // Checkout completed - no notifications sent at this stage
            // Notifications will be sent only after successful payment completion

            // Prepare success message with account information
            $accountMessage = '';
            if (session()->get('guest_checkout', false) && $user && $createAccount) {
                $accountMessage = ' A customer account has been created and you are now logged in! You can access your dashboard to track your bookings.';
            } elseif (session()->get('guest_checkout', false) && $user && !$createAccount) {
                $accountMessage = ' A customer account already exists with your email. You can login at /customer/login to track your bookings.';
            }

            // Redirect based on payment method
            if ($request->payment_method === 'check_payment') {
                return redirect()->route('booking-confirmation')
                    ->with('success', 'Check payment booking confirmed!' . $accountMessage . ' Please mail your check to complete the payment. Your booking reference: ' . $transactionId);
            } elseif ($request->payment_method === 'amarpay') {
                // Initiate Amar Pay payment
                if (empty($reservations)) {
                    throw new \Exception('No reservations available for payment processing.');
                }
                
                $amarPayService = app(\App\Services\AmarPayService::class);
                $result = $amarPayService->initiatePayment($reservations[0], $finalTotal);
                
                if ($result['success']) {
                    return redirect($result['redirect_url']);
                } else {
                    return redirect()->route('payment.failed')
                        ->with('error', 'Failed to initiate payment. Please try again.')
                        ->with('payment_details', [
                            'reason' => 'Payment initiation failed',
                            'error_message' => $result['message'] ?? 'Unknown error'
                        ]);
                }
            } else {
                // Redirect to payment gateway for credit card processing
                if (!$payment) {
                    throw new \Exception('No payment was created. Please try again.');
                }
                
                return redirect()->route('frontend.payment.index')
                    ->with('success', 'Booking confirmed!' . $accountMessage . ' Redirecting to payment gateway...')
                    ->with('payment_id', $payment->id);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            // Debug logging
            Log::error('processBooking error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Check if this is a payment-related error
            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, 'payment') !== false || strpos($errorMessage, 'Payment') !== false) {
                return redirect()->route('payment.failed')
                    ->with('error', 'An error occurred while processing your payment. Please try again.')
                    ->with('payment_details', [
                        'reason' => 'Payment processing error',
                        'error_message' => $errorMessage
                    ]);
            }
            
            return redirect()->back()
                ->with('error', 'An error occurred while processing your booking. Please try again.')
                ->withInput();
        }
    }

    private function getPriceForDate(PackageVariant $variant, string $date): float
    {
        $priceService = app(\App\Services\PriceCalculationService::class);
        return $priceService->getPriceForDate($variant, $date);
    }

    private function generateBookingCode(): string
    {
        do {
            $code = 'BK' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 6));
        } while (Reservation::where('booking_code', $code)->exists());

        return $code;
    }

    public function showConfirmation(Request $request)
    {
        // Get booking code from URL parameter or session
        $bookingCode = $request->get('booking_code') ?? session()->get('last_booking_code');

        if (!$bookingCode) {
            return redirect()->route('frontend.packages.index')
                ->with('error', 'No booking found. Please make a new booking.');
        }

        // Find the first reservation with all related data
        $firstReservation = Reservation::with([
            'customer',
            'packageVariant.package.images',
            'scheduleSlot',
            'payments',
            'promoRedemptions.promoCode'
        ])->where('booking_code', $bookingCode)->first();

        if (!$firstReservation) {
            return redirect()->route('frontend.packages.index')
                ->with('error', 'Booking not found. Please check your booking code.');
        }

        // Get the payment for this reservation
        $payment = $firstReservation->payments()->latest()->first();
        $transactionId = $payment->transaction_id ?? null;

        // Find all reservations that were created in the same checkout session
        // Since we now have one payment per checkout, we need to find all reservations
        // created around the same time as the payment
        $allReservations = [];
        $totalAmount = 0;
        $totalSubtotal = 0;
        $totalDiscount = 0;
        $totalTax = 0;

        if ($payment && $payment->created_at) {
            // Find all reservations created within 5 minutes of the payment creation
            $allReservations = Reservation::with([
                'customer',
                'packageVariant.package.images',
                'scheduleSlot'
            ])->where('customer_id', $firstReservation->customer_id)
              ->where('created_at', '>=', $payment->created_at->subMinutes(5))
              ->where('created_at', '<=', $payment->created_at->addMinutes(5))
              ->orderBy('created_at')
              ->get();

            // Sum up totals from all reservations
            foreach ($allReservations as $reservation) {
                $totalSubtotal += $reservation->subtotal ?? 0;
                $totalDiscount += $reservation->discount_amount ?? 0;
                $totalTax += $reservation->tax_amount ?? 0;
                $totalAmount += $reservation->total_amount ?? 0;
            }
        } else {
            // Fallback to single reservation if no payment found
            $allReservations = [$firstReservation];
            $totalSubtotal = $firstReservation->subtotal ?? 0;
            $totalDiscount = $firstReservation->discount_amount ?? 0;
            $totalTax = $firstReservation->tax_amount ?? 0;
            $totalAmount = $firstReservation->total_amount ?? 0;
        }

        // Use the first reservation for customer info and other details
        $reservation = $firstReservation;

        // Define individual reservation variables for backward compatibility
        $subtotal = $reservation->subtotal ?? 0;
        $discount = $reservation->discount_amount ?? 0;
        $tax = $reservation->tax_amount ?? 0;
        $total = $reservation->total_amount ?? 0;

        // Get promo code if applied (from first reservation)
        $promoCode = null;
        if ($reservation->promoRedemptions->isNotEmpty()) {
            $promoCode = $reservation->promoRedemptions->first()->promoCode;
        }

        // Format payment method for display
        $paymentMethod = $this->formatPaymentMethod($payment->payment_method ?? $payment->method ?? 'unknown');

        return view('frontend.booking-confirmation', compact(
            'reservation',
            'allReservations',
            'payment',
            'subtotal',
            'discount',
            'tax',
            'total',
            'promoCode',
            'paymentMethod',
            'totalAmount',
            'totalSubtotal',
            'totalDiscount',
            'totalTax'
        ));
    }

    private function formatPaymentMethod($method)
    {
        $methods = [
            'credit_card' => 'Credit Card',
            'check_payment' => 'Check Payment',
            'amarpay' => 'Amar Pay',
            'bkash' => 'bKash',
            'nagad' => 'Nagad',
            'rocket' => 'Rocket',
            'upay' => 'Upay',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash Payment'
        ];

        return $methods[$method] ?? ucfirst(str_replace('_', ' ', $method));
    }

    private function isPromoCodeValid(PromoCode $promoCode, float $total): bool
    {
        if ($promoCode->min_spend > $total) {
            return false;
        }

        if ($promoCode->usage_limit_total && $promoCode->redemptions()->count() >= $promoCode->usage_limit_total) {
            return false;
        }

        if ($promoCode->starts_at && now() < $promoCode->starts_at) {
            return false;
        }

        if ($promoCode->ends_at && now() > $promoCode->ends_at) {
            return false;
        }

        return true;
    }

    private function calculateDiscount(PromoCode $promoCode, float $total): float
    {
        if ($promoCode->discount_type === 'percentage') {
            $discount = ($total * $promoCode->discount_value) / 100;
            if ($promoCode->max_discount) {
                $discount = min($discount, $promoCode->max_discount);
            }
        } else {
            $discount = $promoCode->discount_value;
        }

        return $discount;
    }
}
