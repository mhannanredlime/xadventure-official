<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'module',
    ];

    /**
     * Get the roles that belong to the permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }

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
