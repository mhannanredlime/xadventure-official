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
        'cart_uuid',
        'user_id',
        'session_id',
        'package_id',
        'quantity',
        'selected_date',
        'time_slot_id',
        'cart_amount',
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

    protected static function booted()
    {
        static::creating(function ($cart) {
            $cart->cart_uuid = (string) Str::uuid();
        });
    }
}
