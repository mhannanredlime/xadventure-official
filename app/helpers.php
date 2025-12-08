<?php

use App\Models\Cart;
use App\Models\Package;
use Carbon\Carbon;

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

        // Step 2: Prepare query
        $query = $package->packagePrices()->active();

        // Check if $day is a generic type (weekday/weekend) or specific day (mon/tue)
        $lowerDay = strtolower($day);
        $isGenericType = in_array($lowerDay, ['weekday', 'weekend']);

        if ($isGenericType) {
            // Query by PriceType slug
            $query->whereHas('priceType', function($q) use ($lowerDay) {
                $q->where('slug', $lowerDay);
            });
        } else {
            // It's a specific day, try exact match first
            // But we need to handle fallback if no exact day price exists
            // Since we can't easily do "try this OR that" with a single query builder chain without advanced logic,
            // let's fetch matching records and filter in PHP or do two queries.
            // Given the complexity and need for fallback, let's clone.
            
            $dayQuery = clone $query;
            $priceByDay = $dayQuery->where('day', $lowerDay)
                ->when($riderTypeId, function($q) use ($riderTypeId) {
                    $q->where('rider_type_id', $riderTypeId);
                })
                ->first();
                
            if ($priceByDay) {
                return $priceByDay->price;
            }
            
            // Fallback to PriceType
            // Map day to weekend/weekday. 
            // Note: This relies on app-specific definition of weekend.
            // Helper doesn't always have context, assuming standard Sat/Sun or Fri/Sat.
            // Let's assume standard Carbon isWeekend() equivalent if we had a date.
            // Since we only have day name string, we need a map.
            $weekends = ['sat', 'sun', 'friday']; // Including Friday as it's common in some contexts, but let's be safe.
            // Actually, best to check if the string implies weekend.
            // If strictly 'sat', 'sun' -> weekend.
            $isWeekendDay = in_array($lowerDay, ['sat', 'sun']); 
            $typeSlug = $isWeekendDay ? 'weekend' : 'weekday';
            
            $query->whereHas('priceType', function($q) use ($typeSlug) {
                $q->where('slug', $typeSlug);
            });
        }

        // Step 3: Filter by rider type if provided
        if ($riderTypeId) {
            $query->where('rider_type_id', $riderTypeId);
        }

        // Step 4: Return first matching price or minimum
        if ($riderTypeId) {
            return $query->value('price');
        } else {
            return $query->min('price');
        }
    }
}

if (! function_exists('calculateVAT')) {
    /**
     * Calculate VAT and total amount including VAT
     *
     * @param  float  $amount  Base amount
     * @param  float|null  $vatRate  VAT percentage (default from env)
     * @return array ['vat' => float, 'total' => float]
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

if (! function_exists('format_full_date')) {

    /**
     * Format date or datetime into: December 05, 2025
     *
     * @param  mixed  $value  Carbon|string|int|null
     * @param  string  $format  (default: F d, Y)
     * @return string|null
     */
    function format_full_date($value, $format = 'F d, Y')
    {
        if (! $value) {
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

if (! function_exists('getGuestCartItems')) {
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

if (! function_exists('generateTransactionId')) {
    /**
     * Generate a transaction ID based on reservation booking code
     */
    function generateTransactionId(string $bookingCode, string $prefix = 'XADB'): string
    {
        $tranId = $prefix.'-'.$bookingCode.'-'.uniqid();

        return strtolower($tranId);
    }
}

if (! function_exists('cleanOldCarts')) {
    /**
     * Delete carts older than SESSION_LIFETIME but keep current session carts
     *
     * @return int Number of deleted rows
     */
    function cleanOldCarts(?string $currentSessionId = null): int
    {
        $sessionLifetime = env('SESSION_LIFETIME', 120); // default 120 minutes

        return Cart::where(function ($query) use ($sessionLifetime, $currentSessionId) {
            $query->where('created_at', '<', now()->subMinutes($sessionLifetime))
                ->orWhere(function ($q) use ($currentSessionId) {
                    // Keep current session carts, delete others regardless of age
                    if ($currentSessionId) {
                        $q->where('session_id', '!=', $currentSessionId);
                    }
                });
        })->delete();
    }
}
