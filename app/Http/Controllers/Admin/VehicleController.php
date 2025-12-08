<?php

namespace App\Http\Controllers\Admin;

use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use App\Services\ImageService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\VehicleStoreRequest;
use App\Http\Requests\VehicleUpdateRequest;

class VehicleController extends Controller
{
    public function index()
    {
        $data['vehicles'] = Vehicle::with(['vehicleType.images', 'images'])->orderBy('name')->get();
        
        $data['vehicleTypes'] = \Illuminate\Support\Facades\Cache::remember('active_vehicle_types', 3600, function () {
            return VehicleType::with('images')->where('is_active', true)->orderBy('name')->get();
        });
        
        return view('admin.vehicles.index', $data);
    }

    public function create()
    {
        $vehicleTypes = \Illuminate\Support\Facades\Cache::remember('active_vehicle_types', 3600, function () {
            return VehicleType::with('images')->where('is_active', true)->orderBy('name')->get();
        });
        
        return view('admin.vehicles.create', compact('vehicleTypes'));
    }

    public function store(VehicleStoreRequest $request)
    {
        // Debug: Log the incoming request data
        Log::info('Store Request Data:', $request->all());
        
        $validated = $request->validated();

        try {
            // Create the vehicle
            $vehicle = Vehicle::create($validated);
            $vehicle->load('vehicleType');

            // Handle multiple image uploads with extended format support
            if ($request->hasFile('images')) {
                // Validation already handled in Request
                
                $imageService = new ImageService();
                $imageService->uploadMultipleImages($vehicle, $request->file('images'), 'vehicles');
            }

            return redirect()->route('admin.vehicles.index')
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

    public function update(VehicleUpdateRequest $request, Vehicle $vehicle)
    {
        // Handle both PUT and POST requests
        if ($request->method() === 'POST' && $request->has('_method') && $request->input('_method') === 'PUT') {
            \Log::info('PUT request via method spoofing detected');
        }
    
        $validated = $request->validated();

        try {
            // Using validated data from Request which includes basic cleaning
            // But we might need to manually handle 'is_active' if simple update doesn't catch it from prepareForValidation merge?
            // Actually, $request->validated() returns the merged data if defined in prepareForValidation.
            
            // Debug: Log the date value
            \Log::info('Update - Date value being saved:', ['op_start_date' => $validated['op_start_date'] ?? null]);
            
            // Handle multiple image uploads with extended format support
            if ($request->hasFile('images')) {
                 // Validation handled in Request
                
                $imageService = new ImageService();
                $imageService->uploadMultipleImages($vehicle, $request->file('images'), 'vehicles');
            }

            // Update the vehicle
            $vehicle->update($validated);
            $vehicle->load('vehicleType');

            return redirect()->route('admin.vehicles.index')
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
