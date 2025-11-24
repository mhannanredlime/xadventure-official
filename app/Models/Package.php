<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subtitle',
        'type',
        'min_participants',
        'max_participants',
        'display_starting_price',
        'image_path',
        'notes',
        'details',
        'selected_weekday',
        'selected_weekend',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(PackageVariant::class);
    }

    public function vehicleTypes(): BelongsToMany
    {
        return $this->belongsToMany(VehicleType::class, 'package_vehicle_types');
    }

    public function promoCodes(): HasMany
    {
        return $this->hasMany(PromoCode::class);
    }

    /**
     * Get all images for this package
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable')->ordered();
    }

    /**
     * Get the primary image for this package
     */
    public function primaryImage()
    {
        return $this->morphOne(Image::class, 'imageable')->where('is_primary', true);
    }

    /**
     * Get the first image (for backward compatibility)
     */
    public function getFirstImageAttribute()
    {
        return $this->images()->first();
    }

    /**
     * Get the primary image URL (for backward compatibility)
     */
    public function getPrimaryImageUrlAttribute()
    {
        $primaryImage = $this->primaryImage()->first();
        return $primaryImage ? $primaryImage->url : null;
    }

    /**
     * Get all reservations for this package through variants
     */
    public function reservations()
    {
        return $this->hasManyThrough(ReservationItem::class, PackageVariant::class);
    }

    /**
     * Get the display starting price for this package
     * If display_starting_price is set, use it; otherwise calculate from variants
     */
    public function getDisplayStartingPriceAttribute()
    {
        // Check if the attribute exists and is not null
        if (isset($this->attributes['display_starting_price']) && $this->attributes['display_starting_price'] !== null) {
            return $this->attributes['display_starting_price'];
        }
        
        // Fallback to calculating from variants
        $minPrice = $this->variants()
            ->where('is_active', true)
            ->get()
            ->flatMap(function ($variant) {
                return $variant->prices()->pluck('amount');
            })
            ->min();
            
        return $minPrice ?: 0;
    }

    /**
     * Get vehicle type images for this package
     */
    public function getVehicleTypeImages()
    {
        $images = collect();
        
        foreach ($this->vehicleTypes as $vehicleType) {
            $vehicleTypeImages = $vehicleType->images;
            if ($vehicleTypeImages->isNotEmpty()) {
                $images = $images->merge($vehicleTypeImages);
            }
        }
        
        return $images->unique('id');
    }

    /**
     * Get primary vehicle type image for this package
     */
    public function getPrimaryVehicleTypeImage()
    {
        // First try to find a vehicle type that matches the package type
        $matchingVehicleType = $this->vehicleTypes->first(function($vehicleType) {
            return strtolower($vehicleType->name) === $this->type;
        });
        
        if ($matchingVehicleType) {
            $primaryImage = $matchingVehicleType->primaryImage()->first();
            if ($primaryImage) {
                return $primaryImage;
            }
            
            // Fallback to first image from matching vehicle type
            $firstImage = $matchingVehicleType->images()->first();
            if ($firstImage) {
                return $firstImage;
            }
        }
        
        // If no matching vehicle type or no images, try any vehicle type
        foreach ($this->vehicleTypes as $vehicleType) {
            $primaryImage = $vehicleType->primaryImage()->first();
            if ($primaryImage) {
                return $primaryImage;
            }
        }
        
        // Fallback to first image from any vehicle type
        foreach ($this->vehicleTypes as $vehicleType) {
            $firstImage = $vehicleType->images()->first();
            if ($firstImage) {
                return $firstImage;
            }
        }
        
        return null;
    }

    /**
     * Get display image URL with vehicle type fallback (only for ATV/UTV packages)
     */
    public function getDisplayImageUrlAttribute()
    {
        // For ATV/UTV packages, prioritize vehicle type images over package images
        if ($this->type === 'atv' || $this->type === 'utv') {
            // First try to find a vehicle type that matches the package type
            $matchingVehicleType = $this->vehicleTypes->first(function($vehicleType) {
                return strtolower($vehicleType->name) === $this->type;
            });
            
            if ($matchingVehicleType) {
                $vehicleTypeImage = $matchingVehicleType->primaryImage()->first();
                if ($vehicleTypeImage) {
                    return $vehicleTypeImage->url;
                }
                
                // Fallback to first image from matching vehicle type
                $firstImage = $matchingVehicleType->images()->first();
                if ($firstImage) {
                    return $firstImage->url;
                }
            }
            
            // If no matching vehicle type found, fall back to any vehicle type image
            $vehicleTypeImage = $this->getPrimaryVehicleTypeImage();
            if ($vehicleTypeImage) {
                return $vehicleTypeImage->url;
            }
        }

        // For regular packages or if no vehicle type images found, use package images
        // First try to get the primary image from the images relationship
        $primaryImage = $this->primaryImage()->first();
        if ($primaryImage) {
            return $primaryImage->url;
        }

        // Then try to get any image from the images relationship
        $firstImage = $this->images()->first();
        if ($firstImage) {
            return $firstImage->url;
        }

        // Default fallback
        return asset('admin/images/package.svg');
    }
}
