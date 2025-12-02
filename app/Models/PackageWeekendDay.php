<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackageWeekendDay extends Model
{
    use HasFactory;


    protected $fillable = [
        'package_id',
        'day',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: A Weekend Day belongs to a Package
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Scope: only active weekend days
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if a given day is weekend for this package
     */
    public static function isWeekend(Package $package, string $day): bool
    {
        return self::where('package_id', $package->id)
            ->where('day', $day)
            ->where('is_active', true)
            ->exists();
    }
}
