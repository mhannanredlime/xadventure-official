<?php

namespace App\Services;

use App\Models\PromoCode;
use App\Models\PromoRedemption;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PromoCodeService
{
    public function validatePromoCode(string $code, float $subtotal, ?int $customerId = null): array
    {
        $promoCode = PromoCode::where('code', strtoupper($code))
            ->where('status', 'active')
            ->first();

        if (!$promoCode) {
            return [
                'valid' => false,
                'message' => 'Invalid promo code.'
            ];
        }

        // Check if promo code is within valid date range
        $now = Carbon::now();
        if ($promoCode->starts_at && $now->lt($promoCode->starts_at)) {
            return [
                'valid' => false,
                'message' => 'This promo code is not yet active.'
            ];
        }

        if ($promoCode->ends_at && $now->gt($promoCode->ends_at)) {
            return [
                'valid' => false,
                'message' => 'This promo code has expired.'
            ];
        }

        // Check minimum spend requirement
        if ($promoCode->min_spend && $subtotal < $promoCode->min_spend) {
            return [
                'valid' => false,
                'message' => 'Minimum spend of TK ' . number_format($promoCode->min_spend) . ' required.'
            ];
        }

        // Check total usage limit
        if ($promoCode->usage_limit_total) {
            $totalUsage = PromoRedemption::where('promo_code_id', $promoCode->id)->count();
            if ($totalUsage >= $promoCode->usage_limit_total) {
                return [
                    'valid' => false,
                    'message' => 'This promo code has reached its usage limit.'
                ];
            }
        }

        // Check per-user usage limit
        if ($promoCode->usage_limit_per_user && $customerId) {
            $userUsage = PromoRedemption::where('promo_code_id', $promoCode->id)
                ->where('customer_id', $customerId)
                ->count();
            if ($userUsage >= $promoCode->usage_limit_per_user) {
                return [
                    'valid' => false,
                    'message' => 'You have already used this promo code the maximum number of times.'
                ];
            }
        }

        // Calculate discount
        $discount = $this->calculateDiscount($promoCode, $subtotal);

        return [
            'valid' => true,
            'message' => 'Promo code applied successfully!',
            'promo_code' => $promoCode,
            'discount' => $discount,
            'discount_type' => $promoCode->discount_type,
            'discount_value' => $promoCode->discount_value,
            'max_discount' => $promoCode->max_discount
        ];
    }

    public function calculateDiscount(PromoCode $promoCode, float $subtotal): float
    {
        $discount = 0;

        if ($promoCode->discount_type === 'percentage') {
            $discount = ($subtotal * $promoCode->discount_value) / 100;
        } elseif ($promoCode->discount_type === 'fixed') {
            $discount = $promoCode->discount_value;
        }

        // Apply maximum discount limit
        if ($promoCode->max_discount && $discount > $promoCode->max_discount) {
            $discount = $promoCode->max_discount;
        }

        // Ensure discount doesn't exceed subtotal
        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        return round($discount, 2);
    }

    public function applyPromoCode(string $code, float $subtotal, ?int $customerId = null): array
    {
        $validation = $this->validatePromoCode($code, $subtotal, $customerId);

        if (!$validation['valid']) {
            return $validation;
        }

        $discount = $validation['discount'];
        $total = $subtotal - $discount;

        return [
            'valid' => true,
            'message' => 'Promo code applied successfully!',
            'discount' => $discount,
            'subtotal' => $subtotal,
            'total' => $total,
            'promo_code' => $validation['promo_code']
        ];
    }

    public function recordRedemption(int $promoCodeId, int $reservationId, int $customerId, float $amountDiscounted): PromoRedemption
    {
        return PromoRedemption::create([
            'promo_code_id' => $promoCodeId,
            'reservation_id' => $reservationId,
            'customer_id' => $customerId,
            'amount_discounted' => $amountDiscounted,
            'redeemed_at' => Carbon::now(),
        ]);
    }
}
