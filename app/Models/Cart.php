<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_type',
        'uuid',
        'user_id',
        'session_id',
        'package_id',
        'rider_type_id',
        'quantity',
        'selected_date',
        'date',
        'time_slot_id',
        'schedule_slot_id',
        'amount',
        'metadata',
        'expires_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'expires_at' => 'datetime',
    ];

    // Relation to Package
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    // Accessor for cart_amount (alias for amount)
    public function getCartAmountAttribute()
    {
        return $this->amount;
    }

    // Accessor for cart_uuid (alias for uuid)
    public function getCartUuidAttribute()
    {
        return $this->uuid;
    }

    protected static function booted()
    {
        static::creating(function ($cart) {
            $cart->uuid = (string) Str::uuid();
        });
    }
}
