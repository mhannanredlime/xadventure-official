<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlotPresetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'slot_preset_id',
        'schedule_slot_id',
        'sort_order',
    ];

    public function preset(): BelongsTo
    {
        return $this->belongsTo(SlotPreset::class, 'slot_preset_id');
    }

    public function scheduleSlot(): BelongsTo
    {
        return $this->belongsTo(ScheduleSlot::class);
    }
}


