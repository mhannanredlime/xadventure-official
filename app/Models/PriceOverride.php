<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_variant_id',
        'date',
        'price_tag',
        'price_amount',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'price_amount' => 'decimal:2'
    ];

    public function packageVariant(): BelongsTo
    {
        return $this->belongsTo(PackageVariant::class);
    }

    public function getColorAttribute(): string
    {
        return [
            'premium' => 'purple',
            'discounted' => 'green'
        ][$this->price_tag] ?? 'gray';
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'TK ' . number_format($this->price_amount);
    }
}
