<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'module',
    ];

    // roles() is provided by Spatie
    // public function roles(): BelongsToMany
    // {
    //    return $this->belongsToMany(Role::class, 'permission_role');
    // }

    /**
     * Scope to filter permissions by module.
     */
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Get all available modules.
     */
    public static function getModules(): array
    {
        return self::distinct()->pluck('module')->sort()->values()->toArray();
    }
}
