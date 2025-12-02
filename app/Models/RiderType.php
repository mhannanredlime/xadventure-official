<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiderType extends Model
{
    use HasFactory;

    protected $table = 'rider_types';

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
     * Relationship: A rider type has many package prices.
     */
    public function packagePrices()
    {
        return $this->hasMany(PackagePrice::class);
    }

    /**
     * Scope: Only active rider types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Accessor: formatted label for UI dropdowns
     * Example: "Double Rider (2)"
     */
    public function getLabelAttribute()
    {
        return "{$this->name} ({$this->rider_count})";
    }
}
