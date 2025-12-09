<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'user_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'package_id',
        'package_price_id',
        'schedule_slot_id',
        'date',
        'report_time',
        'party_size',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'deposit_amount',
        'balance_amount',
        'booking_status',
        'payment_status',
        'notes',
        'acknowledgment_data',
        'signature_data',
    ];

    protected $casts = [
        'date' => 'date',
        'report_time' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'acknowledgment_data' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    /**
     * Get the package that owns the Reservation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function packagePrice(): BelongsTo
    {
        return $this->belongsTo(PackagePrice::class);
    }

    public function scheduleSlot(): BelongsTo
    {
        return $this->belongsTo(ScheduleSlot::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReservationItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function promoRedemptions(): HasMany
    {
        return $this->hasMany(PromoRedemption::class);
    }
}
