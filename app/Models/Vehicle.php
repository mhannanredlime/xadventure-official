<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_type_id',
        'name',
        'details',
        'image_path', // Keep for backward compatibility
        'is_active',
        'op_start_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'op_start_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vehicle) {
            $vehicle->validateNameUniqueness();
        });

        static::updating(function ($vehicle) {
            $vehicle->validateNameUniqueness();
        });
    }

    /**
     * Validate vehicle name uniqueness
     */
    protected function validateNameUniqueness()
    {
        $existingVehicle = static::where('name', $this->name);
        if ($this->exists) {
            $existingVehicle->where('id', '!=', $this->id);
        }
        if ($existingVehicle->exists()) {
            throw ValidationException::withMessages([
                'name' => ['A vehicle with this name already exists.']
            ]);
        }
    }

    /**
     * Get validation rules for creating a vehicle
     */
    public static function getValidationRules($vehicleId = null)
    {
        $uniqueRule = $vehicleId 
            ? Rule::unique('vehicles')->ignore($vehicleId)
            : Rule::unique('vehicles');

        return [
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'name' => [
                'required',
                'string',
                'max:255',
                $uniqueRule,
            ],
            'details' => 'nullable|string',
            'op_start_date' => 'nullable|date_format:Y-m-d',
            'is_active' => 'boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg |max:2048'
        ];
    }

    /**
     * Get validation messages
     */
    public static function getValidationMessages()
    {
        return [
            'vehicle_type_id.required' => 'Vehicle type is required.',
            'vehicle_type_id.exists' => 'Selected vehicle type does not exist.',
            'name.required' => 'Vehicle name is required.',
            'name.string' => 'Vehicle name must be a string.',
            'name.max' => 'Vehicle name cannot exceed 255 characters.',
            'name.unique' => 'A vehicle with this name already exists.',
            'op_start_date.date_format' => 'Start date must be in YYYY-MM-DD format.',
            'images.*.image' => 'Uploaded files must be images.',
            'images.*.mimes' => 'Images must be in JPEG, PNG, JPG, or GIF format.',
            'images.*.max' => 'Images cannot exceed 2MB in size.',
        ];
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    /**
     * Get all images for this vehicle
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable')->ordered();
    }

    /**
     * Get the primary image for this vehicle
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
     * Get vehicle type images for this vehicle
     */
    public function getVehicleTypeImages()
    {
        return $this->vehicleType ? $this->vehicleType->images : collect();
    }

    /**
     * Get primary vehicle type image for this vehicle
     */
    public function getPrimaryVehicleTypeImage()
    {
        if (!$this->vehicleType) {
            return null;
        }
        
        return $this->vehicleType->primaryImage()->first();
    }

    /**
     * Get display image URL with fallback
     */
    public function getDisplayImageUrlAttribute()
    {
        // First try to get the primary image from the images relationship
        $primaryImage = $this->primaryImage()->first();
        if ($primaryImage) {
            return $primaryImage->url;
        }

        // Then try to get the first image from the images relationship
        $firstImage = $this->images()->first();
        if ($firstImage) {
            return $firstImage->url;
        }

        // Finally, fall back to the vehicle type's primary image
        if ($this->vehicleType) {
            $vehicleTypePrimaryImage = $this->vehicleType->primaryImage()->first();
            if ($vehicleTypePrimaryImage) {
                return $vehicleTypePrimaryImage->url;
            }
        }

        return null;
    }

    /**
     * Get all display images for this vehicle
     */
    public function getAllDisplayImages()
    {
        $images = collect();

        // Add vehicle's own images
        $vehicleImages = $this->images()->get();
        if ($vehicleImages->isNotEmpty()) {
            $images = $images->merge($vehicleImages);
        }

        // Add vehicle type images if no vehicle images
        if ($images->isEmpty() && $this->vehicleType) {
            $vehicleTypeImages = $this->vehicleType->images()->get();
            $images = $images->merge($vehicleTypeImages);
        }

        return $images;
    }
}
