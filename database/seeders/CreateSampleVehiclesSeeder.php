<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\VehicleType;

class CreateSampleVehiclesSeeder extends Seeder
{
    public function run(): void
    {
        // Get vehicle types
        $atvType = VehicleType::where('name', 'ATV')->first();
        $utvType = VehicleType::where('name', 'UTV')->first();
        $regularType = VehicleType::where('name', 'Regular')->first();

        if (!$atvType || !$utvType || !$regularType) {
            $this->command->error('Vehicle types not found. Please run VehicleSeeder first.');
            return;
        }

        $createdCount = 0;
        $updatedCount = 0;

        // Create ATV vehicles
        for ($i = 1; $i <= 5; $i++) {
            $vehicle = Vehicle::updateOrCreate(
                ['name' => "ATV-{$i}"],
                [
                    'vehicle_type_id' => $atvType->id,
                    'name' => "ATV-{$i}",
                    'details' => "All-Terrain Vehicle {$i} - 2 Seater",
                    'is_active' => true,
                ]
            );
            
            if ($vehicle->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $updatedCount++;
            }
        }

        // Create UTV vehicles
        for ($i = 1; $i <= 3; $i++) {
            $vehicle = Vehicle::updateOrCreate(
                ['name' => "UTV-{$i}"],
                [
                    'vehicle_type_id' => $utvType->id,
                    'name' => "UTV-{$i}",
                    'details' => "Utility Task Vehicle {$i} - 4 Seater",
                    'is_active' => true,
                ]
            );
            
            if ($vehicle->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $updatedCount++;
            }
        }

        // Create Regular vehicles
        for ($i = 1; $i <= 4; $i++) {
            $vehicle = Vehicle::updateOrCreate(
                ['name' => "Regular-{$i}"],
                [
                    'vehicle_type_id' => $regularType->id,
                    'name' => "Regular-{$i}",
                    'details' => "Regular Vehicle {$i} - Single Rider",
                    'is_active' => true,
                ]
            );
            
            if ($vehicle->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $updatedCount++;
            }
        }

        $this->command->info("Sample vehicles created successfully!");
        $this->command->info("Created: {$createdCount} new vehicles, {$updatedCount} updated");
    }
}
