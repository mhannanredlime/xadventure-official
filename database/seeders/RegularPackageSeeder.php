<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\RiderType;
use App\Models\PriceType;
use Illuminate\Database\Seeder;

class RegularPackageSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure Price Types exist
        $weekdayPriceType = \App\Models\PriceType::firstOrCreate(['slug' => 'weekday'], ['name' => 'Weekday']);
        $weekendPriceType = \App\Models\PriceType::firstOrCreate(['slug' => 'weekend'], ['name' => 'Weekend']);

        $packages = [
            [
                'name' => 'Person Package',
                'subtitle' => 'Perfect for small groups',
                'type' => 'regular',
                'min_participants' => 2,
                'max_participants' => 2,
                'is_active' => true,
                'details' => 'Small group adventure package with personal attention from our professional instructors.',
                'images' => [
                    [
                        'image_path' => 'packages/person-package.jpg',
                        'original_name' => 'person-package.jpg',
                        'mime_type' => 'image/jpeg',
                        'file_size' => 1024000,
                        'sort_order' => 1,
                        'is_primary' => true,
                        'alt_text' => 'Person Package - Small Group Adventure',
                    ],
                ],
                'pricing_options' => [
                    [
                        'rider_type_name' => 'Group of 2',
                        'prices' => [
                            ['price_type_slug' => 'weekday', 'amount' => 99.00],
                            ['price_type_slug' => 'weekend', 'amount' => 99.00],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Popular Package',
                'subtitle' => 'Most popular choice for groups',
                'type' => 'regular',
                'min_participants' => 10,
                'max_participants' => 10,
                'is_active' => true,
                'details' => 'Perfect for large groups with private gazebo and professional instructor.',
                'images' => [
                    [
                        'image_path' => 'packages/popular-package.jpg',
                        'original_name' => 'popular-package.jpg',
                        'mime_type' => 'image/jpeg',
                        'file_size' => 1024000,
                        'sort_order' => 1,
                        'is_primary' => true,
                        'alt_text' => 'Popular Package - Group Adventure',
                    ],
                ],
                'pricing_options' => [
                    [
                        'rider_type_name' => 'Group of 10',
                        'prices' => [
                            ['price_type_slug' => 'weekday', 'amount' => 179.00],
                            ['price_type_slug' => 'weekend', 'amount' => 179.00],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Basic Package',
                'subtitle' => 'Great value for small groups',
                'type' => 'regular',
                'min_participants' => 5,
                'max_participants' => 5,
                'is_active' => true,
                'details' => 'Premium experience for small groups with premium gazebo.',
                'images' => [
                    [
                        'image_path' => 'packages/basic-package.jpg',
                        'original_name' => 'basic-package.jpg',
                        'mime_type' => 'image/jpeg',
                        'file_size' => 1024000,
                        'sort_order' => 1,
                        'is_primary' => true,
                        'alt_text' => 'Basic Package - Premium Group Experience',
                    ],
                ],
                'pricing_options' => [
                    [
                        'rider_type_name' => 'Group of 5',
                        'prices' => [
                            ['price_type_slug' => 'weekday', 'amount' => 299.00],
                            ['price_type_slug' => 'weekend', 'amount' => 299.00],
                        ]
                    ]
                ]
            ]
        ];

        foreach ($packages as $packageData) {
            $pricingOptions = $packageData['pricing_options'];
            $images = $packageData['images'] ?? [];
            unset($packageData['pricing_options'], $packageData['images']); // cleanup array for updateOrCreate
            
            $package = Package::updateOrCreate(
                ['name' => $packageData['name']],
                $packageData
            );
            
            // Create images for the package
            foreach ($images as $imageData) {
                // Create placeholder image file if it doesn't exist
                $this->createPlaceholderImage($imageData['image_path'], $package->name);
                
                // Create image record
                $package->images()->updateOrCreate(
                    ['image_path' => $imageData['image_path']],
                    $imageData
                );
            }
            
            // Handle Pricing Logic (Replacing Variants)
            
            // Important: Clear old prices to prevent duplication on re-seed
            $package->packagePrices()->delete();

            foreach ($pricingOptions as $option) {
                // Ensure the "Group of X" Rider Type exists
                // We assume slug is a normalized version of name
                $riderTypeName = $option['rider_type_name'];
                $riderTypeSlug = \Illuminate\Support\Str::slug($riderTypeName);

                $riderType = \App\Models\RiderType::firstOrCreate(
                    ['slug' => $riderTypeSlug],
                    ['name' => $riderTypeName]
                );

                foreach ($option['prices'] as $priceData) {
                     // Find the correct PriceType ID
                     $pt = \App\Models\PriceType::where('slug', $priceData['price_type_slug'])->first();
                     
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

        $this->command->info('Regular packages seeded successfully!');
    }

    /**
     * Create a placeholder image file for seeding
     */
    private function createPlaceholderImage($imagePath, $packageName)
    {
        $fullPath = storage_path('app/public/' . $imagePath);
        $directory = dirname($fullPath);
        
        // Create directory if it doesn't exist
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Create a simple placeholder image if the file doesn't exist
        if (!file_exists($fullPath)) {
            // Create a simple colored rectangle as placeholder
            $width = 800;
            $height = 600;
            $image = imagecreatetruecolor($width, $height);
            
            // Set colors based on package type
            $bgColor = imagecolorallocate($image, 255, 107, 53); // Orange
            $textColor = imagecolorallocate($image, 255, 255, 255); // White
            $text = $packageName;
            
            imagefill($image, 0, 0, $bgColor);
            
            // Add text
            $fontSize = 48;
            $fontFile = storage_path('app/fonts/arial.ttf');
            
            // Use a fallback if custom font is not available
            if (file_exists($fontFile)) {
                imagettftext($image, $fontSize, 0, $width/2 - 120, $height/2, $textColor, $fontFile, $text);
            } else {
                // Use built-in font
                $fontSize = 5;
                $textWidth = imagefontwidth($fontSize) * strlen($text);
                $textHeight = imagefontheight($fontSize);
                imagestring($image, $fontSize, ($width - $textWidth) / 2, ($height - $textHeight) / 2, $text, $textColor);
            }
            
            // Save the image
            imagejpeg($image, $fullPath, 90);
            imagedestroy($image);
        }
    }
}
