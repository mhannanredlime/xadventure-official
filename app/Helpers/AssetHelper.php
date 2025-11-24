<?php

namespace App\Helpers;

class AssetHelper
{
    /**
     * Get a versioned asset URL with timestamp to prevent caching
     *
     * @param string $path
     * @return string
     */
    public static function versioned($path)
    {
        $fullPath = public_path($path);
        
        // Check if file exists and get its modification time
        if (file_exists($fullPath)) {
            $version = filemtime($fullPath);
        } else {
            // If file doesn't exist, use current timestamp
            $version = time();
        }
        
        return asset($path) . '?v=' . $version;
    }

    /**
     * Get a versioned asset URL with custom version parameter name
     *
     * @param string $path
     * @param string $paramName
     * @return string
     */
    public static function versionedWithParam($path, $paramName = 'v')
    {
        $fullPath = public_path($path);
        
        if (file_exists($fullPath)) {
            $version = filemtime($fullPath);
        } else {
            $version = time();
        }
        
        return asset($path) . '?' . $paramName . '=' . $version;
    }

    /**
     * Get a versioned asset URL with cache busting hash
     *
     * @param string $path
     * @return string
     */
    public static function versionedWithHash($path)
    {
        $fullPath = public_path($path);
        
        if (file_exists($fullPath)) {
            $version = md5_file($fullPath);
        } else {
            $version = md5(time());
        }
        
        return asset($path) . '?v=' . substr($version, 0, 8);
    }
}

