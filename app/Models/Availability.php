<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'package_variant_id',
        'schedule_slot_id',
        'capacity_total',
        'capacity_reserved',
        'is_day_off'
    ];

    protected $casts = [
        'date' => 'date',
        'is_day_off' => 'boolean'
    ];

    public function packageVariant(): BelongsTo
    {
        return $this->belongsTo(PackageVariant::class);
    }

    public function scheduleSlot(): BelongsTo
    {
        return $this->belongsTo(ScheduleSlot::class);
    }


    public function getAvailableCapacityAttribute(): int
    {
        return $this->capacity_total - $this->capacity_reserved;
    }

    public function getCalendarStatusAttribute(): string
    {
        if ($this->is_day_off) {
            return 'day-off';
        }
        
        if ($this->capacity_reserved >= $this->capacity_total) {
            return 'booked';
        }
        
        return 'available';
    }
}
