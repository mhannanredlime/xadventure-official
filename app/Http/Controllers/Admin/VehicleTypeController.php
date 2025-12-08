<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\VehicleTypeStoreRequest;
use App\Http\Requests\VehicleTypeUpdateRequest;

class VehicleTypeController extends Controller
{
    public function index()
    {
        $sort = request()->get('sort', 'newest');
        
        $vehicleTypes = match($sort) {
            'oldest' => VehicleType::with('images')->orderBy('created_at', 'asc')->get(),
            'name' => VehicleType::with('images')->orderBy('name', 'asc')->get(),
            'name_desc' => VehicleType::with('images')->orderBy('name', 'desc')->get(),
            default => VehicleType::with('images')->orderBy('created_at', 'desc')->get(), // newest first
        };
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'vehicleTypes' => $vehicleTypes
            ]);
        }
        
        return view('admin.vehicles.vehicle-setup', compact('vehicleTypes', 'sort'));
    }

    public function create()
    {
        return view('admin.vehicle-types.create');
    }

    public function store(VehicleTypeStoreRequest $request)
    {
        $validated = $request->validated();
        
        // Add image validation - support all common image formats including WebP
        // This is now redundant if handled in Request but keeping for double safety or remove? Request already handles it.
        // Removing manual validation block.

        $vehicleType = VehicleType::create($validated);

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            $imageService = new ImageService();
            $imageService->uploadMultipleImages($vehicleType, $request->file('images'), 'vehicle-types');
        }

        // Handle gallery images
        if ($request->has('gallery_image_ids')) {
            $galleryService = new \App\Services\GalleryService();
            $galleryIds = explode(',', $request->gallery_image_ids);
            
            foreach ($galleryIds as $galleryId) {
                $galleryImage = \App\Models\Gallery::find($galleryId);
                if ($galleryImage) {
                    try {
                        $imageData = $galleryService->copyImageToLocation($galleryImage, 'vehicle-types');
                        
                        // Create image record for the vehicle type
                        $vehicleType->images()->create([
                            'image_path' => $imageData['image_path'],
                            'original_name' => $imageData['original_name'],
                            'mime_type' => $imageData['mime_type'],
                            'file_size' => $imageData['file_size'],
                            'sort_order' => $vehicleType->images()->count(),
                            'is_primary' => $vehicleType->images()->count() === 0,
                            'alt_text' => $imageData['alt_text'],
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to copy gallery image: ' . $e->getMessage());
                    }
                }
            }
        }

        return redirect()->route('admin.vehicle-types.index')
            ->with('success', 'Vehicle type created successfully.');
    }

    public function edit(VehicleType $vehicleType)
    {
        // Eager load images for the edit view
        $vehicleType->load('images');
        
        return view('admin.vehicle-types.edit', compact('vehicleType'));
    }

    public function update(VehicleTypeUpdateRequest $request, VehicleType $vehicleType)
    {
        // Debug: Log the incoming request data
        Log::info('Update Request Data:', $request->all());
        Log::info('Files:', $request->allFiles());
        
        try {
            $validated = $request->validated();

            Log::info('Validation passed, updating vehicle type');
            $vehicleType->update($validated);

            // Handle multiple image uploads
            if ($request->hasFile('images')) {
                Log::info('Processing image uploads');
                $imageService = new ImageService();
                $imageService->uploadMultipleImages($vehicleType, $request->file('images'), 'vehicle-types');
                Log::info('Image uploads completed');
            } else {
                Log::info('No images to upload');
            }

            // Handle gallery images
            if ($request->has('gallery_image_ids')) {
                Log::info('Processing gallery images');
                $galleryService = new \App\Services\GalleryService();
                $galleryIds = explode(',', $request->gallery_image_ids);
                
                foreach ($galleryIds as $galleryId) {
                    $galleryImage = \App\Models\Gallery::find($galleryId);
                    if ($galleryImage) {
                        try {
                            $imageData = $galleryService->copyImageToLocation($galleryImage, 'vehicle-types');
                            
                            // Create image record for the vehicle type
                            $vehicleType->images()->create([
                                'image_path' => $imageData['image_path'],
                                'original_name' => $imageData['original_name'],
                                'mime_type' => $imageData['mime_type'],
                                'file_size' => $imageData['file_size'],
                                'sort_order' => $vehicleType->images()->count(),
                                'is_primary' => $vehicleType->images()->count() === 0,
                                'alt_text' => $imageData['alt_text'],
                            ]);
                            Log::info('Gallery image copied successfully: ' . $galleryId);
                        } catch (\Exception $e) {
                            Log::error('Failed to copy gallery image: ' . $e->getMessage());
                        }
                    }
                }
                Log::info('Gallery images processing completed');
            }

            Log::info('Vehicle type updated successfully');
            return redirect()->route('admin.vehicle-types.index')
                ->with('success', 'Vehicle type updated successfully.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', $e->errors());
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating vehicle type: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while updating the vehicle type: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(VehicleType $vehicleType)
    {
        // Delete image if exists
        if ($vehicleType->image_path) {
            Storage::disk('public')->delete($vehicleType->image_path);
        }

        $vehicleType->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Vehicle type deleted successfully.'
            ]);
        }

        return redirect()->route('admin.vehicle-types.index')
            ->with('success', 'Vehicle type deleted successfully.');
    }
}
