<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PriceType extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'rider_count',
        'is_active',
    ];

    protected $casts = [
        'rider_count' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: A PriceType has many Package Prices
     */
    public function packagePrices()
    {
        return $this->hasMany(PackagePrice::class);
    }

    /**
     * Scope: Only active price types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Helper: Label for UI
     * Example: "Single Rider (1)" or "Double Rider (2)"
     */
    public function getLabelAttribute()
    {
        return "{$this->name} ({$this->rider_count})";
    }
}
