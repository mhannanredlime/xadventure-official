<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

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
                VehicleTypeSeeder::class,
                VehicleTypeImageSeeder::class,
                VehicleSeeder::class,
                CreateSampleVehiclesSeeder::class,
            ]);

            // Step 3: Seed packages and related data
            $this->call([
                PackageSeeder::class,
                // UpdatePackageVehicleTypesSeeder::class,
                RegularPackageSeeder::class,
            ]);

            // Step 4: Seed scheduling system
            $this->call([
                CleanupScheduleSlotsSeeder::class,
                AvailabilitySeeder::class,
            ]);

            // Step 5: Seed promotional and customer data
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
            $this->command->error('Error during seeding: ' . $e->getMessage());
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
        $this->command->info('Creating admin user...');

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => true,
                'user_type' => "admin",
            ]
        );

        $this->command->info('Admin user created/updated successfully.');
    }
}
