<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'report_time',
        'start_time',
        'end_time',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'report_time' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
