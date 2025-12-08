<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Package;
use App\Models\PromoCode;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class PromoCodeController extends Controller
{
    public function index()
    {
        $promoCodes = PromoCode::with(['package', 'vehicleType'])->orderBy('created_at', 'desc')->get();
        $packages = Package::where('is_active', true)->orderBy('name')->get();
        $vehicleTypes = VehicleType::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.promo-code.index', compact('promoCodes', 'packages', 'vehicleTypes'));
    }

    public function create()
    {
        $packages = Package::where('is_active', true)->orderBy('name')->get();
        $vehicleTypes = VehicleType::where('is_active', true)->orderBy('name')->get();
        return view('admin.promo-code.create', compact('packages', 'vehicleTypes'));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Debug: Log all incoming data
            \Log::info('Promo code store request data:', $request->all());
            
            // Validate the request
            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:promo_codes',
                'applies_to' => 'required|in:all,package,vehicle_type',
                'package_id' => 'nullable|required_if:applies_to,package|exists:packages,id',
                'vehicle_type_id' => 'nullable|required_if:applies_to,vehicle_type|exists:vehicle_types,id',
                'discount_type' => 'required|in:percentage,fixed',
                'discount_value' => 'required|numeric|min:0',
                'max_discount' => 'nullable|numeric|min:0',
                'min_spend' => 'nullable|numeric|min:0',
                'usage_limit_total' => 'nullable|integer|min:1',
                'usage_limit_per_user' => 'required|integer|min:1',
                'starts_at' => 'nullable|date',
                'ends_at' => 'nullable|date|after:starts_at',
                'status' => 'required|in:active,inactive,expired',
                'remarks' => 'nullable|string',
            ]);

            // Debug: Log validated data
            \Log::info('Promo code validated data:', $validated);

            // Clear package_id and vehicle_type_id if applies_to is 'all'
            if ($validated['applies_to'] === 'all') {
                $validated['package_id'] = null;
                $validated['vehicle_type_id'] = null;
            } elseif ($validated['applies_to'] === 'package') {
                $validated['vehicle_type_id'] = null;
            } elseif ($validated['applies_to'] === 'vehicle_type') {
                $validated['package_id'] = null;
            }

            // Create the promo code
            $promoCode = PromoCode::create($validated);
            
            \Log::info('Promo code created successfully:', ['id' => $promoCode->id]);

            return response()->json([
                'success' => true,
                'message' => 'Promo code created successfully.',
                'promo_code' => $promoCode
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Promo code validation failed:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Promo code creation error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Error creating promo code: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(PromoCode $promoCode): JsonResponse
    {
        try {
            $promoCode->load(['package', 'vehicleType']);
            
            return response()->json([
                'success' => true,
                'promo' => $promoCode
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading promo code: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, PromoCode $promoCode): JsonResponse
    {
        try {
            // Debug: Log all incoming data
            \Log::info('Promo code update request data:', $request->all());
            
            // Validate the request
            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:promo_codes,code,' . $promoCode->id,
                'applies_to' => 'required|in:all,package,vehicle_type',
                'package_id' => 'nullable|required_if:applies_to,package|exists:packages,id',
                'vehicle_type_id' => 'nullable|required_if:applies_to,vehicle_type|exists:vehicle_types,id',
                'discount_type' => 'required|in:percentage,fixed',
                'discount_value' => 'required|numeric|min:0',
                'max_discount' => 'nullable|numeric|min:0',
                'min_spend' => 'nullable|numeric|min:0',
                'usage_limit_total' => 'nullable|integer|min:1',
                'usage_limit_per_user' => 'required|integer|min:1',
                'starts_at' => 'nullable|date',
                'ends_at' => 'nullable|date|after:starts_at',
                'status' => 'required|in:active,inactive,expired',
                'remarks' => 'nullable|string',
            ]);

            // Debug: Log validated data
            \Log::info('Promo code update validated data:', $validated);

            // Clear package_id and vehicle_type_id if applies_to is 'all'
            if ($validated['applies_to'] === 'all') {
                $validated['package_id'] = null;
                $validated['vehicle_type_id'] = null;
            } elseif ($validated['applies_to'] === 'package') {
                $validated['vehicle_type_id'] = null;
            } elseif ($validated['applies_to'] === 'vehicle_type') {
                $validated['package_id'] = null;
            }

            // Update the promo code
            $promoCode->update($validated);
            
            \Log::info('Promo code updated successfully:', ['id' => $promoCode->id]);

            return response()->json([
                'success' => true,
                'message' => 'Promo code updated successfully.',
                'promo_code' => $promoCode
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Promo code update validation failed:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Promo code update error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Error updating promo code: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(PromoCode $promoCode): JsonResponse
    {
        try {
            $promoCode->delete();

            return response()->json([
                'success' => true,
                'message' => 'Promo code deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting promo code: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus(PromoCode $promoCode): JsonResponse
    {
        try {
            $newStatus = $promoCode->status === 'active' ? 'inactive' : 'active';
            $promoCode->update(['status' => $newStatus]);

            return response()->json([
                'success' => true,
                'message' => 'Promo code status updated successfully.',
                'new_status' => $newStatus
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error toggling promo code status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPromoCodes(Request $request): JsonResponse
    {
        try {
            $query = PromoCode::with(['package', 'vehicleType']);

            // Apply filters
            if ($request->filled('package_id')) {
                $query->where('package_id', $request->package_id);
            }
            if ($request->filled('vehicle_type_id')) {
                $query->where('vehicle_type_id', $request->vehicle_type_id);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $promoCodes = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'promoCodes' => $promoCodes
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching promo codes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function validateCode(Request $request): JsonResponse
    {
        try {
            $code = $request->input('code');
            $excludeId = $request->input('exclude_id');

            $query = PromoCode::where('code', $code);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            $exists = $query->exists();

            return response()->json([
                'success' => true,
                'available' => !$exists,
                'message' => $exists ? 'Promo code already exists' : 'Promo code is available'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error validating promo code: ' . $e->getMessage()
            ], 500);
        }
    }
}
