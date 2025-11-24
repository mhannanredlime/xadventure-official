<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Gallery extends Model
{
    use HasFactory;

    protected $table = 'gallery';

    protected $fillable = [
        'image_path',
        'thumbnail_path',
        'original_name',
        'mime_type',
        'file_size',
        'alt_text',
        'tags',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'tags' => 'array',
    ];

    protected $appends = ['url', 'thumbnail_url'];

    /**
     * Get the user who uploaded the image
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
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
        if ($this->thumbnail_path && Storage::disk('public_storage')->exists($this->thumbnail_path)) {
            return asset('storage/' . $this->thumbnail_path);
        }

        return $this->url;
    }

    /**
     * Delete the image files when the model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($gallery) {
            // Delete the main image file
            if (Storage::disk('public_storage')->exists($gallery->image_path)) {
                Storage::disk('public_storage')->delete($gallery->image_path);
            }

            // Delete thumbnail if exists
            if ($gallery->thumbnail_path && Storage::disk('public_storage')->exists($gallery->thumbnail_path)) {
                Storage::disk('public_storage')->delete($gallery->thumbnail_path);
            }
        });
    }

    /**
     * Scope to search images by filename or alt text
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('original_name', 'like', "%{$search}%")
              ->orWhere('alt_text', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to filter by tags
     */
    public function scopeWithTags($query, $tags)
    {
        if (empty($tags)) {
            return $query;
        }

        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('tags', $tag);
            }
        });
    }

    /**
     * Scope to order by upload date
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
