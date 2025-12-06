<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderPlacingMakePaymentRequest;
use App\Http\Requests\XCartUpdateRequest;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Package;
use App\Models\PackageVariant;
use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\Reservation;
use App\Models\ScheduleSlot;
use App\Models\User;
use App\Services\AmarPayService;
use App\Services\PhoneNumberService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function processToCheckout(Request $request)
    {
        // Trim and cast inputs
        $request->merge([
            'selected_date' => trim($request->selected_date),
            'time_slot_id' => (int) $request->time_slot_id,
        ]);

        // Parse selected date using Carbon
        try {
            $selectedDate = Carbon::parse($request->selected_date);
        } catch (\Exception $e) {
            return back()->withErrors(['selected_date' => 'Invalid date format']);
        }
        $time_slot = ScheduleSlot::find($request->time_slot_id);

        // Fetch guest cart items and update selected date and time slot
        $guestCartItems = getGuestCartItems();
        foreach ($guestCartItems as $cartItem) {
            $cartItem->selected_date = $selectedDate;
            $cartItem->time_slot_id = $request->time_slot_id;
            $cartItem->save();
        }

        // Pass data to the view
        return view('frontend.shopping-cart', [
            'guestCartItems' => $guestCartItems,
            'time_slot' => $time_slot,
            'selected_date' => format_full_date($selectedDate), // ensure format
        ]);
    }

    public function addToCart(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'package_id' => 'required|integer|exists:packages,id',
                'rider_type_id' => 'nullable|integer', // optional
            ]);

            $packageId = $request->input('package_id');
            $riderTypeId = $request->input('rider_type_id'); // null if not provided
            $sessionId = session()->getId();

            $package = Package::where('id', $packageId)
                ->where('is_active', 1)
                ->first();

            if (! $package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected package is invalid or unavailable.',
                ]);
            }

            $today = strtolower(Carbon::now()->format('D'));

            $price = get_package_price($package, $today, $riderTypeId);

            if (! $price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Price not available for this package today.',
                ]);
            }

            $cartItem = Cart::firstOrNew([
                'session_id' => $sessionId,
                'package_id' => $packageId,
                'package_type' => $package->type,
            ], [
                'cart_amount' => $price,
                'rider_type_id' => $riderTypeId, // optional, can be null
            ]);

            $cartItem->quantity = ($cartItem->quantity ?? 0) + 1;
            $cartItem->save();

            return response()->json([
                'success' => true,
                'cart_count' => Cart::where('session_id', $sessionId)->count(),
                'cart_total_items' => Cart::where('session_id', $sessionId)->sum('quantity'),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while adding to cart.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateCart(XCartUpdateRequest $request)
    {
        $validated = $request->validated();
        // Fetch cart item
        $cartItem = Cart::where('cart_uuid', $validated['cart_uuid'])->firstOrFail();
        switch ($validated['change']) {
            case 'addition':
                $cartItem->quantity += 1;
                $cartItem->save();
                ToastMagic::success('Cart updated successfully!');
                break;

            case 'minus':
                if ($cartItem->quantity <= 1) {
                    // Quantity 1 already, cannot decrease further
                    ToastMagic::error('Quantity cannot be less than 1.');
                } else {
                    $cartItem->quantity -= 1;
                    $cartItem->save();
                    ToastMagic::success('Cart updated successfully!');
                }
                break;

            default:
                return redirect()->back()->with('error', 'Invalid action.');
        }

        return redirect()->back();
    }

    /**
     * Get availability data for cart item editing
     */
    public function getCartItemAvailability(Request $request)
    {
        $cartKey = $request->get('cart_key');
        $cart = session()->get('cart', []);

        if (! isset($cart[$cartKey])) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in cart.',
            ], 404);
        }

        $cartItem = $cart[$cartKey];
        $variant = \App\Models\PackageVariant::find($cartItem['variant_id']);

        if (! $variant) {
            return response()->json([
                'success' => false,
                'message' => 'Package variant not found.',
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
                'title' => $package->name.' - '.$variant->variant_name,
                'image' => $package->display_image_url ?? asset('admin/images/pack-1.png'),
                'unit_price' => $availability['final_price'],
                'current_quantity' => $cartItem['quantity'],
                'available_vehicles' => $availability['total_vehicles'], // Use total_vehicles like admin, not total_available
                'available_capacity' => $availability['available_capacity'],
                'price_tag' => $availability['price_tag'],
                'has_discount' => $availability['has_discount'],
                'default_price' => $availability['default_price'],
            ],
        ]);
    }

    public function removeFromCart(Request $request, $cart_uuid)
    {
        // dd($request->all());
        $cartItem = Cart::where('cart_uuid', $cart_uuid)->first();
        if (! $cartItem) {
            return redirect()->route('frontend.process-to-checkout')
                ->with('error', 'Item not found in cart.');
        }
        $cartItem->delete();

        return redirect()->back()->with('success', 'Item removed from cart successfully.');
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
                $newCartKey = $item['variant_id'].'_'.$date.'_'.$timeSlot;

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
            'message' => 'Booking details updated successfully.',
        ]);
    }

    public function validatePromoCode(Request $request)
    {
        $request->validate([
            'promo_code' => 'required|string|max:50',
        ]);

        $promoCode = $request->promo_code;

        // Fetch guest cart items using your helper
        $guestCartItems = getGuestCartItems();

        if ($guestCartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.',
            ], 422);
        }

        // Calculate subtotal using helper get_package_price()
        $subtotal = 0;

        foreach ($guestCartItems as $item) {
            $package = $item->package;

            if (! $package) {
                continue;
            }

            // Convert reservation date to day name (sun, mon, tue...)
            $day = strtolower(Carbon::parse($item->reservation_date)->format('D'));

            // Fetch price from helper
            $price = get_package_price($package, $day, $item->rider_type_id);

            if (! $price) {
                continue;
            }

            $subtotal += $price * $item->quantity;
        }

        if ($subtotal <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to calculate cart total.',
            ], 422);
        }

        // Validate promo code
        $promoCodeService = app(\App\Services\PromoCodeService::class);
        $result = $promoCodeService->validatePromoCode($promoCode, $subtotal);

        if (! $result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        // Save promo data
        session()->put('applied_promo_code', $result['promo_code']);
        session()->put('promo_discount', $result['discount']);

        $finalTotal = max($subtotal - $result['discount'], 0);

        return response()->json([
            'success' => true,
            'message' => 'Promo code applied successfully!',
            'promo_code' => $promoCode,
            'discount' => $result['discount'],
            'discount_formatted' => 'TK '.number_format($result['discount'], 2),
            'subtotal' => $subtotal,
            'subtotal_formatted' => 'TK '.number_format($subtotal, 2),
            'final_total' => $finalTotal,
            'final_total_formatted' => 'TK '.number_format($finalTotal, 2),
        ]);
    }

    public function removePromoCode(Request $request)
    {
        // Remove promo code from session
        session()->forget('applied_promo_code');
        session()->forget('promo_discount');

        return response()->json([
            'success' => true,
            'message' => 'Promo code removed successfully!',
        ]);
    }

    public function checkout(Request $request)
    {

        $data['guestCartItems'] = Cart::with('package')
            ->where('session_id', session()->getId())
            ->where('created_at', '>=', now()->subMinutes(env('SESSION_LIFETIME')))
            ->get();

        return view('frontend.checkout.index', $data);
    }

    public function processBooking(OrderPlacingMakePaymentRequest $request, AmarPayService $amarPayService)
    {
        // Validate request
        $validated = $request->validated();

        $phoneService = new PhoneNumberService;
        $formattedPhone = $phoneService->validateAndFormat($request->customer_phone)['formatted'] ?? $request->customer_phone;

        $cartItems = getGuestCartItems(); // Fetch guest cart items
        if ($cartItems->isEmpty()) {
            return redirect()->route('frontend.process-to-checkout')->with('error', 'Your cart is empty.');
        }

        DB::beginTransaction();

        try {
            // Create account logic
            $createAccount = $request->boolean('create_account');
            $email = $request->customer_email;
            $phone = $formattedPhone;
            $user = null;

            if ($createAccount) {
                $user = User::where('email', $email)->orWhere('phone', $phone)->first();

                if (! $user) {
                    // Create new user
                    $user = User::create([
                        'name' => $request->customer_name,
                        'email' => $email,
                        'phone' => $phone,
                        'address' => $request->customer_address,
                        'password' => bcrypt($request->password),
                        'is_admin' => false,
                    ]);
                } else {
                    // Update existing user
                    $user->update([
                        'name' => $request->customer_name,
                        'phone' => $phone,
                        'address' => $request->customer_address,
                    ]);

                    if ($request->filled('password')) {
                        $user->update(['password' => bcrypt($request->password)]);
                    }
                }
            }

            // Calculate subtotal
            $subtotal = 0;
            foreach ($cartItems as $cartItem) {
                if (! $cartItem->package) {
                    continue;
                }
                $price = get_package_price($cartItem->package, now()->format('D'));
                $subtotal += $price * $cartItem->quantity;
            }

            // Apply promo code
            $promoDiscount = 0;
            $appliedPromoCode = session()->get('applied_promo_code');
            if ($appliedPromoCode) {
                $userId = $user->id ?? null;
                $promoValidation = app(\App\Services\PromoCodeService::class)
                    ->validatePromoCode($appliedPromoCode->code, $subtotal, $userId);

                if ($promoValidation['valid']) {
                    $promoDiscount = $promoValidation['discount'];
                }
            }

            // Calculate VAT & total amount
            $amountAfterDiscount = max(0, $subtotal - $promoDiscount);
            $taxData = calculateVAT($amountAfterDiscount);
            $totalAmount = $taxData['total'];

            // Create reservations
            $reservations = [];
            foreach ($cartItems as $cartItem) {
                if (! $cartItem->package) {
                    continue;
                }

                $price = get_package_price($cartItem->package, now()->format('D'));
                $itemSubtotal = $price * $cartItem->quantity;

                // Allocate promo & VAT proportionally
                $itemDiscount = ($subtotal > 0) ? ($itemSubtotal / $subtotal) * $promoDiscount : 0;
                $itemVAT = ($subtotal > 0) ? ($itemSubtotal / $subtotal) * $taxData['vat'] : 0;
                $itemTotal = ($itemSubtotal - $itemDiscount) + $itemVAT;

                $reservation = Reservation::create([
                    'booking_code' => $this->generateBookingCode(),
                    'user_id' => $user->id ?? null,
                    'customer_name' => $request->customer_name,
                    'customer_email' => $request->customer_email,
                    'customer_phone' => $formattedPhone,
                    'package_variant_id' => null,
                    'package_id' => $cartItem->package_id,
                    'schedule_slot_id' => $cartItem->time_slot_id,
                    'date' => $cartItem->selected_date,
                    'report_time' => '09:00:00',
                    'party_size' => $cartItem->quantity ?? 0,
                    'subtotal' => $itemSubtotal,
                    'discount_amount' => round($itemDiscount, 2),
                    'tax_amount' => round($itemVAT, 2),
                    'total_amount' => round($itemTotal, 2),
                    'booking_status' => in_array($request->payment_method, ['check_payment', 'amarpay']) ? 'pending' : 'confirmed',
                    'payment_status' => in_array($request->payment_method, ['check_payment', 'amarpay']) ? 'pending' : 'paid',
                ]);

                $reservations[] = $reservation;
            }

            if (empty($reservations)) {
                throw new Exception('No valid reservations could be created.');
            }
            Cart::whereIn('id', $cartItems->pluck('id'))->delete();

            // Create Payment record
            $transactionId = generateTransactionId($reservations[0]->booking_code);
            $payment = Payment::create([
                'reservation_id' => $reservations[0]->id,
                'method' => $request->payment_method,
                'amount' => $totalAmount,
                'currency' => 'BDT',
                'status' => in_array($request->payment_method, ['check_payment', 'amarpay']) ? 'pending' : 'completed',
                'transaction_id' => $transactionId,
                'payment_details' => [],
            ]);

            Log::info('Transaction ID: '.$transactionId.' | Payment ID: '.$payment->id.' | Method: '.$request->payment_method);
            cleanOldCarts(session()->getId());
            DB::commit();

            // Account messages
            $accountMessage = '';
            if ($user && $createAccount) {
                $accountMessage = ' A customer account has been created and you are now logged in! You can access your dashboard to track your bookings.';
            } elseif ($user && ! $createAccount) {
                $accountMessage = ' A customer account already exists with your email. You can login at /customer/login to track your bookings.';
            }

            // Redirect based on payment method
            if ($request->payment_method === 'check_payment') {
                return redirect()->route('booking.confirmation')
                    ->with('success', 'Check payment booking confirmed!'.$accountMessage.' Please mail your check to complete the payment. Your booking reference: '.$transactionId);

            } elseif ($request->payment_method === 'amarpay') {
                $result = $amarPayService->initiatePayment($reservations[0], $totalAmount);
                if ($result['success']) {
                    return redirect($result['redirect_url']);
                } else {
                    return redirect()->route('payment.failed')
                        ->with('error', 'Failed to initiate payment. Please try again.')
                        ->with('payment_details', [
                            'reason' => 'Payment initiation failed',
                            'error_message' => $result['message'] ?? 'Unknown error',
                        ]);
                }
            } else {
                return redirect()->route('frontend.payment.index')
                    ->with('success', 'Booking confirmed!'.$accountMessage.' Redirecting to payment gateway...')
                    ->with('payment_id', $payment->id);
            }

            

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('processBooking error', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Booking failed: '.$e->getMessage());
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
            $code = 'BK'.date('Ymd').strtoupper(substr(md5(uniqid()), 0, 6));
        } while (Reservation::where('booking_code', $code)->exists());

        return $code;
    }

    public function showConfirmation(Request $request, ?string $bookingCode = null)
    {
        $bookingCode = $request->query('booking_code');
        $reservation = Reservation::with([
            'customer',
            'packageVariant.package.images',
            'scheduleSlot',
            'payments',
            'promoRedemptions.promoCode',
        ])->where('booking_code', $bookingCode)->firstOrFail();

        // Get the latest payment
        $payment = $reservation->payments()->latest()->first();
        $paymentCreatedAt = $payment?->created_at;

        // Get all reservations in the same checkout (within 5 minutes of payment)
        $allReservations = Reservation::with([
            'customer',
            'packageVariant.package.images',
            'scheduleSlot',
        ])
            ->when($paymentCreatedAt, function ($query) use ($reservation, $paymentCreatedAt) {
                $query->where('customer_id', $reservation->customer_id)
                    ->whereBetween('created_at', [
                        $paymentCreatedAt->copy()->subMinutes(5),
                        $paymentCreatedAt->copy()->addMinutes(5),
                    ]);
            })
            ->orderBy('created_at')
            ->get();

        // Fallback to single reservation if no other reservations found
        if ($allReservations->isEmpty()) {
            $allReservations = collect([$reservation]);
        }

        // Calculate totals
        $totalSubtotal = $allReservations->sum(fn ($r) => $r->subtotal ?? 0);
        $totalDiscount = $allReservations->sum(fn ($r) => $r->discount_amount ?? 0);
        $totalTax = $allReservations->sum(fn ($r) => $r->tax_amount ?? 0);
        $totalAmount = $allReservations->sum(fn ($r) => $r->total_amount ?? 0);

        // Use first reservation for customer info and promo code
        $subtotal = $reservation->subtotal ?? 0;
        $discount = $reservation->discount_amount ?? 0;
        $tax = $reservation->tax_amount ?? 0;
        $total = $reservation->total_amount ?? 0;
        $promoCode = $reservation->promoRedemptions->first()?->promoCode;

        // Format payment method
        $paymentMethod = $this->formatPaymentMethod($payment?->payment_method ?? $payment?->method ?? 'unknown');

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
            'cash' => 'Cash Payment',
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
