<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use App\Models\Image;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class VehicleTypeSeeder extends Seeder
{
    public function run(): void
    {
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

            // Create sample images for the vehicle type
            foreach ($images as $imageData) {
                // Check if image already exists
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
            // Determine which vehicle type image to use based on the path
            $sourceImage = null;
            
            if (strpos($imagePath, 'atv') !== false) {
                // Use ATV image
                $sourceImage = public_path('frontEnd/images/atv-transparent.svg');
            } elseif (strpos($imagePath, 'utv') !== false) {
                // Use UTV image
                $sourceImage = public_path('frontEnd/images/utv-transparent.svg');
            } elseif (strpos($imagePath, 'regular') !== false) {
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
        if (strpos($imagePath, 'atv') !== false) {
            $bgColor = imagecolorallocate($image, 255, 107, 53); // Orange
            $textColor = imagecolorallocate($image, 255, 255, 255); // White
            $text = 'ATV';
        } elseif (strpos($imagePath, 'utv') !== false) {
            $bgColor = imagecolorallocate($image, 52, 152, 219); // Blue
            $textColor = imagecolorallocate($image, 255, 255, 255); // White
            $text = 'UTV';
        } else {
            $bgColor = imagecolorallocate($image, 46, 204, 113); // Green
            $textColor = imagecolorallocate($image, 255, 255, 255); // White
            $text = 'Regular';
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
