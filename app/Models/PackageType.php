<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PackageType extends Model
{
    protected $fillable = ['name', 'slug', 'is_active', 'parent_id'];

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

    /**
     * Relationship: Parent type
     */
    public function parent()
    {
        return $this->belongsTo(PackageType::class, 'parent_id');
    }

    /**
     * Relationship: Child types
     */
    public function children()
    {
        return $this->hasMany(PackageType::class, 'parent_id');
    }

    /**
     * Packages for this type
     */
    public function packages()
    {
        return $this->hasMany(Package::class, 'package_type_id', 'id');
    }

    /**
     * Scope a query to only include active package types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
