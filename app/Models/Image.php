<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_path',
        'original_name',
        'mime_type',
        'file_size',
        'sort_order',
        'is_primary',
        'alt_text',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $appends = ['url', 'thumbnail_url'];

    /**
     * Get the parent imageable model (Package, Vehicle, VehicleType, etc.).
     */
    public function imageable()
    {
        return $this->morphTo();
    }

    /**
     * Get the full URL for the image
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }

    /**
     * Get the thumbnail URL for the image
     */
    public function getThumbnailUrlAttribute()
    {
        $pathInfo = pathinfo($this->image_path);
        $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['basename'];

        // Check if thumbnail exists, otherwise return original
        if (Storage::disk('public_storage')->exists($thumbnailPath)) {
            return asset('storage/' . $thumbnailPath);
        }

        return $this->url;
    }

    /**
     * Delete the image file when the model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            // Delete the main image file
            if (Storage::disk('public_storage')->exists($image->image_path)) {
                Storage::disk('public_storage')->delete($image->image_path);
            }

            // Delete thumbnail if exists
            $pathInfo = pathinfo($image->image_path);
            $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['basename'];
            if (Storage::disk('public_storage')->exists($thumbnailPath)) {
                Storage::disk('public_storage')->delete($thumbnailPath);
            }
        });
    }

    /**
     * Scope to get primary images
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }
}
