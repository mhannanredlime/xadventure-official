<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with(['vehicleType.images', 'images'])->orderBy('name')->get();
        $vehicleTypes = VehicleType::with('images')->where('is_active', true)->orderBy('name')->get();
        
        return view('admin.vehical-management', compact('vehicles', 'vehicleTypes'));
    }

    public function create()
    {
        $vehicleTypes = VehicleType::with('images')->where('is_active', true)->orderBy('name')->get();
        
        return view('admin.vehicles.create', compact('vehicleTypes'));
    }

    public function store(Request $request)
    {
        // Debug: Log the incoming request data
        Log::info('Store Request Data:', $request->all());
        
        // Use Vehicle model validation rules
        $validator = Validator::make($request->all(), Vehicle::getValidationRules(), Vehicle::getValidationMessages());

        if ($validator->fails()) {
            Log::info('Validation failed:', $validator->errors()->toArray());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $data = [
                'vehicle_type_id' => $request->input('vehicle_type_id'),
                'name' => $request->input('name'),
                'details' => $request->input('details'),
                'is_active' => $request->has('is_active') || $request->input('is_active') === 'on',
                'op_start_date' => $request->input('op_start_date') ? $request->input('op_start_date') : null,
            ];
            
            // Debug: Log the date value
            Log::info('Store - Date value being saved:', ['op_start_date' => $data['op_start_date']]);

            // Create the vehicle
            $vehicle = Vehicle::create($data);
            $vehicle->load('vehicleType');

            // Handle multiple image uploads with extended format support
            if ($request->hasFile('images')) {
                // Validate image formats
                $request->validate([
                    'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,bmp,svg|max:5120', // 5MB max, support WebP and more formats
                ]);
                
                $imageService = new ImageService();
                $imageService->uploadMultipleImages($vehicle, $request->file('images'), 'vehicles');
            }

            return redirect()->route('admin.vehical-management')
                ->with('success', 'Vehicle created successfully.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating the vehicle.'
                ], 500);
            }

            return back()->with('error', 'An error occurred while creating the vehicle.')->withInput();
        }
    }

    public function edit(Vehicle $vehicle)
    {
        // Eager load images for the edit view
        $vehicle->load(['vehicleType.images', 'images']);
        $vehicleTypes = VehicleType::with('images')->where('is_active', true)->orderBy('name')->get();
        
        return view('admin.vehicles.edit', compact('vehicle', 'vehicleTypes'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        // Handle both PUT and POST requests
        if ($request->method() === 'POST' && $request->has('_method') && $request->input('_method') === 'PUT') {
            // This is a PUT request via method spoofing
            \Log::info('PUT request via method spoofing detected');
        }
        
        // Simple test to see if method is called
        \Log::info('Update method called successfully');
        
        // Debug: Log the incoming request data
        \Log::info('Update Request Data:', $request->all());
        \Log::info('Update Request data keys:', array_keys($request->all()));
        \Log::info('Update Request Method:', ['method' => $request->method()]);
        \Log::info('Update Request Content Type:', ['content_type' => $request->header('Content-Type')]);
        \Log::info('Update Request Raw Input:', ['raw' => $request->getContent()]);
        \Log::info('Update Request POST data:', ['post' => $request->post()]);
        \Log::info('Update Request Input data:', ['input' => $request->input()]);
        
        // Use Vehicle model validation rules
        $validator = Validator::make($request->all(), Vehicle::getValidationRules($vehicle->id), Vehicle::getValidationMessages());

        if ($validator->fails()) {
            \Log::info('Validation failed:', $validator->errors()->toArray());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $data = [
                'vehicle_type_id' => $request->input('vehicle_type_id'),
                'name' => $request->input('name'),
                'details' => $request->input('details'),
                'is_active' => $request->has('is_active') || $request->input('is_active') === 'on',
                'op_start_date' => $request->input('op_start_date') ? $request->input('op_start_date') : null,
            ];
            
            // Debug: Log the date value
            \Log::info('Update - Date value being saved:', ['op_start_date' => $data['op_start_date']]);
            
            // Handle multiple image uploads with extended format support
            if ($request->hasFile('images')) {
                // Validate image formats
                $request->validate([
                    'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,bmp,svg|max:5120', // 5MB max, support WebP and more formats
                ]);
                
                $imageService = new ImageService();
                $imageService->uploadMultipleImages($vehicle, $request->file('images'), 'vehicles');
            }

            // Update the vehicle
            $vehicle->update($data);
            $vehicle->load('vehicleType');

            return redirect()->route('admin.vehical-management')
                ->with('success', 'Vehicle updated successfully.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the vehicle.'
                ], 500);
            }

            return back()->with('error', 'An error occurred while updating the vehicle.')->withInput();
        }
    }

    public function destroy(Vehicle $vehicle)
    {
        // Delete associated image
        if ($vehicle->image_path) {
            Storage::disk('public')->delete($vehicle->image_path);
        }

        $vehicle->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Vehicle deleted successfully.'
            ]);
        }

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle deleted successfully.');
    }

    public function toggleStatus(Vehicle $vehicle)
    {
        $vehicle->update(['is_active' => !$vehicle->is_active]);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Vehicle status updated successfully.',
                'is_active' => $vehicle->is_active
            ]);
        }

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle status updated successfully.');
    }
}
