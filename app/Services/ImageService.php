<?php

namespace App\Services;

use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * Upload multiple images for a model
     */
    public function uploadMultipleImages($model, array $files, string $folder = 'images', bool $createThumbnails = true): array
    {
        $uploadedImages = [];
        $existingImageCount = $model->images()->count();

        foreach ($files as $index => $file) {
            if ($file && $file->isValid()) {
                $isPrimary = ($existingImageCount === 0 && $index === 0);

                $image = $this->uploadSingleImage($model, $file, $folder, $createThumbnails, $index, $isPrimary);
                $uploadedImages[] = $image;
            }
        }

        return $uploadedImages;
    }

    /**
     * Upload a single image
     */
    public function uploadSingleImage($model, UploadedFile $file, string $folder = 'images', bool $createThumbnail = true, int $sortOrder = 0, ?bool $isPrimary = null): Image
    {
        // Generate unique filename
        $filename = $this->generateUniqueFilename($file);
        $path = $folder.'/'.$filename;

        // Store the original image directly in public/storage
        $filePath = $file->storeAs($folder, $filename, 'public_storage');

        // Create thumbnail if requested
        if ($createThumbnail) {
            $this->createThumbnail($filePath, $folder);
        }

        if ($isPrimary === null) {
            $isPrimary = $model->images()->count() === 0;
        }

        $mimeType = $this->getMimeTypeWithFallback($file);

        // Create image record
        $image = $model->images()->create([
            'image_path' => $filePath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'file_size' => $file->getSize(),
            'sort_order' => $sortOrder,
            'is_primary' => $isPrimary,
            'alt_text' => $file->getClientOriginalName(),
        ]);

        return $image;
    }

    /**
     * Create thumbnail for an image
     */
    public function createThumbnail(string $imagePath, string $folder): bool
    {
        try {
            $pathInfo = pathinfo($imagePath);
            $thumbnailPath = $pathInfo['dirname'].'/thumbnails/'.$pathInfo['basename'];

            // Create thumbnails directory if it doesn't exist
            Storage::disk('public_storage')->makeDirectory($pathInfo['dirname'].'/thumbnails');

            // For now, just copy the original image as thumbnail
            // TODO: Implement proper thumbnail creation with Intervention Image
            Storage::disk('public_storage')->copy($imagePath, $thumbnailPath);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create thumbnail: '.$e->getMessage());

            return false;
        }
    }

    private function generateUniqueFilename(UploadedFile $file): string
    {
        return (string) Str::uuid().'.'.$file->getClientOriginalExtension();
    }

    /**
     * Delete image and its files
     */
    public function deleteImage(Image $image): bool
    {
        try {
            // Delete the image record (this will trigger the model's boot method to delete files)
            $image->delete();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete image: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Set primary image
     */
    public function setPrimaryImage($model, Image $image): bool
    {
        try {
            // Remove primary flag from all other images
            $model->images()->where('is_primary', true)->update(['is_primary' => false]);

            // Set this image as primary
            $image->update(['is_primary' => true]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to set primary image: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Reorder images
     */
    public function reorderImages(array $imageIds): bool
    {
        try {
            foreach ($imageIds as $index => $imageId) {
                Image::where('id', $imageId)->update(['sort_order' => $index]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to reorder images: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Update image alt text
     */
    public function updateAltText(Image $image, string $altText): bool
    {
        try {
            $image->update(['alt_text' => $altText]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update alt text: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get folder path based on model type
     */
    public function getFolderPath($model): string
    {
        $modelType = class_basename($model);

        return match ($modelType) {
            'Package' => 'packages',
            'Vehicle' => 'vehicles',
            'VehicleType' => 'vehicle-types',
            default => 'images',
        };
    }

    /**
     * Get MIME type with fallback for missing fileinfo extension
     */
    private function getMimeTypeWithFallback(UploadedFile $file): string
    {
        try {
            // Try to get MIME type using Laravel's method (requires fileinfo extension)
            return $file->getMimeType();
        } catch (\Exception $e) {
            // Fallback: determine MIME type from file extension
            $extension = strtolower($file->getClientOriginalExtension());

            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'bmp' => 'image/bmp',
                'svg' => 'image/svg+xml',
                'ico' => 'image/x-icon',
                'tiff' => 'image/tiff',
                'tif' => 'image/tiff',
                'avif' => 'image/avif',
                'heic' => 'image/heic',
                'heif' => 'image/heif',
            ];

            return $mimeTypes[$extension] ?? 'application/octet-stream';
        }
    }
}
