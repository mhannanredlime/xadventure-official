<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\Package;
use App\Models\PackageVariant;
use App\Models\ScheduleSlot;
use App\Models\SlotPreset;
use App\Models\SlotPresetOverride;
use App\Models\VehicleType;
use App\Services\PriceCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    protected $priceService;

    public function __construct(PriceCalculationService $priceService)
    {
        $this->priceService = $priceService;
    }

    public function atvUtvLandingPage(Request $request)
    {
        // Get ATV and UTV packages specifically
        $atvPackages = Package::with(['packagePrices.riderType', 'vehicleTypes', 'images'])
            ->where('is_active', true)
            ->where('name', 'like', '%ATV%')
            ->orderBy('created_at', 'desc')
            ->get();

        $utvPackages = Package::with(['packagePrices.riderType', 'vehicleTypes', 'images'])
            ->where('is_active', true)
            ->where('name', 'like', '%UTV%')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get all packages for the booking functionality
        $allPackages = Package::with(['packagePrices.riderType', 'vehicleTypes', 'images'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get schedule slots for time selection
        $scheduleSlots = ScheduleSlot::where('is_active', true)
            ->orderBy('start_time')
            ->get();

        Log::info('ATV UTV Page');

        return view('frontend.adventure-details', compact(
            'atvPackages',
            'utvPackages',
            'allPackages',
            'scheduleSlots'
        ));
    }

    public function atvUtvPackBookings()
    {
        // Get all active ATV/UTV packages
        $packages = Package::with(['vehicleTypes', 'images', 'packagePrices'])
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('name', 'like', '%ATV%')
                      ->orWhere('name', 'like', '%UTV%');
            })
            ->orderByRaw("CASE WHEN name LIKE '%ATV%' THEN 1 WHEN name LIKE '%UTV%' THEN 2 ELSE 3 END")
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($package) {
                // Check if this is ATV/UTV package
                $isATVUTV = str_contains($package->name, 'ATV') || str_contains($package->name, 'UTV');

                // Current day type
                $dayName = now()->isWeekend() ? 'weekend' : 'weekday';

                // Get prices using your helper
                $singlePrice = get_package_price($package, $dayName, 1);
                $doublePrice = $isATVUTV ? get_package_price($package, $dayName, 2) : 0;

                // Display starting price
                $displayPrice = $package->display_starting_price ?? 0;

                // Add computed fields
                $package->is_atv_utv = $isATVUTV;
                $package->single_price = $singlePrice;
                $package->double_price = $doublePrice;
                $package->display_price = $displayPrice;
                $package->effective_price = $displayPrice > 0 ? $displayPrice : ($isATVUTV ? min($singlePrice, $doublePrice) : $singlePrice);
                $package->day_name = $dayName;

                return $package;
            });

        // Active schedule slots
        $scheduleSlots = ScheduleSlot::where('is_active', true)
            ->orderBy('start_time')
            ->get();

        // Prepare JSON for JS
        $packageDataJson = $packages->map(function ($package) {
            return [
                'id' => $package->id,
                'name' => $package->name,
                'is_atv_utv' => $package->is_atv_utv,
                'display_starting_price' => $package->display_price,
                'price_data' => [
                    'single' => $package->single_price,
                    'double' => $package->double_price,
                    'display' => $package->display_price,
                    'day' => $package->day_name,
                ],
            ];
        });

        return view('frontend.packages.atv-utv-packages', [
            'packages' => $packages,
            'scheduleSlots' => $scheduleSlots,
            'package_data_json' => $packageDataJson,
        ]);
    }

    /**
     * Calculate available vehicles for a package
     */
    private function calculateAvailableVehicles($package): int
    {
        $totalAvailableVehicles = 0;
        $today = \Carbon\Carbon::today()->format('Y-m-d');

        foreach ($package->vehicleTypes as $vehicleType) {
            $totalAvailableVehicles += \App\Models\Vehicle::where('vehicle_type_id', $vehicleType->id)
                ->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('op_start_date')->orWhere('op_start_date', '<=', $today))
                ->count();
        }

        return $totalAvailableVehicles;
    }

    /**
     * Calculate effective price for display
     */
    private function calculateEffectivePrice($displayPrice, $singlePrice, $doublePrice, $isATVUTV): float
    {
        if ($displayPrice > 0) {
            return $displayPrice;
        }

        if ($isATVUTV) {
            return min($singlePrice, $doublePrice);
        }

        return $singlePrice;
    }

    /**
     * Prepare package data JSON for frontend
     */
    private function preparePackageDataJson($packages)
    {
        return $packages->map(function ($package) {
            return [
                'id' => $package->id,
                'name' => $package->name,
                'description' => $package->description,
                'requirements' => $package->requirements,
                'display_image_url' => $package->display_image_url,
                'is_atv_utv' => $package->computed['is_atv_utv'],
                'display_starting_price' => $package->computed['display_price'],
                'vehicle_count' => $package->computed['total_available_vehicles'],
                'price_data' => [
                    'single' => $package->computed['single_price'],
                    'double' => $package->computed['double_price'],
                    'display' => $package->computed['display_price'],
                    'day' => $package->computed['day_name'],
                ],
            ];
        });
    }

    public function show(Package $package)
    {
        $package->load(['packagePrices.riderType', 'vehicleTypes', 'images']);

        // Get schedule slots for time selection
        $scheduleSlots = ScheduleSlot::where('is_active', true)
            ->orderBy('start_time')
            ->get();

        // Availability Data strictly based on Package (not Variant)
        // We can skip detailed availability grid per variant as it was
        // or refactor it. For now, sending basic data.
        $availabilityData = []; // $this->getAvailabilityDataForPackage($package); 

        return view('frontend.packages.show', compact('package', 'scheduleSlots', 'availabilityData'));
    }

    public function getVariants(Request $request)
    {
        $packageId = $request->get('package_id');
        $date = $request->get('date');

        $variants = PackageVariant::with(['prices', 'availabilities' => function ($query) use ($date) {
            $query->where('date', $date);
        }])
            ->where('package_id', $packageId)
            ->where('is_active', true)
            ->get();

        return response()->json($variants);
    }

    public function getAvailability(Request $request)
    {
        $variantId = $request->get('variant_id');
        $date = $request->get('date');
        $slotId = $request->get('slot_id');

        $availability = Availability::where([
            'package_variant_id' => $variantId,
            'date' => $date,
            'schedule_slot_id' => $slotId,
        ])->first();

        return response()->json($availability);
    }

    /**
     * Public endpoint: Get all active schedule slots with availability for a variant and date
     */
    /**
     * Public endpoint: Get all active schedule slots with availability for a package and date
     */
    public function getSlotsAvailability(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'date' => 'required|date',
            'rider_type_id' => 'nullable|integer'
        ]);

        $package = Package::find($request->get('package_id'));
        $date = $request->get('date');
        $riderTypeId = $request->get('rider_type_id');

        // Determine which time slots to show using presets (default or per-day override)
        $presetOverride = SlotPresetOverride::where([
            'package_id' => $package->id,
            'date' => $date,
        ])->first();

        if ($presetOverride) {
            $slots = $presetOverride->preset->scheduleSlots;
        } else {
            $defaultPreset = SlotPreset::where(['is_default' => true, 'is_active' => true])->first();
            if ($defaultPreset) {
                $slots = $defaultPreset->scheduleSlots;
            } else {
                $slots = ScheduleSlot::where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('start_time')
                    ->get();
            }
        }

        $result = [];
        foreach ($slots as $slot) {
            // Check availability for this slot
            $pricing = $this->priceService->getPricingAndAvailabilityForDate($package, $date, $slot->id, $riderTypeId);
            
            // Should be open if available capacity > 0 OR if it's a regular package with no capacity constraints?
            // Existing logic checked if "regular" type and defaulted to open.
            // New logic: Check available_capacity. Regular packages likely have high capacity seeded.
            // Or we check Package Type.
            
            $packageType = $package->type ?? 'regular';
            $isRegularPackage = $packageType === 'regular';
            
            // For UI display
            $result[] = [
                'id' => $slot->id,
                'name' => $slot->name,
                'label' => (string) (\Carbon\Carbon::parse($slot->start_time)->format('g A').' - '.\Carbon\Carbon::parse($slot->end_time)->format('g A')),
                'is_open' => $pricing['is_available'],
                'available_total' => $pricing['total_available'], 
                'available_capacity' => $pricing['available_capacity'], 
                'total_booked' => $pricing['total_booked'],
                'final_price' => $pricing['final_price'],
                'price_tag' => $pricing['price_tag'],
            ];
        }

        return response()->json($result);
    }

    public function getPricingForDate(Request $request)
    {
        $date = $request->get('date');
        $packageId = $request->get('package_id');
        $riderTypeId = $request->get('rider_type_id');

        $package = Package::find($packageId);

        if (! $package) {
            return response()->json(['error' => 'Package not found'], 404);
        }

        // Get comprehensive pricing and availability data using the new integrated service
        $pricingData = $this->priceService->getPricingAndAvailabilityForDate($package, $date, null, $riderTypeId);

        // Check if it's weekend
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $isWeekend = ($dayOfWeek == 0 || $dayOfWeek == 6);

        return response()->json(array_merge($pricingData, [
            'package_id' => $package->id,
            'is_weekend' => $isWeekend,
        ]));
    }

    public function checkAvailability(Request $request)
    {
        $date = $request->get('date');
        $packageId = $request->get('package_id');
        $riderTypeId = $request->get('rider_type_id');
        $slotId = $request->get('slot_id');
        $quantity = $request->get('quantity', 1);

        $package = Package::find($packageId);
        if (! $package) {
            return response()->json([
                'available' => false,
                'message' => 'Package not found',
            ]);
        }

        // Get comprehensive pricing and availability data using the new integrated service
        $pricingData = $this->priceService->getPricingAndAvailabilityForDate($package, $date, $slotId, $riderTypeId);

        $isAvailable = $pricingData['available_capacity'] >= $quantity;

        return response()->json([
            'available' => $isAvailable,
            'available_quantity' => $pricingData['available_capacity'],
            'requested_quantity' => $quantity,
            'final_price' => $pricingData['final_price'],
            'default_price' => $pricingData['default_price'],
            'price_tag' => $pricingData['price_tag'],
            'has_discount' => $pricingData['has_discount'],
            'discount_amount' => $pricingData['discount_amount'],
            'discount_percentage' => $pricingData['discount_percentage'],
            'total_cost' => $pricingData['final_price'] * $quantity,
            'vehicle_types' => $pricingData['vehicle_types'],
            'total_booked' => $pricingData['total_booked'],
        ]);
    }
}
