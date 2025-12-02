<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackagePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'price_type_id',   // regular / weekend
        'rider_type_id',   // single / double / 4 riders etc.
        'price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // A price belongs to a package
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    // Price type: regular / weekend / special
    public function priceType()
    {
        return $this->belongsTo(PriceType::class);
    }

    // Rider type: 1 rider / 2 riders / family etc.
    public function riderType()
    {
        return $this->belongsTo(RiderType::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    // Only active prices
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Filter by price type (regular/weekend)
    public function scopeForPriceType($query, $priceTypeId)
    {
        return $query->where('price_type_id', $priceTypeId);
    }

    // Filter by rider type
    public function scopeForRider($query, $riderTypeId)
    {
        return $query->where('rider_type_id', $riderTypeId);
    }
}
