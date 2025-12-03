<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\PriceType;

class AtvUtvPackageService
{
    public function savePackagePrices(Package $package, $dayPrices)
    {
        // dd($dayPrices);
        if (! is_array($dayPrices)) {
            throw new \Exception('Invalid day_prices format');
        }

        $dayPricesArray = collect($dayPrices)->unique(function ($item) {
            return $item['day'].'-'.$item['rider_type_id'];
        })->values()->all();
        
        PackagePrice::where('package_id', $package->id)->delete();

        $priceData = [];
        foreach ($dayPricesArray as $row) {
            $priceType = PriceType::where('slug', $row['type'])->first();

            if (! $priceType) {
                continue;
            }

            $priceData[] = [
                'package_id' => $package->id,
                'price_type_id' => $priceType->id,
                'day' => $row['day'],
                'rider_type_id' => $row['rider_type_id'],
                'price' => $row['price'],
                'is_active' => true,
                'package_type_id' => $package->package_type_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert new prices
        if (! empty($priceData)) {
            PackagePrice::insert($priceData);
        }

        return true;
    }
}
