<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'applies_to',
        'package_id',
        'vehicle_type_id',
        'discount_type',
        'discount_value',
        'max_discount',
        'min_spend',
        'usage_limit_total',
        'usage_limit_per_user',
        'starts_at',
        'ends_at',
        'status',
        'remarks',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_spend' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function getStatusAttribute($value)
    {
        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'expired';
        }

        return $value;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>', now());
            });
    }
    
    public function scopeValid($query)
    {
         return $query->active()
            ->where(function ($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            });
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(PromoRedemption::class);
    }
}