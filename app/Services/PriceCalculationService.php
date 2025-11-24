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

    public function getPriceForDate(PackageVariant $variant, $date): float
    {
        // Check for price override
        $override = PriceOverride::where([
            'package_variant_id' => $variant->id,
            'date' => $date
        ])->first();

        if ($override) {
            return $override->price_amount;
        }

        // Use default pricing
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $priceType = ($dayOfWeek >= 6) ? 'weekend' : 'weekday';

        $defaultPrice = $variant->prices()
            ->where('price_type', $priceType)
            ->first();

        return $defaultPrice ? $defaultPrice->amount : 0;
    }

    public function getPriceTagForDate(PackageVariant $variant, $date): ?string
    {
        $override = PriceOverride::where([
            'package_variant_id' => $variant->id,
            'date' => $date
        ])->first();

        return $override ? $override->price_tag : null;
    }

    public function getDefaultPriceForDate(PackageVariant $variant, $date): float
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $priceType = ($dayOfWeek >= 6) ? 'weekend' : 'weekday';

        $defaultPrice = $variant->prices()
            ->where('price_type', $priceType)
            ->first();

        return $defaultPrice ? $defaultPrice->amount : 0;
    }

    /**
     * Get comprehensive pricing and availability data for a package variant
     */
    public function getPricingAndAvailabilityForDate(PackageVariant $variant, $date, $scheduleSlotId = null, array $excludeCartItems = []): array
    {
        $finalPrice = $this->getPriceForDate($variant, $date);
        $defaultPrice = $this->getDefaultPriceForDate($variant, $date);
        $priceTag = $this->getPriceTagForDate($variant, $date);
        
        // Get vehicle availability
        $availability = $this->vehicleAvailabilityService->calculateAvailabilityForPackageVariant(
            $variant, 
            $date, 
            $scheduleSlotId,
            $excludeCartItems
        );

        return [
            'final_price' => $finalPrice,
            'default_price' => $defaultPrice,
            'price_tag' => $priceTag,
            'is_available' => $availability['is_available'],
            'available_capacity' => $availability['available_capacity'],
            'total_available' => $availability['total_available'],
            'total_vehicles' => $availability['total_vehicles'], // Add missing total_vehicles key
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

