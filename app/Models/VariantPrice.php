<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_variant_id',
        'price_type',
        'amount',
        'original_amount',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function packageVariant(): BelongsTo
    {
        return $this->belongsTo(PackageVariant::class);
    }
}
