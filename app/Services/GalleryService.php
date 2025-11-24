<?php

namespace App\Services;

use App\Models\Gallery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GalleryService
{
    /**
     * Upload multiple images to gallery
     */
    public function uploadImages(array $files, int $uploadedBy): array
    {
        $uploadedImages = [];

        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $image = $this->uploadSingleImage($file, $uploadedBy);
                $uploadedImages[] = $image;
            }
        }

        return $uploadedImages;
    }

    /**
     * Upload a single image to gallery
     */
    public function uploadSingleImage(UploadedFile $file, int $uploadedBy): Gallery
    {
        // Generate unique filename
        $filename = $this->generateUniqueFilename($file);
        $path = 'gallery/' . $filename;

        // Store the original image
        $filePath = $file->storeAs('gallery', $filename, 'public_storage');

        // Create thumbnail
        $thumbnailPath = $this->createThumbnail($filePath);

        // Get MIME type with fallback
        $mimeType = $this->getMimeTypeWithFallback($file);

        // Create gallery record
        $gallery = Gallery::create([
            'image_path' => $filePath,
            'thumbnail_path' => $thumbnailPath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'file_size' => $file->getSize(),
            'alt_text' => $file->getClientOriginalName(),
            'uploaded_by' => $uploadedBy,
        ]);

        return $gallery;
    }

    /**
     * Create thumbnail for an image
     */
    public function createThumbnail(string $imagePath): ?string
    {
        try {
            $pathInfo = pathinfo($imagePath);
            $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['basename'];

            // Create thumbnails directory if it doesn't exist
            Storage::disk('public_storage')->makeDirectory($pathInfo['dirname'] . '/thumbnails');

            // For now, just copy the original image as thumbnail
            // TODO: Implement proper thumbnail creation with Intervention Image
            Storage::disk('public_storage')->copy($imagePath, $thumbnailPath);

            return $thumbnailPath;
        } catch (\Exception $e) {
            Log::error('Failed to create thumbnail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete image and its files
     */
    public function deleteImage(Gallery $gallery): bool
    {
        try {
            $gallery->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete gallery image: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Search images with filters
     */
    public function searchImages(?string $search = '', ?array $tags = [], int $perPage = 20)
    {
        $query = Gallery::with('uploader');

        $search = $search ?? '';
        $tags = $tags ?? [];

        if (!empty($search)) {
            $query->search($search);
        }

        if (!empty($tags)) {
            $query->withTags($tags);
        }

        return $query->ordered()->paginate($perPage);
    }

    /**
     * Get images for modal (with pagination)
     */
    public function getImagesForModal(?string $search = '', ?array $tags = [], int $perPage = 12)
    {
        $search = $search ?? '';
        $tags = $tags ?? [];
        return $this->searchImages($search, $tags, $perPage);
    }

    /**
     * Copy gallery image to another location
     */
    public function copyImageToLocation(Gallery $gallery, string $destinationFolder): array
    {
        try {
            $pathInfo = pathinfo($gallery->image_path);
            $filename = $this->generateUniqueFilenameFromPath($gallery->image_path);
            $newPath = $destinationFolder . '/' . $filename;

            // Copy the original image
            Storage::disk('public_storage')->copy($gallery->image_path, $newPath);

            // Copy thumbnail if exists
            $thumbnailPath = null;
            if ($gallery->thumbnail_path) {
                $thumbPathInfo = pathinfo($gallery->thumbnail_path);
                $thumbFilename = $this->generateUniqueFilenameFromPath($gallery->thumbnail_path);
                $newThumbPath = $destinationFolder . '/thumbnails/' . $thumbFilename;
                
                // Create thumbnails directory if it doesn't exist
                Storage::disk('public_storage')->makeDirectory($destinationFolder . '/thumbnails');
                
                Storage::disk('public_storage')->copy($gallery->thumbnail_path, $newThumbPath);
                $thumbnailPath = $newThumbPath;
            }

            return [
                'image_path' => $newPath,
                'thumbnail_path' => $thumbnailPath,
                'original_name' => $gallery->original_name,
                'mime_type' => $gallery->mime_type,
                'file_size' => $gallery->file_size,
                'alt_text' => $gallery->alt_text,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to copy gallery image: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(40) . '.' . $extension;

        return $filename;
    }

    /**
     * Generate unique filename from existing path
     */
    private function generateUniqueFilenameFromPath(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $filename = Str::random(40) . '.' . $extension;

        return $filename;
    }

    /**
     * Get MIME type with fallback for missing fileinfo extension
     */
    private function getMimeTypeWithFallback(UploadedFile $file): string
    {
        try {
            return $file->getMimeType();
        } catch (\Exception $e) {
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
