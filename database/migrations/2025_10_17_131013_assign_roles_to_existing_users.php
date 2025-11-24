<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, seed permissions
        $permissionSeeder = new \Database\Seeders\PermissionSeeder();
        $permissionSeeder->run();

        // Then, seed roles
        $roleSeeder = new \Database\Seeders\RoleSeeder();
        $roleSeeder->run();

        // Get the Master Admin role
        $masterAdminRole = \App\Models\Role::where('slug', 'master-admin')->first();
        
        if (!$masterAdminRole) {
            throw new \Exception('Master Admin role not found. Please run the seeders first.');
        }

        // Assign Master Admin role to all users with is_admin = 1
        $adminUsers = \App\Models\User::where('is_admin', 1)->get();
        
        foreach ($adminUsers as $user) {
            $user->roles()->syncWithoutDetaching([$masterAdminRole->id]);
        }

        // Log the migration
        \Log::info('Role assignment migration completed', [
            'admin_users_count' => $adminUsers->count(),
            'master_admin_role_id' => $masterAdminRole->id
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove all role assignments
        \DB::table('role_user')->truncate();
        
        // Optionally remove roles and permissions (be careful with this)
        // \DB::table('permission_role')->truncate();
        // \DB::table('roles')->truncate();
        // \DB::table('permissions')->truncate();
    }
};
