<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlotPresetOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'date',
        'slot_preset_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function preset(): BelongsTo
    {
        return $this->belongsTo(SlotPreset::class, 'slot_preset_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}


