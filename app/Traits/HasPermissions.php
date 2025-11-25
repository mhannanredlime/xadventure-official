<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasPermissions
{
    /**
     * Get all permissions for the user through their roles.
     */
    public function permissions()
    {
        return $this->roles()->with('permissions')->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');
    }

    /**
     * Get all permission slugs for the user.
     */
    public function getAllPermissions()
    {
        return $this->permissions()->pluck('slug');
    }

    /**
     * Check if the user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        // First check if user has any roles
        if ($this->roles->isEmpty()) {
            return false;
        }

        // Check if any of the user's roles have this permission
        return $this->roles->contains(function ($role) use ($permission) {
            return $role->permissions->contains('slug', $permission);
        });
    }

    /**
     * Check if the user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->roles->contains('slug', $role);
    }

    /**
     * Check if the user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles->whereIn('slug', $roles)->isNotEmpty();
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(Role $role): void
    {
        $this->roles()->syncWithoutDetaching([$role->id]);
    }

    /**
     * Assign multiple roles to the user.
     */
    public function assignRoles(array $roles): void
    {
        $this->roles()->syncWithoutDetaching($roles);
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(Role $role): void
    {
        $this->roles()->detach($role->id);
    }

    /**
     * Sync roles for the user.
     */
    public function syncRoles(array $roles): void
    {
        $this->roles()->sync($roles);
    }

    /**
     * Check if the user is a master admin.
     */
    public function isMasterAdmin(): bool
    {
        return $this->hasRole('master-admin');
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if the user is a manager.
     */
    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    /**
     * Get all permissions grouped by module.
     */
    public function getPermissionsByModule(): array
    {
        return $this->permissions()->groupBy('module')->toArray();
    }

    /**
     * Check if the user can manage users.
     */
    public function canManageUsers(): bool
    {
        return $this->hasAnyPermission(['users.view', 'users.create', 'users.edit', 'users.delete']);
    }

    /**
     * Check if the user can manage roles.
     */
    public function canManageRoles(): bool
    {
        return $this->hasAnyPermission(['roles.view', 'roles.create', 'roles.edit', 'roles.delete']);
    }
}
