<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * Get the users that belong to the role.
     */
    // Spatie handles users() and permissions()
    // but check if implementation matches. 
    // Spatie uses 'model_has_roles' and 'role_has_permissions'. 
    // Existing code uses 'role_user' and 'permission_role'.
    // We should comment out generic BelongsToMany if we want to use Spatie's tables.
    // However, if we keep 'role_user', Spatie won't use it.
    // I will comment them out to force usage of Spatie's structure.

    /**
     * Check if the role has a specific permission.
     */
    // Spatie provides givePermissionTo, revokePermissionTo, syncPermissions
    // We remove custom implementations to avoid signature conflicts.

    /**
     * Check if the role has a specific permission (legacy wrapper).
     */
    public function hasPermission(string $permission): bool
    {
        return $this->hasPermissionTo($permission);
    }

    /**
     * Check if the role is a system role (cannot be deleted).
     */
    public function isSystemRole(): bool
    {
        return $this->is_system;
    }
}
