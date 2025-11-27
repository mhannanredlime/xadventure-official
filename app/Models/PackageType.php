<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PackageType extends Model
{
    protected $fillable = ['name', 'slug', 'is_active'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name')) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function packages()
    {
        return $this->hasMany(Package::class, 'type', 'id');
    }

    /**
     * Scope a query to only include active package types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

}
