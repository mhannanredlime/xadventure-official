<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test admin account
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'phone' => '+1234567890',
                'address' => 'Admin Address',
                'email_verified_at' => now(),
            ]
        );

        echo "Admin user created: admin@admin.com / password\n";
    }
}

