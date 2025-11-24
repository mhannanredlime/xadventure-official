<?php

/**
 * Asset Migration Script
 * 
 * This script helps migrate existing asset() calls to versioned_asset() calls
 * in your Blade template files.
 * 
 * Usage: php scripts/migrate-assets.php
 */

$bladeFiles = glob('resources/views/**/*.blade.php');
$migratedCount = 0;
$totalFiles = count($bladeFiles);

echo "Starting asset migration...\n";
echo "Found {$totalFiles} Blade template files\n\n";

foreach ($bladeFiles as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Pattern to match asset() calls for CSS and JS files
    $patterns = [
        // CSS files
        '/asset\(\s*[\'"]([^\'"]*\.css[^\'"]*)[\'"]\s*\)/i',
        // JS files  
        '/asset\(\s*[\'"]([^\'"]*\.js[^\'"]*)[\'"]\s*\)/i',
        // Image files (common formats)
        '/asset\(\s*[\'"]([^\'"]*\.(jpg|jpeg|png|gif|webp|svg)[^\'"]*)[\'"]\s*\)/i',
    ];
    
    $modified = false;
    
    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            // Process matches in reverse order to maintain offsets
            for ($i = count($matches[0]) - 1; $i >= 0; $i--) {
                $fullMatch = $matches[0][$i][0];
                $assetPath = $matches[1][$i][0];
                $offset = $matches[0][$i][1];
                
                // Skip if it's already a versioned asset or storage asset
                if (strpos($fullMatch, 'versioned_asset') !== false || 
                    strpos($assetPath, 'storage/') !== false) {
                    continue;
                }
                
                // Replace asset() with versioned_asset()
                $replacement = str_replace('asset(', 'versioned_asset(', $fullMatch);
                $content = substr_replace($content, $replacement, $offset, strlen($fullMatch));
                $modified = true;
            }
        }
    }
    
    if ($modified) {
        file_put_contents($file, $content);
        $migratedCount++;
        echo "✅ Migrated: {$file}\n";
    }
}

echo "\nMigration completed!\n";
echo "Migrated {$migratedCount} out of {$totalFiles} files\n";

if ($migratedCount > 0) {
    echo "\nNext steps:\n";
    echo "1. Review the changes in the migrated files\n";
    echo "2. Test your application to ensure everything works correctly\n";
    echo "3. Clear Laravel cache: php artisan cache:clear\n";
    echo "4. Clear view cache: php artisan view:clear\n";
} else {
    echo "\nNo files were migrated. This could mean:\n";
    echo "- All assets are already using versioned_asset()\n";
    echo "- No CSS/JS/image assets were found in the templates\n";
    echo "- Assets are using storage/ paths (which should remain as asset())\n";
}

echo "\nRemember to keep using asset() for:\n";
echo "- Storage assets (asset('storage/...'))\n";
echo "- Third-party CDN assets\n";
echo "- Assets that should not be versioned\n";

