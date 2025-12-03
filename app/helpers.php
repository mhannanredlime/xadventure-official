<?php

use App\Models\Package;

/**
 * Global Helper Functions
 * 
 * This file contains global helper functions for the application.
 */

if (!function_exists('versioned_asset')) {
    /**
     * Get a versioned asset URL with timestamp to prevent caching
     *
     * @param string $path
     * @return string
     */
    function versioned_asset($path) {
        return \App\Helpers\AssetHelper::versioned($path);
    }
}

if (!function_exists('asset_versioned')) {
    /**
     * Alias for versioned_asset()
     *
     * @param string $path
     * @return string
     */
    function asset_versioned($path) {
        return \App\Helpers\AssetHelper::versioned($path);
    }
}

if (!function_exists('asset_hash')) {
    /**
     * Get a versioned asset URL with hash to prevent caching
     *
     * @param string $path
     * @return string
     */
    function asset_hash($path) {
        return \App\Helpers\AssetHelper::versionedWithHash($path);
    }
}

if (!function_exists('weekDays')) {
    function weekDays()
    {
        return ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
    }
}
if (!function_exists('atvPackageAvgPrice')) {
    function atvPackageAvgPrice(Package $package)
    {
        if (!$package->packagePrices || $package->packagePrices->isEmpty()) {
            return 0;
        }

        // Calculate average price
        return round($package->packagePrices->avg('price'));
    }
}