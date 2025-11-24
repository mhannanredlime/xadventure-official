<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class PackageVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'variant_name',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(VariantPrice::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function priceOverrides(): HasMany
    {
        return $this->hasMany(PriceOverride::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function reservationItems(): HasMany
    {
        return $this->hasMany(ReservationItem::class);
    }

    public function getPriceForDate($date): float
    {
        // Check for price override first
        $override = $this->priceOverrides()
            ->where('date', $date)
            ->first();

        if ($override) {
            return $override->price_amount;
        }

        // Use default pricing
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $priceType = ($dayOfWeek >= 6) ? 'weekend' : 'weekday';

        $defaultPrice = $this->prices()
            ->where('price_type', $priceType)
            ->first();

        return $defaultPrice ? $defaultPrice->amount : 0;
    }

    public function getPriceTagForDate($date): ?string
    {
        $override = $this->priceOverrides()
            ->where('date', $date)
            ->first();

        return $override ? $override->price_tag : null;
    }
}
