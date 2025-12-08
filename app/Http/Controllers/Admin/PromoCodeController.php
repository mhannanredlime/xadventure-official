<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Package;
use App\Models\PromoCode;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromoCodeStoreUpdateRequest;

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

    public function store(StorePromoCodeStoreUpdateRequest $request)
    {
        try {
            $validated = $request->validated();

            // Clear package_id and vehicle_type_id based on applies_to
            if ($validated['applies_to'] === 'all') {
                $validated['package_id'] = null;
                $validated['vehicle_type_id'] = null;
            } elseif ($validated['applies_to'] === 'package') {
                $validated['vehicle_type_id'] = null;
            } elseif ($validated['applies_to'] === 'vehicle_type') {
                $validated['package_id'] = null;
            }

            PromoCode::create($validated);
            
            return redirect()->route('admin.promo-codes.index')
                ->with('success', 'Promo code created successfully.');
        } catch (\Exception $e) {
            \Log::error('Promo code creation error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating promo code: ' . $e->getMessage());
        }
    }

    public function edit(PromoCode $promoCode)
    {
        $packages = Package::where('is_active', true)->orderBy('name')->get();
        $vehicleTypes = VehicleType::where('is_active', true)->orderBy('name')->get();
        return view('admin.promo-code.edit', compact('promoCode', 'packages', 'vehicleTypes'));
    }

    public function update(StorePromoCodeStoreUpdateRequest $request, PromoCode $promoCode)
    {
        try {
            $validated = $request->validated();

            // Clear package_id and vehicle_type_id based on applies_to
            if ($validated['applies_to'] === 'all') {
                $validated['package_id'] = null;
                $validated['vehicle_type_id'] = null;
            } elseif ($validated['applies_to'] === 'package') {
                $validated['vehicle_type_id'] = null;
            } elseif ($validated['applies_to'] === 'vehicle_type') {
                $validated['package_id'] = null;
            }

            $promoCode->update($validated);
            
            return redirect()->route('admin.promo-codes.index')
                ->with('success', 'Promo code updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Promo code update error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating promo code: ' . $e->getMessage());
        }
    }

    public function destroy(PromoCode $promoCode)
    {
        try {
            $promoCode->delete();

            return redirect()->route('admin.promo-codes.index')
                ->with('success', 'Promo code deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting promo code: ' . $e->getMessage());
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
