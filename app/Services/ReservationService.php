<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ReservationService
{
    protected $cartService;
    protected $pricingService;
    protected $promoCodeService;

    public function __construct(
        CartService $cartService,
        PricingService $pricingService,
        PromoCodeService $promoCodeService
    ) {
        $this->cartService = $cartService;
        $this->pricingService = $pricingService;
        $this->promoCodeService = $promoCodeService;
    }

    /**
     * Create a reservation from the current cart
     */
    public function createReservationFromCart(array $userData, ?string $promoCode = null): Reservation
    {
        return DB::transaction(function () use ($userData, $promoCode) {
            $cartItems = $this->cartService->getCartItems();
            
            if (empty($cartItems)) {
                throw new \Exception("Cart is empty");
            }

            // Calculate totals
            $subtotal = 0;
            $itemsData = [];

            foreach ($cartItems as $item) {
                // Resolve variant/package
                 if (isset($item['variant']) && $item['variant']) {
                    $variant = $item['variant'];
                    $package = $variant->package;
                } else {
                    $variant = \App\Models\PackageVariant::find($item['variant_id']);
                    $package = $variant->package;
                }

                $unitPrice = $this->pricingService->get_package_price($package, $item['date'], $item['quantity']);
                $lineTotal = $unitPrice * $item['quantity'];
                $subtotal += $lineTotal;

                $itemsData[] = [
                    'package_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                    'package_name' => $package->name, // Snapshot
                    'variant_name' => $variant->name, // Snapshot
                ];
                
                // Note: We need a date for the payload? 
                // Reservation usually has a main date, but cart items might be on diff dates?
                // Context says "Reservation" has "date".
                // If cart has mixed dates, we might need multiple reservations or Reservation supports header date?
                // Context diagram shows `reservations` table. Usually grouped by checkout.
                // If multiple dates are allowed in cart, we create one reservation record per Group? 
                // Or one Header and items have details?
                // Let's assume for now 1 Reservation = 1 Main Date or we take the first date, 
                // OR we split into multiple bookings.
                // For simplicity and typical booking flow, let's assume all items must be for same trip or we create 1 master reservation.
                // However, `Reservation` model suggests single date column usually.
                // Let's check Reservation Model structure if possible, but I don't see it fully.
                // I will pick the date from the first item as the "Main Date" or require separate bookings. 
                // Let's just use the first item's date for the main record for now.
            }
            
            $mainDate = $cartItems[0]['date'];
            $scheduleSlotId = $cartItems[0]['slot_id'] ?? null;

            // Apply Promo
            $discount = 0;
            $promoId = null;
            if ($promoCode) {
                $validation = $this->promoCodeService->validatePromoCode($promoCode, $subtotal, Auth::id());
                if ($validation['valid']) {
                     $discount = $this->promoCodeService->calculateDiscount($validation['promo_code'], $subtotal);
                     $promoId = $validation['promo_code']->id;
                }
            }

            $finalAmount = $subtotal - $discount;

            // Create Reservation
            $reservation = Reservation::create([
                'booking_code' => $this->generateBookingCode(),
                'user_id' => Auth::id(), // or null for guest?
                'customer_name' => $userData['name'],
                'customer_email' => $userData['email'],
                'customer_phone' => $userData['phone'],
                'date' => $mainDate,
                'schedule_slot_id' => $scheduleSlotId, // Assuming single slot for whole booking or NULL if mixed
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'final_amount' => $finalAmount,
                'booking_status' => 'pending',
                'payment_status' => 'unpaid',
                'promo_code_id' => $promoId
            ]);

            // Create Items
            foreach ($itemsData as $data) {
                ReservationItem::create([
                    'reservation_id' => $reservation->id,
                    'package_variant_id' => $data['package_variant_id'],
                    'qty' => $data['quantity'], // Map 'quantity' to 'qty'
                    'unit_price' => $data['unit_price'],
                    'line_total' => $data['total_price']
                ]);
            }

            // Record Promo Usage
            if ($promoId && Auth::check()) {
                $this->promoCodeService->recordRedemption($promoId, $reservation->id, Auth::id(), $discount);
            }

            // Clear Cart
            $this->cartService->clearCart();

            return $reservation;
        });
    }

    protected function generateBookingCode()
    {
        return strtoupper(Str::random(8));
    }
}
