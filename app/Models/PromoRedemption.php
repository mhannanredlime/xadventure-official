<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'promo_code_id',
        'reservation_id',
        'customer_id',
        'amount_discounted',
        'redeemed_at',
    ];

    protected $casts = [
        'amount_discounted' => 'decimal:2',
        'redeemed_at' => 'datetime',
    ];

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
