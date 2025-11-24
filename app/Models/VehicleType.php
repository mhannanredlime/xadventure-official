<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VehicleType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subtitle',
        'image_path', // Keep for backward compatibility
        'seating_capacity',
        'license_requirement',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'seating_capacity' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vehicleType) {
            $vehicleType->validateAtvUtvUniqueness();
        });

        static::updating(function ($vehicleType) {
            $vehicleType->validateAtvUtvUniqueness();
        });
    }

    /**
     * Validate ATV/UTV uniqueness
     */
    protected function validateAtvUtvUniqueness()
    {
        $name = strtoupper($this->name);
        
        if ($name === 'ATV') {
            $existingAtv = static::where('name', 'ATV');
            if ($this->exists) {
                $existingAtv->where('id', '!=', $this->id);
            }
            if ($existingAtv->exists()) {
                throw ValidationException::withMessages([
                    'name' => ['Only one ATV vehicle type can be created.']
                ]);
            }
        }
        
        if ($name === 'UTV') {
            $existingUtv = static::where('name', 'UTV');
            if ($this->exists) {
                $existingUtv->where('id', '!=', $this->id);
            }
            if ($existingUtv->exists()) {
                throw ValidationException::withMessages([
                    'name' => ['Only one UTV vehicle type can be created.']
                ]);
            }
        }
    }

    /**
     * Get validation rules for creating a vehicle type
     */
    public static function getValidationRules($vehicleTypeId = null)
    {
        $uniqueRule = $vehicleTypeId 
            ? Rule::unique('vehicle_types')->ignore($vehicleTypeId)
            : Rule::unique('vehicle_types');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                $uniqueRule,
            ],
            'subtitle' => 'nullable|string|max:255',
            'seating_capacity' => 'nullable|integer|min:1|max:10',
            'license_requirement' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get validation messages
     */
    public static function getValidationMessages()
    {
        return [
            'name.required' => 'Vehicle type name is required.',
            'name.string' => 'Vehicle type name must be a string.',
            'name.max' => 'Vehicle type name cannot exceed 255 characters.',
            'name.unique' => 'This vehicle type name already exists.',
            'seating_capacity.integer' => 'Seating capacity must be a whole number.',
            'seating_capacity.min' => 'Seating capacity must be at least 1.',
            'seating_capacity.max' => 'Seating capacity cannot exceed 10.',
        ];
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_vehicle_types');
    }

    public function promoCodes(): HasMany
    {
        return $this->hasMany(PromoCode::class);
    }

    /**
     * Get all images for this vehicle type
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable')->ordered();
    }

    /**
     * Get the primary image for this vehicle type
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
     * Get the display image URL with fallback
     */
    public function getDisplayImageUrlAttribute()
    {
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

        // Finally, fall back to the old image_path field
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        // Default fallback
        return asset('admin/images/vehicle-type.svg');
    }


}
