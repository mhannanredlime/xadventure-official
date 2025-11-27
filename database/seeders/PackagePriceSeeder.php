<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;
use App\Models\PackagePrice;

class PackagePriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = Package::all();

        foreach ($packages as $package) {
            $weekdays = ['monday','tuesday','wednesday','thursday','friday','sunday'];
            $weekends = ['friday','saturday'];

            // Seed weekday prices
            foreach ($weekdays as $day) {
                PackagePrice::create([
                    'package_id' => $package->id,
                    'type'       => 'weekday',
                    'day'        => $day,
                    'price'      => 1000,  // default price
                    'is_active'  => true,
                ]);
            }

            // Seed weekend prices
            foreach ($weekends as $day) {
                PackagePrice::create([
                    'package_id' => $package->id,
                    'type'       => 'weekend',
                    'day'        => $day,
                    'price'      => 1200,  // default price
                    'is_active'  => true,
                ]);
            }

        }
    }
}
