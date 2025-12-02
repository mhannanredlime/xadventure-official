<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\PriceType;
use App\Models\RiderType;
use Illuminate\Database\Seeder;

class PackagePriceSeeder extends Seeder
{
    public function run(): void
    {
        $priceTypes = PriceType::all()->keyBy('slug'); // e.g. 'weekday', 'weekend'
        $riders = RiderType::all();

        $weekdays = ['sun','mon','tue','wed','thu'];
        $weekends = ['fri','sat'];

        // Fetch a single ATV package
        $package = Package::where('type', 'atv')->first();

        if (!$package) {
            $this->command->info("No ATV package found!");
            return;
        }

        // Seed weekday prices
        foreach ($weekdays as $day) {
            foreach ($riders as $rider) {
                $price = $rider->slug === 'single-rider' ? 200 : 300;

                PackagePrice::updateOrCreate(
                    [
                        'package_id' => $package->id,
                        'rider_type_id' => $rider->id,
                        'price_type_id' => $priceTypes['weekday']->id, // regular for weekdays
                        'day' => $day,
                    ],
                    [
                        'price' => $price,
                        'is_active' => true,
                        'package_type_id' => $package->package_type_id, // correct column
                    ]
                );
            }
        }

        // Seed weekend prices
        foreach ($weekends as $day) {
            foreach ($riders as $rider) {
                $price = $rider->slug === 'single-rider' ? 250 : 350;

                PackagePrice::updateOrCreate(
                    [
                        'package_id' => $package->id,
                        'rider_type_id' => $rider->id,
                        'price_type_id' => $priceTypes['weekend']->id, // weekend price
                        'day' => $day,
                    ],
                    [
                        'price' => $price,
                        'is_active' => true,
                        'package_type_id' => $package->package_type_id,
                    ]
                );
            }
        }

        $this->command->info("PackagePrice created for 7 days for package ID: {$package->id}");
    }
}
