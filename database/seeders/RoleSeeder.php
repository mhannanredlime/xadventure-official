<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update Master Admin role
        $masterAdmin = Role::updateOrCreate(
            ['slug' => 'master-admin'],
            [
                'name' => 'Master Admin',
                'description' => 'Full control of everything including user and role management',
                'is_system' => true,
                'guard_name' => 'web',
            ]
        );

        // Create or update Admin role
        $admin = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Full control of pricing, reservations, analytics, and vehicle/package management. No user/role management.',
                'is_system' => true,
                'guard_name' => 'web',
            ]
        );

        // Create or update Manager role
        $manager = Role::updateOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'description' => 'Reservation view and edit only. No pricing, packages, analytics, or user management.',
                'is_system' => true,
                'guard_name' => 'web',
            ]
        );

        // Assign all permissions to Master Admin
        $allPermissions = Permission::all();
        $masterAdmin->permissions()->sync($allPermissions->pluck('id'));

        // Assign permissions to Admin (everything except user/role management)
        $adminPermissions = Permission::whereNotIn('module', ['User Management'])->get();
        $admin->permissions()->sync($adminPermissions->pluck('id'));

        // Assign permissions to Manager (only reservations)
        $managerPermissions = Permission::whereIn('slug', [
            'reservations.view',
            'reservations.edit'
        ])->get();
        $manager->permissions()->sync($managerPermissions->pluck('id'));
    }
}
