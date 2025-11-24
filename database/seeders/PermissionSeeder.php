<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User Management Module
            [
                'name' => 'View Users',
                'slug' => 'users.view',
                'description' => 'View user list and details',
                'module' => 'User Management',
            ],
            [
                'name' => 'Create Users',
                'slug' => 'users.create',
                'description' => 'Create new users',
                'module' => 'User Management',
            ],
            [
                'name' => 'Edit Users',
                'slug' => 'users.edit',
                'description' => 'Edit existing users',
                'module' => 'User Management',
            ],
            [
                'name' => 'Delete Users',
                'slug' => 'users.delete',
                'description' => 'Delete users',
                'module' => 'User Management',
            ],
            [
                'name' => 'View Roles',
                'slug' => 'roles.view',
                'description' => 'View role list and details',
                'module' => 'User Management',
            ],
            [
                'name' => 'Create Roles',
                'slug' => 'roles.create',
                'description' => 'Create new roles',
                'module' => 'User Management',
            ],
            [
                'name' => 'Edit Roles',
                'slug' => 'roles.edit',
                'description' => 'Edit existing roles',
                'module' => 'User Management',
            ],
            [
                'name' => 'Delete Roles',
                'slug' => 'roles.delete',
                'description' => 'Delete roles',
                'module' => 'User Management',
            ],

            // Package Management Module
            [
                'name' => 'View Packages',
                'slug' => 'packages.view',
                'description' => 'View package list and details',
                'module' => 'Package Management',
            ],
            [
                'name' => 'Manage Packages',
                'slug' => 'packages.manage',
                'description' => 'Create, edit, and delete packages',
                'module' => 'Package Management',
            ],

            // Pricing Management Module
            [
                'name' => 'View Pricing',
                'slug' => 'pricing.view',
                'description' => 'View pricing information',
                'module' => 'Pricing Management',
            ],
            [
                'name' => 'Manage Pricing',
                'slug' => 'pricing.manage',
                'description' => 'Create, edit, and delete pricing',
                'module' => 'Pricing Management',
            ],
            [
                'name' => 'View Promo Codes',
                'slug' => 'promo-codes.view',
                'description' => 'View promo code list and details',
                'module' => 'Pricing Management',
            ],
            [
                'name' => 'Manage Promo Codes',
                'slug' => 'promo-codes.manage',
                'description' => 'Create, edit, and delete promo codes',
                'module' => 'Pricing Management',
            ],

            // Reservation Management Module
            [
                'name' => 'View Reservations',
                'slug' => 'reservations.view',
                'description' => 'View reservation list and details',
                'module' => 'Reservation Management',
            ],
            [
                'name' => 'Edit Reservations',
                'slug' => 'reservations.edit',
                'description' => 'Edit existing reservations',
                'module' => 'Reservation Management',
            ],

            // Vehicle Management Module
            [
                'name' => 'View Vehicles',
                'slug' => 'vehicles.view',
                'description' => 'View vehicle list and details',
                'module' => 'Vehicle Management',
            ],
            [
                'name' => 'Manage Vehicles',
                'slug' => 'vehicles.manage',
                'description' => 'Create, edit, and delete vehicles',
                'module' => 'Vehicle Management',
            ],
            [
                'name' => 'View Vehicle Types',
                'slug' => 'vehicle-types.view',
                'description' => 'View vehicle type list and details',
                'module' => 'Vehicle Management',
            ],
            [
                'name' => 'Manage Vehicle Types',
                'slug' => 'vehicle-types.manage',
                'description' => 'Create, edit, and delete vehicle types',
                'module' => 'Vehicle Management',
            ],

            // Analytics Module
            [
                'name' => 'View Analytics',
                'slug' => 'analytics.view',
                'description' => 'View analytics and reports',
                'module' => 'Analytics',
            ],

            // Settings Module
            [
                'name' => 'View Settings',
                'slug' => 'settings.view',
                'description' => 'View system settings',
                'module' => 'Settings',
            ],
            [
                'name' => 'Manage Settings',
                'slug' => 'settings.manage',
                'description' => 'Create, edit, and delete settings',
                'module' => 'Settings',
            ],

            // Gallery Module
            [
                'name' => 'View Gallery',
                'slug' => 'gallery.view',
                'description' => 'View gallery images',
                'module' => 'Gallery',
            ],
            [
                'name' => 'Manage Gallery',
                'slug' => 'gallery.manage',
                'description' => 'Create, edit, and delete gallery images',
                'module' => 'Gallery',
            ],

            // Calendar Module
            [
                'name' => 'View Calendar',
                'slug' => 'calendar.view',
                'description' => 'View calendar and availability',
                'module' => 'Calendar',
            ],
            [
                'name' => 'Manage Calendar',
                'slug' => 'calendar.manage',
                'description' => 'Manage calendar and availability',
                'module' => 'Calendar',
            ],

            // Dashboard Module
            [
                'name' => 'View Dashboard',
                'slug' => 'dashboard.view',
                'description' => 'View admin dashboard',
                'module' => 'Dashboard',
            ],

            // Profile Module
            [
                'name' => 'Manage Profile',
                'slug' => 'profile.manage',
                'description' => 'Manage own profile and settings',
                'module' => 'Profile',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
