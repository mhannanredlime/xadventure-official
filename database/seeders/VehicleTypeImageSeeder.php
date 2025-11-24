<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use App\Models\Image;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class VehicleTypeImageSeeder extends Seeder
{
    public function run(): void
    {
        // Get all existing vehicle types
        $vehicleTypes = VehicleType::all();

        if ($vehicleTypes->isEmpty()) {
            $this->command->error('No vehicle types found. Please run VehicleTypeSeeder first.');
            return;
        }

        // Define image mappings for different vehicle types
        $imageMappings = [
            'ATV' => 'atv-transparent.svg',
            'UTV' => 'utv-transparent.svg',
            'Regular' => 'atv-trial.svg',
            'car' => 'atv-transparent.svg',
        ];

        foreach ($vehicleTypes as $vehicleType) {
            $imageFile = $imageMappings[$vehicleType->name] ?? 'vehicle-type.svg';
            $this->createVehicleTypeImage($vehicleType, $imageFile, $vehicleType->name . ' Vehicle Type', 'image/svg+xml');
        }

        $this->command->info('Vehicle type images created successfully!');
    }

    private function createVehicleTypeImage($vehicleType, $filename, $altText, $mimeType = 'image/svg+xml')
    {
        // Create a sample image path (this would normally be an uploaded file)
        $imagePath = 'vehicle-types/' . $filename;
        
        // Create the directory if it doesn't exist
        Storage::disk('public')->makeDirectory('vehicle-types');
        
        // Copy the actual image file from frontend images
        $sourcePath = public_path('frontEnd/images/' . $filename);
        if (file_exists($sourcePath)) {
            $sampleImageContent = file_get_contents($sourcePath);
            Storage::disk('public')->put($imagePath, $sampleImageContent);
        } else {
            // Fallback to default vehicle type image
            $sampleImageContent = file_get_contents(public_path('admin/images/vehicle-type.svg'));
            Storage::disk('public')->put($imagePath, $sampleImageContent);
        }

        // Create the image record in the database
        $image = Image::create([
            'image_path' => $imagePath,
            'original_name' => $filename,
            'mime_type' => $mimeType,
            'file_size' => strlen($sampleImageContent),
            'sort_order' => 1,
            'is_primary' => true,
            'alt_text' => $altText,
            'imageable_type' => VehicleType::class,
            'imageable_id' => $vehicleType->id,
        ]);

        $this->command->info("Created image for {$vehicleType->name}: {$filename}");
    }
}
