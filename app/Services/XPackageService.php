<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\PriceType;
use App\Services\ImageService;
use Illuminate\Support\Facades\DB;

class XPackageService
{
    protected ImageService $imageService;

    public function __construct()
    {
        $this->imageService = new ImageService();
    }

    /**
     * Create or update a regular package
     */
    public function saveRegularPackage(array $data, ?Package $package = null): Package
    {
        DB::beginTransaction();
        try {
            $packageData = [
                'name' => $data['packageName'],
                'subtitle' => $data['subTitle'] ?? null,
                'package_type_id' => $data['packageType'],
                'details' => $data['details'] ?? null,
                'display_starting_price' => $data['displayStartingPrice'] ?? null,
                'min_participants' => $data['minParticipant'],
                'max_participants' => $data['maxParticipant'],
                'is_active' => $data['is_active'] ?? true,
            ];

            // Create or update
            $package = $package ? tap($package)->update($packageData) : Package::create($packageData);

            // Handle images
            if (!empty($data['images'])) {
                $this->imageService->uploadMultipleImages($package, $data['images'], 'packages');
            }

            if (!empty($data['delete_images'])) {
                $this->imageService->deleteSpecificImages($package, $data['delete_images']);
            }

            // Sync day prices
            if (!empty($data['day_prices'])) {
                $this->syncDayPrices($package, $data['day_prices']);
            }

            DB::commit();

            return $package;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Sync day-wise prices for a package
     */
    protected function syncDayPrices(Package $package, array $dayPrices): void
    {
        // Delete existing prices
        PackagePrice::where('package_id', $package->id)->delete();

        $weekdayPriceType = PriceType::where('slug', 'weekday')->first();
        $weekendPriceType = PriceType::where('slug', 'weekend')->first();

        $now = now();
        $pricesToCreate = [];

        foreach ($dayPrices as $priceData) {
            if (empty($priceData['day']) || !isset($priceData['price'])) continue;

            $day = $priceData['day'];
            $price = $priceData['price'];
            $priceTypeId = in_array($day, ['fri', 'sat']) ? $weekendPriceType->id : $weekdayPriceType->id;

            $pricesToCreate[] = [
                'package_id' => $package->id,
                'package_type_id' => $package->package_type_id,
                'day' => $day,
                'price' => $price,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($pricesToCreate)) {
            PackagePrice::insert($pricesToCreate);
        }
    }
}
