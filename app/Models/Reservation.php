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
        'customer_id',
        'package_variant_id',
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
        return $this->belongsTo(Customer::class);
    }

    public function packageVariant(): BelongsTo
    {
        return $this->belongsTo(PackageVariant::class);
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
