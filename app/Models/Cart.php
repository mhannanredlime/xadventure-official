<?php

namespace App\Models;

use App\Models\Package;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
