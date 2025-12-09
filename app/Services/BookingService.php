<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use App\Models\PackagePrice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class BookingService
{
    protected $amarPayService;
    protected $cartService;
    protected $promoCodeService;

    public function __construct(
        AmarPayService $amarPayService,
        CartService $cartService,
        PromoCodeService $promoCodeService
    ) {
        $this->amarPayService = $amarPayService;
        $this->cartService = $cartService;
        $this->promoCodeService = $promoCodeService;
    }

    /**
     * Process booking from cart items
     */
    public function processBooking(array $data, User $user = null): array
    {
        // 1. Fetch Cart Items
        $cartItems = $this->cartService->getCartItems(); // Uses existing method returning array, let's check return type
        // Wait, getCartItems returns an array of data, not models. 
        // We might need models for deletion or we can fetch them again or update CartService to return models/query.
        // Let's use a helper that returns models for now to be safe, or direct query using CartService scope.
        
        $sessionCartItems = Cart::where('session_id', session()->getId())->get(); // Direct access for now, later refactor CartService to return models

        if ($sessionCartItems->isEmpty()) {
            throw new Exception('Your cart is empty.');
        }

        DB::beginTransaction();

        try {
            // 2. Calculate Totals
            $subtotal = 0;
            foreach ($sessionCartItems as $item) {
                $subtotal += $item->amount * $item->quantity;
            }

            // 3. Apply Promo Code
            $promoDiscount = 0;
            $appliedPromoCode = session()->get('applied_promo_code');
            if ($appliedPromoCode) {
                 $validation = $this->promoCodeService->validatePromoCode($appliedPromoCode->code, $subtotal, $user ? $user->id : null);
                 if ($validation['valid']) {
                     $promoDiscount = $validation['discount'];
                 }
            }

            // 4. Calculate Tax/VAT
            $amountAfterDiscount = max(0, $subtotal - $promoDiscount);
            // Assuming calculateVAT helper is global or we should inject a PricingService?
            // Using global helper as seen in controller
            $taxData = calculateVAT($amountAfterDiscount);
            $totalAmount = $taxData['total'];

            // 5. Create or Find User for customer info
            if (!$user) {
                // Check if user exists by email
                $user = User::where('email', $data['customer_email'])->first();
                
                if (!$user) {
                    // Create new user with customer type
                    $user = User::create([
                        'name' => $data['customer_name'],
                        'email' => $data['customer_email'],
                        'phone' => $data['customer_phone'],
                        'address' => $data['customer_address'] ?? null,
                        'user_type' => 'customer',
                        'password' => bcrypt(Str::random(16)), // Random password for guest checkout
                    ]);
                } else {
                    // Update existing user's info
                    $user->update([
                        'name' => $data['customer_name'],
                        'phone' => $data['customer_phone'],
                        'address' => $data['customer_address'] ?? $user->address,
                    ]);
                }
            }

            // 6. Create Reservations
            $reservations = [];
            $bookingCode = $this->generateBookingCode();

            foreach ($sessionCartItems as $cartItem) {
                // Logic to split cart items to reservations
                 // Resolve Package Price ID logic repeated from controller
                 $resDate = $cartItem->date ?? $cartItem->selected_date ?? now();
                 $day = Carbon::parse($resDate)->format('D');
                 
                 // Re-fetch price to be safe? Or trust cart amount? 
                 // Controller trusted helper. Let's trust logic.
                 
                 $packagePriceId = PackagePrice::where('package_id', $cartItem->package_id)
                    ->where('day', strtolower($day))
                    ->when($cartItem->rider_type_id, function($q) use ($cartItem) {
                        return $q->where('rider_type_id', $cartItem->rider_type_id);
                    })
                    ->value('id');

                 $itemSubtotal = $cartItem->amount * $cartItem->quantity;
                 $itemDiscount = ($subtotal > 0) ? ($itemSubtotal / $subtotal) * $promoDiscount : 0;
                 $itemVAT = ($subtotal > 0) ? ($itemSubtotal / $subtotal) * $taxData['vat'] : 0;
                 $itemTotal = ($itemSubtotal - $itemDiscount) + $itemVAT;

                 $reservation = Reservation::create([
                    'booking_code' => $this->generateBookingCode(), // Unique code per res
                    'user_id' => $user->id,
                    'package_price_id' => $packagePriceId,
                    'package_id' => $cartItem->package_id,
                    'schedule_slot_id' => $cartItem->schedule_slot_id ?? $cartItem->time_slot_id,
                    'date' => $resDate,
                    'report_time' => '09:00:00', // Default
                    'party_size' => $cartItem->quantity,
                    'subtotal' => $itemSubtotal,
                    'discount_amount' => round($itemDiscount, 2),
                    'tax_amount' => round($itemVAT, 2),
                    'total_amount' => round($itemTotal, 2),
                    'booking_status' => 'pending', 
                    'payment_status' => 'pending',
                ]);
                
                $reservations[] = $reservation;
            }

            // 6. Transaction & Payment Record
            $transactionId = generateTransactionId($reservations[0]->booking_code);
            $payment = Payment::create([
                'reservation_id' => $reservations[0]->id,
                'method' => $data['payment_method'],
                'amount' => $totalAmount,
                'currency' => 'BDT',
                'status' => 'pending',
                'transaction_id' => $transactionId,
            ]);

            // 7. Clear Cart
            Cart::whereIn('id', $sessionCartItems->pluck('id'))->delete();

            DB::commit();

            return [
                'success' => true,
                'reservations' => $reservations,
                'payment' => $payment,
                'transaction_id' => $transactionId,
                'total_amount' => $totalAmount
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('BookingService Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function initiateAmarPay(Reservation $reservation, float $amount): array
    {
        return $this->amarPayService->initiatePayment($reservation, $amount);
    }

    private function generateBookingCode(): string
    {
        do {
            $code = 'BK' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 6));
        } while (Reservation::where('booking_code', $code)->exists());

        return $code;
    }
}
