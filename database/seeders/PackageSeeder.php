<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\RiderType;
use App\Models\PriceType;
use App\Models\VehicleType;
use App\Models\Image;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding packages...');

        try {
            // Get vehicle types to ensure we use correct IDs
            $atvType = VehicleType::where('name', 'ATV')->first();
            $utvType = VehicleType::where('name', 'UTV')->first();
            $regularType = VehicleType::where('name', 'Regular')->first();

            if (!$atvType || !$utvType || !$regularType) {
                throw new \Exception('Vehicle types not found. Please run VehicleTypeSeeder first.');
            }

            $packages = [
                [
                    'name' => 'ATV/UTV Trail Rides',
                    'subtitle' => 'Experience the thrill of off-road adventure',
                    'type' => 'atv',
                    'min_participants' => 1,
                    'max_participants' => 10,
                    'display_starting_price' => 1200.00,
                    'is_active' => true,
                    // 'selected_weekday' => true,
                    // 'selected_weekend' => true,
                    'notes' => 'Perfect for adventure seekers looking for an exciting off-road experience.',
                    'details' => 'Our ATV/UTV trail rides offer an unforgettable adventure through scenic trails and challenging terrain.',
                    'vehicle_type_ids' => [$atvType->id, $utvType->id],
                    'pricing_options' => [
                        [
                            'rider_type_name' => 'Single Rider',
                            'prices' => [
                                ['price_type_slug' => 'weekday', 'amount' => 1200.00],
                                ['price_type_slug' => 'weekend', 'amount' => 1500.00],
                            ]
                        ],
                        [
                            'rider_type_name' => 'Double Rider',
                            'prices' => [
                                ['price_type_slug' => 'weekday', 'amount' => 1500.00],
                                ['price_type_slug' => 'weekend', 'amount' => 1800.00],
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'Regular Package',
                    'subtitle' => 'Standard adventure packages',
                    'type' => 'regular',
                    'min_participants' => 1,
                    'max_participants' => 10,
                    'display_starting_price' => 800.00,
                    'is_active' => true,
                    // 'selected_weekday' => true,
                    // 'selected_weekend' => true,
                    'notes' => 'Our standard package for regular adventure tours.',
                    'details' => 'Enjoy a standard adventure tour with our regular vehicles.',
                    'vehicle_type_ids' => [$regularType->id],
                    'images' => [
                        [
                            'image_path' => 'packages/regular-package.jpg',
                            'original_name' => 'regular-package.jpg',
                            'mime_type' => 'image/jpeg',
                            'file_size' => 1024000,
                            'sort_order' => 1,
                            'is_primary' => true,
                            'alt_text' => 'Regular Package - Main Image',
                        ],
                    ],
                    'pricing_options' => [
                        [
                            'rider_type_name' => 'Adventure Tour',
                            'prices' => [
                                ['price_type_slug' => 'weekday', 'amount' => 800.00],
                                ['price_type_slug' => 'weekend', 'amount' => 1000.00],
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'Premium ATV Experience',
                    'subtitle' => 'Exclusive ATV adventure with premium features',
                    'type' => 'atv',
                    'min_participants' => 1,
                    'max_participants' => 6,
                    'display_starting_price' => 2000.00,
                    'is_active' => true,
                    'selected_weekday' => true,
                    'selected_weekend' => true,
                    'notes' => 'Premium ATV experience with additional features and longer duration.',
                    'details' => 'Experience the ultimate ATV adventure with premium vehicles and extended trail access.',
                    'vehicle_type_ids' => [$atvType->id],
                    'pricing_options' => [
                        [
                            'rider_type_name' => 'Premium Single',
                            'prices' => [
                                ['price_type_slug' => 'weekday', 'amount' => 2000.00],
                                ['price_type_slug' => 'weekend', 'amount' => 2500.00],
                            ]
                        ],
                        [
                            'rider_type_name' => 'Premium Double',
                            'prices' => [
                                ['price_type_slug' => 'weekday', 'amount' => 2500.00],
                                ['price_type_slug' => 'weekend', 'amount' => 3000.00],
                            ]
                        ]
                    ]
                ]
            ];

            $createdPackages = 0;
            $updatedPackages = 0;

            foreach ($packages as $packageData) {
                $pricingOptions = $packageData['pricing_options'];
                $vehicleTypeIds = $packageData['vehicle_type_ids'] ?? [];
                $images = $packageData['images'] ?? [];
                unset($packageData['pricing_options'], $packageData['vehicle_type_ids'], $packageData['images']);
                
                $package = Package::updateOrCreate(
                    ['name' => $packageData['name']],
                    $packageData
                );

                if ($package->wasRecentlyCreated) {
                    $createdPackages++;
                } else {
                    $updatedPackages++;
                }
                
                // Attach vehicle types
                if (!empty($vehicleTypeIds)) {
                    $package->vehicleTypes()->sync($vehicleTypeIds);
                }
                
                // Create images for the package
                foreach ($images as $imageData) {
                    Image::updateOrCreate(
                        [
                            'imageable_type' => Package::class,
                            'imageable_id' => $package->id,
                            'original_name' => $imageData['original_name']
                        ],
                        array_merge($imageData, [
                            'imageable_type' => Package::class,
                            'imageable_id' => $package->id,
                        ])
                    );
                }
                
                // Create prices (replacing variants)
                $package->packagePrices()->delete(); // Clear existing prices

                foreach ($pricingOptions as $option) {
                    $riderTypeName = $option['rider_type_name'];
                    $riderTypeSlug = \Illuminate\Support\Str::slug($riderTypeName);
                    
                    $riderType = RiderType::firstOrCreate(
                        ['slug' => $riderTypeSlug],
                        ['name' => $riderTypeName]
                    );

                    foreach ($option['prices'] as $priceData) {
                        $pt = PriceType::where('slug', $priceData['price_type_slug'])->first();
                        if (!$pt) continue;

                        $package->packagePrices()->create([
                            'rider_type_id' => $riderType->id,
                            'price_type_id' => $pt->id,
                            'price' => $priceData['amount'],
                            'is_active' => true,
                        ]);
                    }
                }
            }

            $this->command->info("Package seeding completed: {$createdPackages} created, {$updatedPackages} updated.");

        } catch (\Exception $e) {
            $this->command->error('Error seeding packages: ' . $e->getMessage());
            Log::error('Package seeding failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }
}
