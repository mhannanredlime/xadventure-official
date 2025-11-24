<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
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
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    /**
     * Get the permissions that belong to the role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    /**
     * Check if the role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('slug', $permission)->exists();
    }

    /**
     * Give permission to the role.
     */
    public function givePermissionTo(Permission $permission): void
    {
        $this->permissions()->syncWithoutDetaching([$permission->id]);
    }

    /**
     * Revoke permission from the role.
     */
    public function revokePermissionTo(Permission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }

    /**
     * Sync permissions for the role.
     */
    public function syncPermissions(array $permissions): void
    {
        $this->permissions()->sync($permissions);
    }

    /**
     * Check if the role is a system role (cannot be deleted).
     */
    public function isSystemRole(): bool
    {
        return $this->is_system;
    }
}
