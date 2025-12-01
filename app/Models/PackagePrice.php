<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackagePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'day',
        'rider_count',
        'price',
        'day_type',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'rider_count' => 'integer',
    ];

    /**
     * Relationship with Package
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Scope: Active Prices
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: For a specific day
     */
    public function scopeForDay($query, $day)
    {
        return $query->where('day', $day);
    }

    /**
     * Scope: For a specific rider count
     */
    public function scopeForRider($query, $riderCount)
    {
        return $query->where('rider_count', $riderCount);
    }

    /**
     * Check if the price is valid today
     */
    public function isValidToday(): bool
    {
        $today = now()->toDateString();
        if ($this->start_date && $today < $this->start_date) return false;
        if ($this->end_date && $today > $this->end_date) return false;
        return true;
    }
}
