<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleType;
use App\Models\Vehicle;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding vehicles...');

        try {
            // Create Vehicle Types with images
            $vehicleTypes = [
            [
                'name' => 'ATV', 
                'subtitle' => '2 Seater ATV',
                'seating_capacity' => 2,
                'is_active' => true,
                'images' => [
                    [
                        'image_path' => 'vehicle-types/atv-main.jpg',
                        'original_name' => 'atv-main.jpg',
                        'mime_type' => 'image/jpeg',
                        'file_size' => 1024000,
                        'sort_order' => 1,
                        'is_primary' => true,
                        'alt_text' => 'ATV Vehicle Type - Main Image',
                    ],
                    [
                        'image_path' => 'vehicle-types/atv-secondary.jpg',
                        'original_name' => 'atv-secondary.jpg',
                        'mime_type' => 'image/jpeg',
                        'file_size' => 1024000,
                        'sort_order' => 2,
                        'is_primary' => false,
                        'alt_text' => 'ATV Vehicle Type - Secondary Image',
                    ],
                ],
            ],
            [
                'name' => 'UTV', 
                'subtitle' => '4 Seater UTV',
                'seating_capacity' => 4,
                'is_active' => true,
                'images' => [
                    [
                        'image_path' => 'vehicle-types/utv-main.jpg',
                        'original_name' => 'utv-main.jpg',
                        'mime_type' => 'image/jpeg',
                        'file_size' => 1024000,
                        'sort_order' => 1,
                        'is_primary' => true,
                        'alt_text' => 'UTV Vehicle Type - Main Image',
                    ],
                    [
                        'image_path' => 'vehicle-types/utv-secondary.jpg',
                        'original_name' => 'utv-secondary.jpg',
                        'mime_type' => 'image/jpeg',
                        'file_size' => 1024000,
                        'sort_order' => 2,
                        'is_primary' => false,
                        'alt_text' => 'UTV Vehicle Type - Secondary Image',
                    ],
                ],
            ],
            [
                'name' => 'Regular', 
                'subtitle' => 'Single Rider',
                'seating_capacity' => 1,
                'is_active' => true,
                'images' => [
                    [
                        'image_path' => 'vehicle-types/regular-main.jpg',
                        'original_name' => 'regular-main.jpg',
                        'mime_type' => 'image/jpeg',
                        'file_size' => 1024000,
                        'sort_order' => 1,
                        'is_primary' => true,
                        'alt_text' => 'Regular Vehicle Type - Main Image',
                    ],
                ],
            ],
        ];

        foreach ($vehicleTypes as $vehicleTypeData) {
            // Extract images data
            $images = $vehicleTypeData['images'] ?? [];
            unset($vehicleTypeData['images']);

            // Create or update vehicle type
            $vehicleType = VehicleType::firstOrCreate(
                ['name' => $vehicleTypeData['name']], // Check by name to ensure uniqueness
                $vehicleTypeData
            );

            // Create sample images for the vehicle type if they don't exist
            foreach ($images as $imageData) {
                $existingImage = Image::where('imageable_type', VehicleType::class)
                    ->where('imageable_id', $vehicleType->id)
                    ->where('image_path', $imageData['image_path'])
                    ->first();

                if (!$existingImage) {
                    // Create placeholder image file if it doesn't exist
                    $this->createPlaceholderImage($imageData['image_path']);
                    
                    // Create image record
                    $vehicleType->images()->create($imageData);
                }
            }
        }

        // Get the created vehicle types
        $atvType = VehicleType::where('name', 'ATV')->first();
        $utvType = VehicleType::where('name', 'UTV')->first();
        $regularType = VehicleType::where('name', 'Regular')->first();

        // Create Sample Vehicles with images
        $vehicles = [
            [
                'vehicle_type_id' => $regularType->id,
                'name' => 'Adventure Bike',
                'details' => 'Standard adventure motorcycle for off-road exploration',
                'is_active' => true,
                'op_start_date' => '2025-08-10',
                'images' => [
                    [
                        'image_path' => 'vehicles/adventure-bike.jpg',
                        'original_name' => 'adventure-bike.jpg',
                        'mime_type' => 'image/jpeg',
                        'file_size' => 1024000,
                        'sort_order' => 1,
                        'is_primary' => true,
                        'alt_text' => 'Adventure Bike - Main Image',
                    ],
                ],
            ],
            [
                'vehicle_type_id' => $utvType->id,
                'name' => 'Can-Am Maverick X3',
                'details' => 'Premium UTV with superior handling and performance',
                'is_active' => true,
                'op_start_date' => '2025-08-10',
                'images' => [
                    [
                        'image_path' => 'vehicles/canam-maverick.jpg',
                        'original_name' => 'canam-maverick.jpg',
                        'mime_type' => 'image/jpeg',
                        'file_size' => 1024000,
                        'sort_order' => 1,
                        'is_primary' => true,
                        'alt_text' => 'Can-Am Maverick X3 - Main Image',
                    ],
                ],
            ],
            [
                'vehicle_type_id' => $atvType->id,
                'name' => 'Honda TRX 450R',
                'details' => 'High-performance ATV for experienced riders',
                'is_active' => true,
                'op_start_date' => '2025-08-10',
                'images' => [
                    [
                        'image_path' => 'vehicles/honda-trx.jpg',
                        'original_name' => 'honda-trx.jpg',
                        'mime_type' => 'image/jpeg',
                        'file_size' => 1024000,
                        'sort_order' => 1,
                        'is_primary' => true,
                        'alt_text' => 'Honda TRX 450R - Main Image',
                    ],
                ],
            ],
            [
                'vehicle_type_id' => $regularType->id,
                'name' => 'Mountain Bike',
                'details' => 'Off-road mountain biking experience',
                'is_active' => true,
                'op_start_date' => '2025-08-10',
                'images' => [
                    [
                        'image_path' => 'vehicles/mountain-bike.jpg',
                        'original_name' => 'mountain-bike.jpg',
                        'mime_type' => 'image/jpeg',
                        'file_size' => 1024000,
                        'sort_order' => 1,
                        'is_primary' => true,
                        'alt_text' => 'Mountain Bike - Main Image',
                    ],
                ],
            ],
            [
                'vehicle_type_id' => $atvType->id,
                'name' => 'Yamaha YFZ450R',
                'details' => 'Sport ATV with racing capabilities',
                'is_active' => false,
                'op_start_date' => '2025-08-10',
                'images' => [
                    [
                        'image_path' => 'vehicles/yamaha-yfz.jpg',
                        'original_name' => 'yamaha-yfz.jpg',
                        'mime_type' => 'image/jpeg',
                        'file_size' => 1024000,
                        'sort_order' => 1,
                        'is_primary' => true,
                        'alt_text' => 'Yamaha YFZ450R - Main Image',
                    ],
                ],
            ],
        ];

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($vehicles as $vehicleData) {
            // Extract images data
            $images = $vehicleData['images'] ?? [];
            unset($vehicleData['images']);

            // Create or update vehicle
            $vehicle = Vehicle::updateOrCreate(
                ['name' => $vehicleData['name']],
                $vehicleData
            );

            if ($vehicle->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $updatedCount++;
            }

            // Create sample images for the vehicle
            foreach ($images as $imageData) {
                // Create placeholder image file if it doesn't exist
                $this->createPlaceholderImage($imageData['image_path']);
                
                // Create image record
                $vehicle->images()->updateOrCreate(
                    [
                        'original_name' => $imageData['original_name'],
                        'imageable_type' => Vehicle::class,
                        'imageable_id' => $vehicle->id
                    ],
                    $imageData
                );
            }
        }

        $this->command->info("Vehicle seeding completed: {$createdCount} created, {$updatedCount} updated.");

        } catch (\Exception $e) {
            $this->command->error('Error seeding vehicles: ' . $e->getMessage());
            Log::error('Vehicle seeding failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }

    /**
     * Create a placeholder image file for seeding
     */
    private function createPlaceholderImage($imagePath)
    {
        $fullPath = storage_path('app/public/' . $imagePath);
        $directory = dirname($fullPath);
        
        // Create directory if it doesn't exist
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Create a realistic placeholder image if the file doesn't exist
        if (!file_exists($fullPath)) {
            // Determine which vehicle image to use based on the path
            $sourceImage = null;
            
            if (strpos($imagePath, 'atv') !== false || strpos($imagePath, 'honda-trx') !== false || strpos($imagePath, 'yamaha-yfz') !== false) {
                // Use ATV image
                $sourceImage = public_path('frontEnd/images/atv-transparent.svg');
            } elseif (strpos($imagePath, 'utv') !== false || strpos($imagePath, 'canam') !== false) {
                // Use UTV image
                $sourceImage = public_path('frontEnd/images/utv-transparent.svg');
            } elseif (strpos($imagePath, 'adventure-bike') !== false || strpos($imagePath, 'mountain-bike') !== false) {
                // Use regular vehicle image
                $sourceImage = public_path('frontEnd/images/atv-trial.svg');
            } else {
                // Default to ATV image
                $sourceImage = public_path('frontEnd/images/atv-transparent.svg');
            }
            
            // Copy the actual image file
            if (file_exists($sourceImage)) {
                copy($sourceImage, $fullPath);
            } else {
                // Fallback to creating a colored rectangle if source image doesn't exist
                $this->createColoredRectangle($fullPath, $imagePath);
            }
        }
    }
    
    /**
     * Create a colored rectangle as fallback
     */
    private function createColoredRectangle($fullPath, $imagePath)
    {
        $width = 800;
        $height = 600;
        $image = imagecreatetruecolor($width, $height);
        
        // Set colors based on vehicle type
        if (strpos($imagePath, 'atv') !== false || strpos($imagePath, 'honda-trx') !== false || strpos($imagePath, 'yamaha-yfz') !== false) {
            $bgColor = imagecolorallocate($image, 255, 107, 53); // Orange
            $textColor = imagecolorallocate($image, 255, 255, 255); // White
            $text = 'ATV';
        } elseif (strpos($imagePath, 'utv') !== false || strpos($imagePath, 'canam') !== false) {
            $bgColor = imagecolorallocate($image, 52, 152, 219); // Blue
            $textColor = imagecolorallocate($image, 255, 255, 255); // White
            $text = 'UTV';
        } else {
            $bgColor = imagecolorallocate($image, 46, 204, 113); // Green
            $textColor = imagecolorallocate($image, 255, 255, 255); // White
            $text = 'Vehicle';
        }
        
        imagefill($image, 0, 0, $bgColor);
        
        // Add text
        $fontSize = 48;
        $fontFile = storage_path('app/fonts/arial.ttf');
        
        // Use a fallback if custom font is not available
        if (file_exists($fontFile)) {
            imagettftext($image, $fontSize, 0, $width/2 - 60, $height/2, $textColor, $fontFile, $text);
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
