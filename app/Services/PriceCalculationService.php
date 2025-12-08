<?php

namespace App\Services;

use App\Models\PackageVariant;
use App\Models\PriceOverride;
use Carbon\Carbon;

class PriceCalculationService
{
    protected $vehicleAvailabilityService;

    public function __construct(VehicleAvailabilityService $vehicleAvailabilityService)
    {
        $this->vehicleAvailabilityService = $vehicleAvailabilityService;
    }

    public function getPriceForDate(\App\Models\Package $package, $date, $riderTypeId = null): float
    {
        // Resolve PackagePrice for ID if possible, for overrides
        // We need package_price_id for PriceOverride.
        // So we must find the PackagePrice record first.
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $dayName = strtolower(Carbon::parse($date)->format('D')); // 'sun', 'mon' etc.
        
        // Find PackagePrice
        $packagePrice = $package->packagePrices()
            ->where('day', $dayName)
            ->when($riderTypeId, function($q) use ($riderTypeId) {
                return $q->where('rider_type_id', $riderTypeId);
            })
            ->first();
            
        if (!$packagePrice) return 0;

        // Check for price override
        $override = PriceOverride::where([
            'package_price_id' => $packagePrice->id,
            'date' => $date
        ])->first();

        if ($override) {
            return $override->price_amount;
        }

        return $packagePrice->price;
    }

    public function getPriceTagForDate(\App\Models\Package $package, $date, $riderTypeId = null): ?string
    {
        $dayName = strtolower(Carbon::parse($date)->format('D'));
        $packagePrice = $package->packagePrices()
            ->where('day', $dayName)
            ->when($riderTypeId, function($q) use ($riderTypeId) {
                return $q->where('rider_type_id', $riderTypeId);
            })
            ->first();
            
        if (!$packagePrice) return null;

        $override = PriceOverride::where([
            'package_price_id' => $packagePrice->id,
            'date' => $date
        ])->first();

        return $override ? $override->price_tag : null;
    }

    public function getDefaultPriceForDate(\App\Models\Package $package, $date, $riderTypeId = null): float
    {
       // Default is the base price from PackagePrice table
       $dayName = strtolower(Carbon::parse($date)->format('D'));
       $packagePrice = $package->packagePrices()
            ->where('day', $dayName)
            ->when($riderTypeId, function($q) use ($riderTypeId) {
                return $q->where('rider_type_id', $riderTypeId);
            })
            ->first();
       
       return $packagePrice ? $packagePrice->price : 0;
    }

    /**
     * Get comprehensive pricing and availability data for a package
     */
    public function getPricingAndAvailabilityForDate(\App\Models\Package $package, $date, $scheduleSlotId = null, $riderTypeId = null, array $excludeCartItems = []): array
    {
        $finalPrice = $this->getPriceForDate($package, $date, $riderTypeId);
        $defaultPrice = $this->getDefaultPriceForDate($package, $date, $riderTypeId);
        $priceTag = $this->getPriceTagForDate($package, $date, $riderTypeId);
        
        // Get vehicle availability
        $availability = $this->vehicleAvailabilityService->calculateAvailabilityForPackage(
            $package, 
            $date, 
            $scheduleSlotId,
            $riderTypeId,
            $excludeCartItems
        );

        return [
            'final_price' => $finalPrice,
            'default_price' => $defaultPrice,
            'price_tag' => $priceTag,
            'is_available' => $availability['is_available'],
            'available_capacity' => $availability['available_capacity'],
            'total_available' => $availability['total_available'],
            'total_vehicles' => $availability['total_vehicles'], 
            'total_booked' => $availability['total_booked'],
            'vehicle_types' => $availability['vehicle_types'],
            'has_discount' => $finalPrice < $defaultPrice,
            'discount_amount' => max(0, $defaultPrice - $finalPrice),
            'discount_percentage' => $defaultPrice > 0 ? round((($defaultPrice - $finalPrice) / $defaultPrice) * 100, 2) : 0,
            'is_day_off' => $availability['is_day_off'] ?? false,
        ];
    }

    /**
     * Validate that a premium price is higher than the base price
     */
    public function validatePremiumPrice(float $premiumPrice, float $basePrice): bool
    {
        return $premiumPrice > $basePrice;
    }

    /**
     * Validate that a discounted price is lower than the base price
     */
    public function validateDiscountedPrice(float $discountedPrice, float $basePrice): bool
    {
        return $discountedPrice < $basePrice;
    }

    /**
     * Get validation error message for price override
     */
    public function getPriceValidationMessage(string $priceTag, float $overridePrice, float $basePrice): string
    {
        if ($priceTag === 'premium') {
            if ($overridePrice <= $basePrice) {
                return "Premium price must be higher than the base price (TK " . number_format($basePrice, 2) . ")";
            }
        } elseif ($priceTag === 'discounted') {
            if ($overridePrice >= $basePrice) {
                return "Discounted price must be lower than the base price (TK " . number_format($basePrice, 2) . ")";
            }
        }
        
        return '';
    }
}

