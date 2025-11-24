<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PackageVariant;
use App\Models\VariantPrice;
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
                    'selected_weekday' => true,
                    'selected_weekend' => true,
                    'notes' => 'Perfect for adventure seekers looking for an exciting off-road experience.',
                    'details' => 'Our ATV/UTV trail rides offer an unforgettable adventure through scenic trails and challenging terrain.',
                    'vehicle_type_ids' => [$atvType->id, $utvType->id],
                    'variants' => [
                        [
                            'variant_name' => 'Single Rider',
                            'capacity' => 1,
                            'is_active' => true,
                            'prices' => [
                                ['price_type' => 'weekday', 'amount' => 1200.00, 'original_amount' => 1200.00],
                                ['price_type' => 'weekend', 'amount' => 1500.00, 'original_amount' => 1500.00],
                            ]
                        ],
                        [
                            'variant_name' => 'Double Rider',
                            'capacity' => 2,
                            'is_active' => true,
                            'prices' => [
                                ['price_type' => 'weekday', 'amount' => 1500.00, 'original_amount' => 1500.00],
                                ['price_type' => 'weekend', 'amount' => 1800.00, 'original_amount' => 1800.00],
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
                    'selected_weekday' => true,
                    'selected_weekend' => true,
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
                    'variants' => [
                        [
                            'variant_name' => 'Adventure Tour',
                            'capacity' => 4,
                            'is_active' => true,
                            'prices' => [
                                ['price_type' => 'weekday', 'amount' => 800.00, 'original_amount' => 800.00],
                                ['price_type' => 'weekend', 'amount' => 1000.00, 'original_amount' => 1000.00],
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
                    'variants' => [
                        [
                            'variant_name' => 'Premium Single',
                            'capacity' => 1,
                            'is_active' => true,
                            'prices' => [
                                ['price_type' => 'weekday', 'amount' => 2000.00, 'original_amount' => 2000.00],
                                ['price_type' => 'weekend', 'amount' => 2500.00, 'original_amount' => 2500.00],
                            ]
                        ],
                        [
                            'variant_name' => 'Premium Double',
                            'capacity' => 2,
                            'is_active' => true,
                            'prices' => [
                                ['price_type' => 'weekday', 'amount' => 2500.00, 'original_amount' => 2500.00],
                                ['price_type' => 'weekend', 'amount' => 3000.00, 'original_amount' => 3000.00],
                            ]
                        ]
                    ]
                ]
            ];

            $createdPackages = 0;
            $updatedPackages = 0;

            foreach ($packages as $packageData) {
                $variants = $packageData['variants'];
                $vehicleTypeIds = $packageData['vehicle_type_ids'] ?? [];
                $images = $packageData['images'] ?? [];
                unset($packageData['variants'], $packageData['vehicle_type_ids'], $packageData['images']);
                
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
                
                // Create variants and prices
                foreach ($variants as $variantData) {
                    $prices = $variantData['prices'];
                    unset($variantData['prices']);
                    
                    $variant = PackageVariant::updateOrCreate(
                        [
                            'package_id' => $package->id,
                            'variant_name' => $variantData['variant_name']
                        ],
                        array_merge($variantData, ['package_id' => $package->id])
                    );
                    
                    // Create prices for the variant
                    foreach ($prices as $priceData) {
                        VariantPrice::updateOrCreate(
                            [
                                'package_variant_id' => $variant->id,
                                'price_type' => $priceData['price_type']
                            ],
                            array_merge($priceData, ['package_variant_id' => $variant->id])
                        );
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
