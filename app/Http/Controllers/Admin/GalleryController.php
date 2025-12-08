<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Services\GalleryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GalleryController extends Controller
{
    protected $galleryService;

    public function __construct(GalleryService $galleryService)
    {
        $this->galleryService = $galleryService;
    }

    /**
     * Display the gallery page
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '') ?? '';
        $tags = $request->get('tags', []) ?? [];
        
        $images = $this->galleryService->searchImages($search, $tags, 20);
        
        return view('admin.gallery.index', compact('images', 'search', 'tags'));
    }

    /**
     * Upload images to gallery
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images.*' => 'required|image|mimes:jpeg,png,jpg ,webp,bmp,svg|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $uploadedImages = $this->galleryService->uploadImages(
                $request->file('images'),
                Auth::id()
            );

            return response()->json([
                'success' => true,
                'message' => count($uploadedImages) . ' image(s) uploaded successfully',
                'images' => $uploadedImages
            ]);
        } catch (\Exception $e) {
            Log::error('Gallery upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while uploading images'
            ], 500);
        }
    }

    /**
     * Get images for modal (with pagination and filtering)
     */
    public function getImages(Request $request)
    {
        $search = $request->get('search', '') ?? '';
        $tags = $request->get('tags', []) ?? [];
        $perPage = $request->get('per_page', 12);
        
        $images = $this->galleryService->getImagesForModal($search, $tags, $perPage);

        return response()->json([
            'success' => true,
            'images' => $images->items(),
            'pagination' => [
                'current_page' => $images->currentPage(),
                'last_page' => $images->lastPage(),
                'per_page' => $images->perPage(),
                'total' => $images->total(),
                'has_more' => $images->hasMorePages(),
            ]
        ]);
    }

    /**
     * Get single image details
     */
    public function show(Gallery $gallery)
    {
        $gallery->load('uploader');
        
        return response()->json([
            'success' => true,
            'image' => $gallery
        ]);
    }

    /**
     * Update image details
     */
    public function update(Request $request, Gallery $gallery)
    {
        $validator = Validator::make($request->all(), [
            'alt_text' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $gallery->update([
                'alt_text' => $request->alt_text,
                'tags' => $request->tags ?? []
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image updated successfully',
                'image' => $gallery
            ]);
        } catch (\Exception $e) {
            Log::error('Gallery update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the image'
            ], 500);
        }
    }

    /**
     * Delete image
     */
    public function destroy(Gallery $gallery)
    {
        try {
            $success = $this->galleryService->deleteImage($gallery);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Image deleted successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Gallery delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the image'
            ], 500);
        }
    }

    /**
     * Copy image to specific location (for use in other forms)
     */
    public function copyToLocation(Request $request, Gallery $gallery)
    {
        $validator = Validator::make($request->all(), [
            'destination_folder' => 'required|string|in:vehicle-types,vehicles,packages'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid destination folder',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $imageData = $this->galleryService->copyImageToLocation(
                $gallery,
                $request->destination_folder
            );

            return response()->json([
                'success' => true,
                'message' => 'Image copied successfully',
                'image_data' => $imageData
            ]);
        } catch (\Exception $e) {
            Log::error('Gallery copy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while copying the image'
            ], 500);
        }
    }
}
