<?php

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


if (!function_exists('weekdays')) {
    /**
     * Get the list of weekdays (Sunday–Thursday)
     *
     * @return array
     */
    function weekdays(): array
    {
        return ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];
    }
}

if (!function_exists('weekends')) {
    /**
     * Get the list of weekends (Friday–Saturday)
     *
     * @return array
     */
    function weekends(): array
    {
        return ['friday', 'saturday'];
    }
}
