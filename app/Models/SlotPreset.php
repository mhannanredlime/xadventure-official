<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlotPreset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SlotPresetItem::class);
    }

    public function scheduleSlots(): BelongsToMany
    {
        return $this->belongsToMany(ScheduleSlot::class, 'slot_preset_items')
            ->withPivot('sort_order')
            ->orderBy('slot_preset_items.sort_order')
            ->orderBy('schedule_slots.start_time');
    }
}


