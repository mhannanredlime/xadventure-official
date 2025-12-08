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
use Devrabiul\ToastMagic\Facades\ToastMagic;
use App\Services\PromoCodeService;

class PromoCodeController extends Controller
{
    protected $promoCodeService;

    public function __construct(PromoCodeService $promoCodeService)
    {
        $this->promoCodeService = $promoCodeService;
    }

    public function index()
    {
        $promoCodes = $this->promoCodeService->getFilteredPromoCodes();
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
            $this->promoCodeService->createPromoCode($request->validated());
            
            ToastMagic::success('Promo code created successfully!');
            return redirect()->route('admin.promo-codes.index');
        } catch (\Exception $e) {
            \Log::error('Promo code creation error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            ToastMagic::error('Error creating promo code: ' . $e->getMessage());
            return redirect()->back()->withInput();
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
            $this->promoCodeService->updatePromoCode($promoCode, $request->validated());
            
            ToastMagic::success('Promo code updated successfully!');
            return redirect()->route('admin.promo-codes.index');
        } catch (\Exception $e) {
            \Log::error('Promo code update error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            ToastMagic::error('Error updating promo code: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(PromoCode $promoCode)
    {
        try {
            $this->promoCodeService->deletePromoCode($promoCode);

            ToastMagic::success('Promo code deleted successfully!');
            return redirect()->route('admin.promo-codes.index');
        } catch (\Exception $e) {
            ToastMagic::error('Error deleting promo code: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function toggleStatus(PromoCode $promoCode): JsonResponse
    {
        try {
            $newStatus = $this->promoCodeService->toggleStatus($promoCode);

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
            $promoCodes = $this->promoCodeService->getFilteredPromoCodes($request->all());

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
            
            $available = $this->promoCodeService->checkCodeAvailability($code, $excludeId);

            return response()->json([
                'success' => true,
                'available' => $available,
                'message' => $available ? 'Promo code is available' : 'Promo code already exists'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error validating promo code: ' . $e->getMessage()
            ], 500);
        }
    }
}
