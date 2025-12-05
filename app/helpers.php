<?php

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Package;

/**
 * Global Helper Functions
 *
 * This file contains global helper functions for the application.
 */
if (! function_exists('versioned_asset')) {
    /**
     * Get a versioned asset URL with timestamp to prevent caching
     *
     * @param  string  $path
     * @return string
     */
    function versioned_asset($path)
    {
        return \App\Helpers\AssetHelper::versioned($path);
    }
}

if (! function_exists('asset_versioned')) {
    /**
     * Alias for versioned_asset()
     *
     * @param  string  $path
     * @return string
     */
    function asset_versioned($path)
    {
        return \App\Helpers\AssetHelper::versioned($path);
    }
}

if (! function_exists('asset_hash')) {
    /**
     * Get a versioned asset URL with hash to prevent caching
     *
     * @param  string  $path
     * @return string
     */
    function asset_hash($path)
    {
        return \App\Helpers\AssetHelper::versionedWithHash($path);
    }
}

if (! function_exists('weekDays')) {
    function weekDays()
    {
        return ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
    }
}
if (! function_exists('atvPackageAvgPrice')) {
    function atvPackageAvgPrice(Package $package)
    {
        if (! $package->packagePrices || $package->packagePrices->isEmpty()) {
            return 0;
        }

        // Calculate average price
        return round($package->packagePrices->avg('price'));
    }
}

if (! function_exists('get_package_price')) {
    /**
     * Get package price based on display_starting_price, day, and rider type
     *
     * @param  int|\App\Models\Package  $package
     */
    function get_package_price($package, string $day, ?int $riderTypeId = null): ?float
    {
        // Fetch model if ID is provided
        if (is_int($package)) {
            $package = Package::find($package);
        }

        if (! $package) {
            return null;
        }

        // Step 1: If display_starting_price is set, return it
        if (! is_null($package->display_starting_price)) {
            return $package->display_starting_price;
        }

        // Step 2: Get active prices for the given day
        $query = $package->packagePrices()->active()->where('day', $day);

        // Step 3: Filter by rider type if provided
        if ($riderTypeId) {
            $query->where('rider_type_id', $riderTypeId);
        }

        // Step 4: Return first matching price or minimum if no riderTypeId
        if ($riderTypeId) {
            return $query->value('price'); // exact rider type
        } else {
            return $query->min('price'); // minimum price for the day
        }
    }
}

if (! function_exists('calculateVAT')) {
    /**
     * Calculate VAT and total amount including VAT
     *
     * @param  float  $amount   Base amount
     * @param  float|null  $vatRate  VAT percentage (default from env)
     * @return array  ['vat' => float, 'total' => float]
     */
    function calculateVAT(float $amount, ?float $vatRate = null): array
    {
        // Ensure VAT rate is set
        $vatRate = $vatRate ?? (float) env('VAT_RATE', 15);

        // Prevent negative amounts
        $amount = max(0, $amount);

        $vatAmount = round(($amount * $vatRate) / 100, 2);
        $totalWithVAT = round($amount + $vatAmount, 2);

        return [
            'vat' => $vatAmount,
            'total' => $totalWithVAT,
        ];
    }   
}


if (!function_exists('format_full_date')) {

    /**
     * Format date or datetime into: December 05, 2025
     *
     * @param  mixed  $value  Carbon|string|int|null
     * @param  string $format (default: F d, Y)
     * @return string|null
     */
    function format_full_date($value, $format = 'F d, Y')
    {
        if (!$value) {
            return null;
        }

        // If already a Carbon instance
        if ($value instanceof Carbon) {
            return $value->format($format);
        }

        // If timestamp
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value)->format($format);
        }

        // If string date or datetime
        return Carbon::parse($value)->format($format);
    }
}

if (!function_exists('getGuestCartItems')) {
    /**
     * Current session এর active guest cart items fetch করবে
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getGuestCartItems()
    {
        return Cart::with('package')
            ->where('session_id', session()->getId())
            ->where('created_at', '>=', now()->subMinutes(env('SESSION_LIFETIME', 120)))
            ->get();
    }
}