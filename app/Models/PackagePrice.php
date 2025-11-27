<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackagePrice extends Model
{
    protected $fillable = [
        'package_id',
        'type',
        'day',
        'start_date',
        'end_date',
        'price',
        'is_active',
    ];

    protected $casts = [
        'days' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
