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
