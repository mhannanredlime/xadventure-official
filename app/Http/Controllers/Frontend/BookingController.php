<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderPlacingMakePaymentRequest;
use App\Http\Requests\XCartUpdateRequest;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Package;

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

    protected $cartService;
    protected $bookingService;

    public function __construct(
        \App\Services\CartService $cartService,
        \App\Services\BookingService $bookingService
    ) {
        $this->cartService = $cartService;
        $this->bookingService = $bookingService;
    }

    /**
     * Display the checkout page
     */
    public function checkout(Request $request)
    {
        $guestCartItems = $this->cartService->getCartItems();
        
        if ($guestCartItems->isEmpty()) {
            return redirect()->route('packages.custom.index')
                ->with('error', 'Your cart is empty. Please add packages to proceed.');
        }
        
        return view('frontend.checkout.index', [
            'guestCartItems' => $guestCartItems,
            'appliedPromoCode' => session('applied_promo_code'),
        ]);
    }

    public function processToCheckout(Request $request)
    {
        // Trim and cast inputs
        $request->merge([
            'selected_date' => trim($request->selected_date),
            'time_slot_id' => (int) $request->time_slot_id,
        ]);

        try {
            $selectedDate = Carbon::parse($request->selected_date);
        } catch (\Exception $e) {
            return back()->withErrors(['selected_date' => 'Invalid date format']);
        }
        $time_slot = ScheduleSlot::find($request->time_slot_id);

        // Fetch guest cart items and update selected date and time slot
        $guestCartItems = Cart::where('session_id', session()->getId())->get(); // Use model directly for bulk update for now
        foreach ($guestCartItems as $cartItem) {
            $cartItem->selected_date = $selectedDate;
            $cartItem->time_slot_id = $request->time_slot_id;
            $cartItem->save();
        }

        // Pass data to the view
        return view('frontend.shopping-cart', [
            // 'guestCartItems' => $guestCartItems, // View will fetch via service or we pass fresh
            'guestCartItems' => $this->cartService->getCartItems(), // Use service to get detailed array
            'time_slot' => $time_slot,
            'selected_date' => format_full_date($selectedDate),
        ]);
    }

    public function addToCart(Request $request)
    {
        try {
            $request->validate([
                'package_id' => 'required|integer|exists:packages,id',
                'rider_type_id' => 'nullable|integer',
                'quantity' => 'nullable|integer', // Allow negative for decrement if needed, or positive for bulk add
            ]);

            $package = Package::where('id', $request->package_id)->where('is_active', 1)->first();

            if (!$package) {
                return response()->json(['success' => false, 'message' => 'Invalid package.']);
            }

            $today = strtolower(Carbon::now()->format('D'));
            $price = get_package_price($package, $today, $request->rider_type_id);

            if ($price === null) {
                return response()->json(['success' => false, 'message' => 'Price not available today.']);
            }

            $quantity = $request->input('quantity', 1);

            $this->cartService->addToCart([
                'package_id' => $package->id,
                'rider_type_id' => $request->rider_type_id,
                'quantity' => $quantity,
                'amount' => $price,
            ]);

            return response()->json([
                'success' => true,
                'cart_count' => $this->cartService->getCartTotalItems(),
                'cart_total_items' => $this->cartService->getCartCount(), // check naming
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding to cart.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function removePackageFromCart(Request $request)
    {
        try {
             $request->validate([
                'package_id' => 'required|integer',
            ]);

            // We need to find the cart item UUID to use the existing service method,
            // or we need a new service method "removeByPackageId".
            // Direct DB query here for now to find UUID as per plan to enable removal without UUID on frontend.
            
            $sessionId = session()->getId();
            $userId = auth()->id();

            $query = Cart::query();
            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                 $query->where('session_id', $sessionId);
            }
            
            $cartItem = $query->where('package_id', $request->package_id)->first();

            if ($cartItem) {
                $this->cartService->removeFromCart($cartItem->uuid);
            }

             return response()->json([
                'success' => true,
                'cart_count' => $this->cartService->getCartTotalItems(),
                'cart_total_items' => $this->cartService->getCartCount(),
            ]);

        } catch (Exception $e) {
             return response()->json([
                'success' => false,
                'message' => 'Error removing from cart.',
            ], 500);
        }
    }

    public function updateCart(XCartUpdateRequest $request)
    {
        $validated = $request->validated();
        $success = $this->cartService->updateCartItem($validated['cart_uuid'], $validated['change']);

        if ($success) {
            ToastMagic::success('Cart updated successfully!');
        } else {
             if ($validated['change'] === 'minus') {
                 ToastMagic::error('Minimum quantity reached.');
             } else {
                 ToastMagic::error('Cart item not found.');
             }
        }
        return redirect()->back();
    }

    /**
     * Get availability data for cart item editing
     */
    public function getCartItemAvailability(Request $request)
    {
        $cartKey = $request->get('cart_key'); // UUID or ID
        // Assuming cart_key is UUID from frontend
        
        $cartItem = Cart::where('uuid', $cartKey)->first();

        if (! $cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in cart.',
            ], 404);
        }

        $package = $cartItem->package;
        
        // Simpler availability check without PackageVariant
        // We need to resolve price for the selected date
        $date = $cartItem->selected_date ?? $cartItem->date;
        $day = $date ? strtolower(Carbon::parse($date)->format('D')) : strtolower(now()->format('D'));
        
        $price = get_package_price($package, $day, $cartItem->rider_type_id);
        
        // Mock availability for now or use real service if available
        // $availability = ... 
        
        return response()->json([
            'success' => true,
            'data' => [
                'title' => $package->name . ($cartItem->rider_type_id ? ' - Rider Type ' . $cartItem->rider_type_id : ''),
                'image' => $package->display_image_url ?? asset('admin/images/pack-1.png'),
                'unit_price' => $price,
                'current_quantity' => $cartItem->quantity,
                'available_vehicles' => 10, // Placeholder
                'available_capacity' => 10, // Placeholder
                'price_tag' => 'Standard',
                'has_discount' => false,
                'default_price' => $price,
            ],
        ]);
    }

    public function removeFromCart(Request $request, $cart_uuid)
    {
        $this->cartService->removeFromCart($cart_uuid);
        return redirect()->back()->with('success', 'Item removed from cart successfully.');
    }

    public function processBooking(OrderPlacingMakePaymentRequest $request)
    {
        $validated = $request->validated();
        
        // Format Phone
        $phoneService = new PhoneNumberService;
        $formattedPhone = $phoneService->validateAndFormat($request->customer_phone)['formatted'] ?? $request->customer_phone;
        $validated['customer_phone'] = $formattedPhone;

        // Check for User creation/update - this might still need to be here or moved to service?
        // Service expects data array. Let's handle User logic here or pass flags to service. 
        // Plan said: "BookingService: Centralize booking creation and processing".
        // Let's create user here for now to keep service focused on Booking/Payment structure, or move user logic to a UserService?
        // To stick to the plan of "maintainable way", let's keep controller thin. 
        // But for safe-to-auto-run refactor, let's replicate existing behavior for User but move Transaction/Reservation to Service.
        
        // ... (User logic omitted/kept same or moved? Let's keep User logic inline for now as it interacts with Auth/Session heavily 
        // and just pass the user object to service).
        
        try {
            DB::beginTransaction();

            // Handle User Logic (Can be extracted later to CustomerService)
             $createAccount = $request->boolean('create_account');
             $user = null;
             if ($createAccount) {
                 $user = User::where('email', $request->customer_email)->orWhere('phone', $formattedPhone)->first();
                 if (!$user) {
                     $user = User::create([
                         'name' => $request->customer_name,
                         'email' => $request->customer_email,
                         'phone' => $formattedPhone,
                         'address' => $request->customer_address,
                         'password' => bcrypt($request->password),
                         'is_admin' => false,
                     ]);
                 }
             }

            $result = $this->bookingService->processBooking($validated, $user);
            $accountMessage = ($user && $createAccount) ? ' A customer account has been created.' : '';

            DB::commit();

            // Redirects
            if ($request->payment_method === 'check_payment') {
                return redirect()->route('booking.confirmation')
                    ->with('success', 'Check booking confirmed! ' . $accountMessage . ' Ref: ' . $result['transaction_id']);
            } elseif ($request->payment_method === 'amarpay') {
                $amarResult = $this->bookingService->initiateAmarPay($result['reservations'][0], $result['total_amount']);
                if ($amarResult['success']) {
                    return redirect($amarResult['redirect_url']);
                }
                return redirect()->route('payment.failed')->with('error', 'AmarPay init failed.');
            } else {
                return redirect()->route('payment.index')
                    ->with('success', 'Booking confirmed! ' . $accountMessage)
                    ->with('payment_id', $result['payment']->id);
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Controller Booking Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Booking failed: ' . $e->getMessage());
        }
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
            'package.images',
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
            'package.images',
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

    /**
     * Validate and apply a promo code
     */
    public function validatePromoCode(Request $request)
    {
        $request->validate([
            'promo_code' => 'required|string|max:50',
        ]);

        $guestCartItems = $this->cartService->getCartItems();
        
        if ($guestCartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.',
            ]);
        }

        // Calculate subtotal from cart
        $subtotal = $guestCartItems->sum(fn($item) => $item['amount'] * $item['quantity']);

        // Use PromoCodeService to validate
        $promoCodeService = app(\App\Services\PromoCodeService::class);
        $result = $promoCodeService->validatePromoCode(
            $request->promo_code,
            $subtotal,
            auth()->id()
        );

        if ($result['valid']) {
            // Store promo code and discount in session
            session(['applied_promo_code' => $result['promo_code']]);
            session(['promo_discount' => $result['discount']]);
            
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'discount' => $result['discount'],
                'discount_formatted' => 'TK ' . number_format($result['discount'], 2),
                'promo_code' => [
                    'code' => $result['promo_code']->code,
                    'discount_type' => $result['promo_code']->discount_type,
                    'discount_value' => $result['promo_code']->discount_value,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ]);
    }

    /**
     * Remove applied promo code
     */
    public function removePromoCode(Request $request)
    {
        session()->forget('applied_promo_code');
        session()->forget('promo_discount');

        return response()->json([
            'success' => true,
            'message' => 'Promo code removed successfully.',
        ]);
    }

}
