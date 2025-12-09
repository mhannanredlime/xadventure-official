<?php

namespace App\Services;

use App\Models\PromoCode;
use Illuminate\Support\Facades\DB;
use Exception;

class PromoCodeService
{
    /**
     * Create a new promo code.
     *
     * @param array $data
     * @return PromoCode
     * @throws Exception
     */
    public function createPromoCode(array $data): PromoCode
    {
        return DB::transaction(function () use ($data) {
            // Logic for 'applies_to' cleanup
            $data = $this->prepareData($data);
            return PromoCode::create($data);
        });
    }

    /**
     * Update an existing promo code.
     *
     * @param PromoCode $promoCode
     * @param array $data
     * @return PromoCode
     * @throws Exception
     */
    public function updatePromoCode(PromoCode $promoCode, array $data): PromoCode
    {
        return DB::transaction(function () use ($promoCode, $data) {
             $data = $this->prepareData($data);
             $promoCode->update($data);
             return $promoCode;
        });
    }

    /**
     * Delete a promo code.
     *
     * @param PromoCode $promoCode
     * @return bool
     * @throws Exception
     */
    public function deletePromoCode(PromoCode $promoCode): bool
    {
        return $promoCode->delete();
    }

    /**
     * Toggle status or perform specific status updates.
     * 
     * @param PromoCode $promoCode
     * @return string New Status
     */
    public function toggleStatus(PromoCode $promoCode): string
    {
         $newStatus = $promoCode->status === 'active' ? 'inactive' : 'active';
         $promoCode->update(['status' => $newStatus]);
         return $newStatus;
    }
    
    /**
     * Get filtered promo codes.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFilteredPromoCodes(array $filters = [])
    {
        $query = PromoCode::with(['package', 'vehicleType']);

        if (!empty($filters['package_id'])) {
            $query->where('package_id', $filters['package_id']);
        }
        if (!empty($filters['vehicle_type_id'])) {
            $query->where('vehicle_type_id', $filters['vehicle_type_id']);
        }
        if (!empty($filters['status'])) {
             // If filter is 'expired', we might use scope or raw query, 
             // but since status attribute is accessor, direct query on DB column 'status' might not catch date-based expiry.
             // However, for admin panel filtering usually relies on DB columns. 
             // If we want to strictly filter by calculated status, it's heavier. 
             // For now, let's assume 'status' column filter, or if 'expired' is passed, check dates.
             if ($filters['status'] === 'expired') {
                 $query->where(function($q) {
                     $q->where('status', 'expired') // if explicitly set in DB
                       ->orWhere('ends_at', '<', now());
                 });
             } else {
                 $query->where('status', $filters['status']);
             }
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
    
    /**
     * Check if code exists.
     */
    public function checkCodeAvailability(string $code, ?int $excludeId = null): bool
    {
        $query = PromoCode::where('code', $code);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return !$query->exists();
    }

    /**
     * Validate a promo code for frontend checkout.
     *
     * @param string $code
     * @param float $subtotal
     * @param int|null $userId
     * @return array ['valid' => bool, 'message' => string, 'discount' => float, 'promo_code' => PromoCode|null]
     */
    public function validatePromoCode(string $code, float $subtotal, ?int $userId = null): array
    {
        $promoCode = PromoCode::where('code', strtoupper(trim($code)))->first();

        // Check if promo code exists
        if (!$promoCode) {
            return [
                'valid' => false,
                'message' => 'Invalid promo code.',
                'discount' => 0,
                'promo_code' => null,
            ];
        }

        // Check if active
        if ($promoCode->status !== 'active') {
            return [
                'valid' => false,
                'message' => 'This promo code is no longer active.',
                'discount' => 0,
                'promo_code' => null,
            ];
        }

        // Check date validity
        $now = now();
        if ($promoCode->starts_at && $promoCode->starts_at->isAfter($now)) {
            return [
                'valid' => false,
                'message' => 'This promo code is not yet valid.',
                'discount' => 0,
                'promo_code' => null,
            ];
        }

        if ($promoCode->ends_at && $promoCode->ends_at->isBefore($now)) {
            return [
                'valid' => false,
                'message' => 'This promo code has expired.',
                'discount' => 0,
                'promo_code' => null,
            ];
        }

        // Check minimum spend
        if ($promoCode->min_spend && $subtotal < $promoCode->min_spend) {
            return [
                'valid' => false,
                'message' => 'Minimum spend of TK ' . number_format($promoCode->min_spend, 2) . ' required.',
                'discount' => 0,
                'promo_code' => null,
            ];
        }

        // Check total usage limit
        if ($promoCode->usage_limit_total) {
            $totalUsed = $promoCode->redemptions()->count();
            if ($totalUsed >= $promoCode->usage_limit_total) {
                return [
                    'valid' => false,
                    'message' => 'This promo code has reached its usage limit.',
                    'discount' => 0,
                    'promo_code' => null,
                ];
            }
        }

        // Check per-user usage limit (uses customer_id in promo_redemptions table)
        if ($userId && $promoCode->usage_limit_per_user) {
            $userUsed = $promoCode->redemptions()->where('customer_id', $userId)->count();
            if ($userUsed >= $promoCode->usage_limit_per_user) {
                return [
                    'valid' => false,
                    'message' => 'You have already used this promo code the maximum number of times.',
                    'discount' => 0,
                    'promo_code' => null,
                ];
            }
        }

        // Calculate discount
        $discount = 0;
        if ($promoCode->discount_type === 'percentage') {
            $discount = ($subtotal * $promoCode->discount_value) / 100;
            // Apply max discount cap if set
            if ($promoCode->max_discount && $discount > $promoCode->max_discount) {
                $discount = $promoCode->max_discount;
            }
        } else {
            // Fixed discount
            $discount = min($promoCode->discount_value, $subtotal);
        }

        return [
            'valid' => true,
            'message' => 'Promo code applied successfully!',
            'discount' => round($discount, 2),
            'promo_code' => $promoCode,
        ];
    }

    /**
     * Prepare data for save/update (handle applies_to nulling).
     */
    private function prepareData(array $data): array
    {
        if (isset($data['applies_to'])) {
            if ($data['applies_to'] === 'all') {
                $data['package_id'] = null;
                $data['vehicle_type_id'] = null;
            } elseif ($data['applies_to'] === 'package') {
                $data['vehicle_type_id'] = null;
            } elseif ($data['applies_to'] === 'vehicle_type') {
                $data['package_id'] = null;
            }
        }
        return $data;
    }
}
