# Asset Versioning System

This project includes a custom asset versioning system to prevent caching issues when assets are updated.

## How It Works

The versioning system automatically adds a timestamp or hash parameter to asset URLs, ensuring that browsers always fetch the latest version of your assets when they change.

## Available Functions

### 1. `versioned_asset($path)`
The main function for versioned assets. Adds a timestamp parameter to the URL.

```php
// Usage in Blade templates
<link rel="stylesheet" href="{{ versioned_asset('frontEnd/css/style.css') }}">
<script src="{{ versioned_asset('frontEnd/js/app.js') }}"></script>
<img src="{{ versioned_asset('frontEnd/images/logo.png') }}" alt="Logo">
```

### 2. `asset_versioned($path)`
Alias for `versioned_asset()` - same functionality.

### 3. `asset_hash($path)`
Uses MD5 hash of the file content instead of timestamp for more precise versioning.

```php
<link rel="stylesheet" href="{{ asset_hash('frontEnd/css/style.css') }}">
```

### 4. `@versionedAsset($path)`
Blade directive for versioned assets.

```php
@versionedAsset('frontEnd/css/style.css')
```

## Examples

### Before (without versioning)
```php
<link rel="stylesheet" href="{{ asset('frontEnd/css/style.css') }}">
<!-- Output: /frontEnd/css/style.css -->
```

### After (with versioning)
```php
<link rel="stylesheet" href="{{ versioned_asset('frontEnd/css/style.css') }}">
<!-- Output: /frontEnd/css/style.css?v=1703123456 -->
```

## Testing the System

Visit `/test-asset-versioning` in your browser to see the versioning system in action. This page demonstrates:

- Regular vs versioned asset URLs
- Different versioning methods (timestamp vs hash)
- Blade directive usage
- Handling of non-existent files
- Various asset types (CSS, JS, images)

## Migration Script

A migration script is available to automatically convert existing `asset()` calls to `versioned_asset()` calls:

```bash
php scripts/migrate-assets.php
```

This script will:
- Scan all Blade template files
- Convert `asset()` calls for CSS, JS, and image files to `versioned_asset()`
- Skip storage assets and already versioned assets
- Provide a summary of changes made

## Implementation Details

- **Timestamp-based**: Uses `filemtime()` to get the file's modification time
- **Hash-based**: Uses `md5_file()` to create a content-based hash
- **Fallback**: If file doesn't exist, uses current timestamp
- **Automatic**: No manual version management required

## When to Use

- **CSS files**: Always use versioned assets to ensure style updates are applied
- **JavaScript files**: Use versioned assets to ensure script updates are loaded
- **Images**: Use versioned assets for images that might be updated
- **Static assets**: Any file that could be modified and needs cache busting

## Performance Considerations

- The system checks file modification time on each request
- For high-traffic sites, consider caching the version numbers
- Hash-based versioning is more precise but slightly slower

## Migration Guide

To migrate existing assets to use versioning:

1. **Automatic Migration**: Run the migration script
   ```bash
   php scripts/migrate-assets.php
   ```

2. **Manual Migration**: Replace `asset()` with `versioned_asset()` for CSS and JS files
3. Replace `asset()` with `versioned_asset()` for images that might be updated
4. Keep `asset()` for static content that never changes

### Example Migration

```php
// Before
<link rel="stylesheet" href="{{ asset('frontEnd/css/style.css') }}">
<script src="{{ asset('frontEnd/js/app.js') }}"></script>

// After
<link rel="stylesheet" href="{{ versioned_asset('frontEnd/css/style.css') }}">
<script src="{{ versioned_asset('frontEnd/js/app.js') }}"></script>
```

## What to Keep as `asset()`

Keep using `asset()` for:
- Storage assets: `asset('storage/...')`
- Third-party CDN assets
- Assets that should not be versioned
- Dynamic assets that change frequently

## Troubleshooting

### Composer Autoload
If you get class not found errors, run:
```bash
composer dump-autoload
```

### Cache Issues
If you still see cached assets, clear Laravel's cache:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### File Permissions
Ensure the web server has read access to the public directory for file modification time checks.

### Testing
Visit `/test-asset-versioning` to verify the system is working correctly.

## Files Modified

- `app/Providers/AppServiceProvider.php` - Registered helper functions and Blade directive
- `app/Helpers/AssetHelper.php` - Core versioning logic
- `composer.json` - Added autoload configuration
- `resources/views/layouts/frontend.blade.php` - Updated to use versioned assets
- `resources/views/layouts/admin.blade.php` - Updated to use versioned assets
- `routes/web.php` - Added test route
- `resources/views/test-asset-versioning.blade.php` - Test page
- `scripts/migrate-assets.php` - Migration script

