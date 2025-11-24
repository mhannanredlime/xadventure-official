<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Set an image as primary
     */
    public function setPrimary(Request $request, Image $image)
    {
        try {
            $success = $this->imageService->setPrimaryImage($image->imageable, $image);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Primary image updated successfully.'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update primary image.'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Error setting primary image: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the primary image.'
            ], 500);
        }
    }

    /**
     * Reorder images
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'exists:images,id'
        ]);

        try {
            $success = $this->imageService->reorderImages($request->image_ids);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Images reordered successfully.'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder images.'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Error reordering images: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while reordering images.'
            ], 500);
        }
    }

    /**
     * Update image alt text
     */
    public function updateAltText(Request $request, Image $image)
    {
        $request->validate([
            'alt_text' => 'nullable|string|max:255'
        ]);

        try {
            $success = $this->imageService->updateAltText($image, $request->alt_text);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Alt text updated successfully.'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update alt text.'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Error updating alt text: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating alt text.'
            ], 500);
        }
    }

    /**
     * Delete an image
     */
    public function destroy(Image $image)
    {
        try {
            $success = $this->imageService->deleteImage($image);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Image deleted successfully.'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image.'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Error deleting image: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the image.'
            ], 500);
        }
    }

    /**
     * Get images for a model
     */
    public function getImages(Request $request)
    {
        Log::info('getImages called with:', $request->all());
        
        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer'
        ]);

        try {
            // Handle both full class name and just model name
            $modelType = $request->model_type;
            if (strpos($modelType, 'App\\Models\\') === 0) {
                $modelClass = $modelType;
            } else {
                $modelClass = 'App\\Models\\' . $modelType;
            }
            
            Log::info('Model class:', ['modelClass' => $modelClass]);
            
            if (!class_exists($modelClass)) {
                Log::error('Model class does not exist:', ['modelClass' => $modelClass]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid model type.'
                ], 400);
            }

            $model = $modelClass::find($request->model_id);
            
            if (!$model) {
                Log::error('Model not found:', ['modelClass' => $modelClass, 'modelId' => $request->model_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Model not found.'
                ], 404);
            }

            $images = $model->images()->with('imageable')->get();
            
            Log::info('Found images:', ['count' => $images->count(), 'images' => $images->toArray()]);
            
            return response()->json([
                'success' => true,
                'images' => $images
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting images: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while getting images.'
            ], 500);
        }
    }
}
