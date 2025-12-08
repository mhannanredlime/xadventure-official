<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting database seeding...');

        try {
            // Step 1: Create admin user first
            $this->createAdminUser();

            // Step 2: Seed core data in proper order
            $this->call([
                PackageTypeSeeder::class,
                PriceTypeSeeder::class,
                RiderTypeSeeder::class,
                VehicleTypeSeeder::class,
                VehicleTypeImageSeeder::class,
                VehicleSeeder::class,
                CreateSampleVehiclesSeeder::class,
                PermissionSeeder::class,
                RoleSeeder::class,
            ]);

            $this->call([
                PackageSeeder::class,
                RegularPackageSeeder::class,
            ]);

            $this->call([
                CleanupScheduleSlotsSeeder::class,
                AvailabilitySeeder::class,
            ]);
            $this->call([
                CleanupScheduleSlotsSeeder::class,
                AvailabilitySeeder::class,
            ]);

            $this->call([
                PromoCodeSeeder::class,
                CreateSamplePromoCodesSeeder::class,
                CustomerSeeder::class,
                CustomerAuthSeeder::class,
            ]);

            // Step 6: Seed reservations (should be last)
            $this->call([
                ReservationSeeder::class,
            ]);

            $this->command->info('Database seeding completed successfully!');
        } catch (\Exception $e) {
            Log::error('Database seeding failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function createAdminUser(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => true,
                'user_type' => "admin",
            ]
        );

        $this->command->info('Admin user created/updated successfully.');
    }
}
