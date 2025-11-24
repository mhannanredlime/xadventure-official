<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;
use App\Models\VehicleType;
use Illuminate\Support\Facades\DB;

class UpdatePackageVehicleTypesSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing relationships
        DB::table('package_vehicle_types')->truncate();

        // Get vehicle types
        $atvType = VehicleType::where('name', 'ATV')->first();
        $utvType = VehicleType::where('name', 'UTV')->first();
        $regularType = VehicleType::where('name', 'Regular')->first();

        if (!$atvType || !$utvType || !$regularType) {
            $this->command->error('Vehicle types not found. Please run VehicleSeeder first.');
            return;
        }

        // Get packages
        $atvUtvPackage = Package::where('name', 'ATV/UTV Trail Rides')->first();
        $regularPackage = Package::where('name', 'Regular Package')->first();

        if ($atvUtvPackage) {
            // ATV/UTV package uses both ATV and UTV vehicle types
            $atvUtvPackage->vehicleTypes()->attach([$atvType->id, $utvType->id]);
            $this->command->info("Updated ATV/UTV Trail Rides package with ATV and UTV vehicle types");
        }

        if ($regularPackage) {
            // Regular packages don't need vehicle types
            $this->command->info("Regular Package doesn't need vehicle types");
        }

        // If packages don't exist, create them
        if (!$atvUtvPackage) {
            $atvUtvPackage = Package::create([
                'name' => 'ATV/UTV Trail Rides',
                'subtitle' => 'Experience the thrill of off-road adventure',
                'type' => 'atv',
                'min_participants' => 1,
                'max_participants' => 10,
                'is_active' => true,
            ]);
            $atvUtvPackage->vehicleTypes()->attach([$atvType->id, $utvType->id]);
            $this->command->info("Created ATV/UTV Trail Rides package with ATV and UTV vehicle types");
        }

        if (!$regularPackage) {
            $regularPackage = Package::create([
                'name' => 'Regular Package',
                'subtitle' => 'Standard adventure packages',
                'type' => 'regular',
                'min_participants' => 1,
                'max_participants' => 10,
                'is_active' => true,
            ]);
            $this->command->info("Created Regular Package (no vehicle types needed)");
        }

        $this->command->info('Package-vehicle type relationships updated successfully!');
    }
}
