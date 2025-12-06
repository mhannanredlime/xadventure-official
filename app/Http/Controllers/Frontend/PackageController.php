<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageVariant;
use App\Models\VehicleType;
use App\Models\ScheduleSlot;
use App\Models\SlotPreset;
use App\Models\SlotPresetOverride;
use App\Models\Availability;
use App\Models\PriceOverride;
use App\Services\PriceCalculationService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    protected $priceService;

    public function __construct(PriceCalculationService $priceService)
    {
        $this->priceService = $priceService;
    }

    public function atvUtvPage(Request $request)
    {
        // Get ATV and UTV packages specifically
        $atvPackages = Package::with(['variants.prices', 'vehicleTypes', 'images'])
            ->where('is_active', true)
            ->where('name', 'like', '%ATV%')
            ->orderBy('created_at', 'desc')
            ->get();

        $utvPackages = Package::with(['variants.prices', 'vehicleTypes', 'images'])
            ->where('is_active', true)
            ->where('name', 'like', '%UTV%')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get all packages for the booking functionality
        $allPackages = Package::with(['variants.prices', 'vehicleTypes', 'images'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get schedule slots for time selection
        $scheduleSlots = ScheduleSlot::where('is_active', true)
            ->orderBy('start_time')
            ->get();

        // Get all package variants with their relationships
        $packageVariants = PackageVariant::with(['package', 'prices', 'availabilities.scheduleSlot'])
            ->where('is_active', true)
            ->get();

        // Group variants by package for easier access in the view
        $variantsByPackage = [];
        foreach ($allPackages as $package) {
            $variantsByPackage[$package->id] = $packageVariants->filter(function($variant) use ($package) {
                return $variant->package_id == $package->id;
            });
        }

        return view('frontend.adventure-details', compact(
            'atvPackages', 
            'utvPackages',
            'allPackages',
            'scheduleSlots',
            'packageVariants',
            'variantsByPackage'
        ));
    }

    public function index()
    {
        // Get only ATV/UTV packages with their variants, prices, and vehicle types
        $packages = Package::with(['variants.prices', 'vehicleTypes', 'images'])
            ->where('is_active', true)
            ->where(function($query) {
                $query->where('name', 'like', '%ATV%')
                      ->orWhere('name', 'like', '%UTV%');
            })
            ->orderByRaw("CASE WHEN name LIKE '%ATV%' THEN 1 WHEN name LIKE '%UTV%' THEN 2 ELSE 3 END")
            ->orderBy('created_at', 'desc')
            ->get();

        // Get schedule slots for time selection
        $scheduleSlots = ScheduleSlot::where('is_active', true)
            ->orderBy('start_time')
            ->get();

        // Get availability data for the next 30 days
        $availabilityData = $this->getAvailabilityData();

        // Get only ATV/UTV package variants with their relationships
        $packageVariants = PackageVariant::with(['package', 'prices', 'availabilities.scheduleSlot'])
            ->where('is_active', true)
            ->whereHas('package', function($query) {
                $query->where('is_active', true)
                      ->where(function($q) {
                          $q->where('name', 'like', '%ATV%')
                            ->orWhere('name', 'like', '%UTV%');
                      });
            })
            ->get();

        // Group variants by package for easier access in the view
        $variantsByPackage = [];
        foreach ($packages as $package) {
            $variantsByPackage[$package->id] = $packageVariants->filter(function($variant) use ($package) {
                return $variant->package_id == $package->id;
            });
        }

        // Calculate availability-based pricing for each variant using the new integrated service
        $pricingWithAvailability = [];
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);

        foreach ($packageVariants as $variant) {
            $pricingWithAvailability[$variant->id] = [];
            
            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                $dateString = $date->format('Y-m-d');
                
                // Get comprehensive pricing and availability data (including cart items)
                $pricingData = $this->priceService->getPricingAndAvailabilityForDate($variant, $dateString);
                
                $pricingWithAvailability[$variant->id][$dateString] = array_merge($pricingData, [
                    'variant_id' => $variant->id,
                    'is_day_off' => $pricingData['is_day_off'] ?? false
                ]);
            }
        }

        return view('frontend.packages.index', compact(
            'packages', 
            'scheduleSlots', 
            'availabilityData',
            'packageVariants',
            'variantsByPackage',
            'pricingWithAvailability'
        ));
    }

    public function show(Package $package)
    {
        $package->load(['variants.prices', 'vehicleTypes', 'images']);
        
        // Get schedule slots for time selection
        $scheduleSlots = ScheduleSlot::where('is_active', true)
            ->orderBy('start_time')
            ->get();

        // Get availability data for this specific package
        $availabilityData = $this->getAvailabilityDataForPackage($package);

        return view('frontend.packages.show', compact('package', 'scheduleSlots', 'availabilityData'));
    }

    public function getVariants(Request $request)
    {
        $packageId = $request->get('package_id');
        $date = $request->get('date');
        
        $variants = PackageVariant::with(['prices', 'availabilities' => function($query) use ($date) {
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
    public function getSlotsAvailability(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:package_variants,id',
            'date' => 'required|date',
        ]);

        $variant = PackageVariant::find($request->get('variant_id'));
        $date = $request->get('date');

        // Determine which time slots to show using presets (default or per-day override)
        $presetOverride = SlotPresetOverride::where([
            'package_variant_id' => $variant->id,
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
            // Check if this is a regular package - regular packages have unlimited availability
            // Load the package relationship if not already loaded
            if (!$variant->relationLoaded('package')) {
                $variant->load('package');
            }
            
            $packageType = $variant->package->type ?? 'regular'; // Default to regular if type is null
            $isRegularPackage = $packageType === 'regular';
            
            if ($isRegularPackage) {
                // For regular packages, return the variant's actual capacity
                $pricing = $this->priceService->getPricingAndAvailabilityForDate($variant, $date, $slot->id);
                $capacity = $variant->capacity ?? 6; // Default to 6 if capacity is not set
                
                $result[] = [
                    'id' => $slot->id,
                    'name' => $slot->name,
                    'label' => (string) (\Carbon\Carbon::parse($slot->start_time)->format('g A') . ' - ' . \Carbon\Carbon::parse($slot->end_time)->format('g A')),
                    'is_open' => true, // Always open for regular packages
                    'available_total' => $capacity, // Use actual capacity
                    'available_capacity' => $capacity, // Use actual capacity
                    'total_booked' => 0,
                    'final_price' => $pricing['final_price'],
                    'price_tag' => $pricing['price_tag'],
                ];
            } else {
                // For ATV/UTV packages, check actual availability
                $pricing = $this->priceService->getPricingAndAvailabilityForDate($variant, $date, $slot->id);

                $result[] = [
                    'id' => $slot->id,
                    'name' => $slot->name,
                    'label' => (string) (\Carbon\Carbon::parse($slot->start_time)->format('g A') . ' - ' . \Carbon\Carbon::parse($slot->end_time)->format('g A')),
                    'is_open' => $pricing['available_capacity'] > 0,
                    'available_total' => $pricing['total_available'], // vehicles left
                    'available_capacity' => $pricing['available_capacity'], // seats/people left
                    'total_booked' => $pricing['total_booked'],
                    'final_price' => $pricing['final_price'],
                    'price_tag' => $pricing['price_tag'],
                ];
            }
        }

        return response()->json($result);
    }

    public function getAvailabilityForDate(Request $request)
    {
        $date = $request->get('date');
        $packageId = $request->get('package_id');

        $availabilities = Availability::with(['packageVariant', 'scheduleSlot'])
            ->where('date', $date)
            ->when($packageId, function($query) use ($packageId) {
                return $query->whereHas('packageVariant', function($q) use ($packageId) {
                    $q->where('package_id', $packageId);
                });
            })
            ->get();

        return response()->json($availabilities);
    }

    public function getPackageDetails(Request $request)
    {
        $packageId = $request->get('package_id');
        
        $package = Package::with(['variants.prices', 'vehicleTypes', 'images'])
            ->where('id', $packageId)
            ->where('is_active', true)
            ->first();

        if (!$package) {
            return response()->json(['error' => 'Package not found'], 404);
        }

        return response()->json($package);
    }

    public function getVehicleTypeDetails(Request $request)
    {
        $vehicleTypeId = $request->get('vehicle_type_id');
        
        $vehicleType = VehicleType::where('id', $vehicleTypeId)
            ->where('is_active', true)
            ->first();

        if (!$vehicleType) {
            return response()->json(['error' => 'Vehicle type not found'], 404);
        }

        return response()->json($vehicleType);
    }

    public function getPricingForDate(Request $request)
    {
        $date = $request->get('date');
        $variantId = $request->get('variant_id');
        
        $variant = PackageVariant::with(['prices'])
            ->where('id', $variantId)
            ->where('is_active', true)
            ->first();

        if (!$variant) {
            return response()->json(['error' => 'Variant not found'], 404);
        }

        // Get comprehensive pricing and availability data using the new integrated service
        $pricingData = $this->priceService->getPricingAndAvailabilityForDate($variant, $date);

        // Check if it's weekend
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $isWeekend = ($dayOfWeek == 0 || $dayOfWeek == 6);

        return response()->json(array_merge($pricingData, [
            'variant' => $variant,
            'variant_id' => $variant->id,
            'is_weekend' => $isWeekend,
        ]));
    }

    public function checkAvailability(Request $request)
    {
        $date = $request->get('date');
        $variantId = $request->get('variant_id');
        $slotId = $request->get('slot_id');
        $quantity = $request->get('quantity', 1);

        $variant = PackageVariant::find($variantId);
        if (!$variant) {
            return response()->json([
                'available' => false,
                'message' => 'Variant not found'
            ]);
        }

        // Get comprehensive pricing and availability data using the new integrated service
        $pricingData = $this->priceService->getPricingAndAvailabilityForDate($variant, $date, $slotId);
        
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
            'total_booked' => $pricingData['total_booked']
        ]);
    }

    private function getAvailabilityData()
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);

        return Availability::with(['packageVariant.package', 'scheduleSlot'])
            ->whereBetween('date', [$startDate, $endDate])
            ->whereHas('packageVariant.package', function($query) {
                $query->where('is_active', true)
                      ->where(function($q) {
                          $q->where('name', 'like', '%ATV%')
                            ->orWhere('name', 'like', '%UTV%');
                      });
            })
            ->get()
            ->groupBy('date')
            ->map(function($dayAvailabilities) {
                return $dayAvailabilities->groupBy('schedule_slot_id');
            });
    }

    private function getAvailabilityDataForPackage(Package $package)
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);

        return Availability::with(['packageVariant', 'scheduleSlot'])
            ->whereBetween('date', [$startDate, $endDate])
            ->whereHas('packageVariant', function($query) use ($package) {
                $query->where('package_id', $package->id);
            })
            ->get()
            ->groupBy('date')
            ->map(function($dayAvailabilities) {
                return $dayAvailabilities->groupBy('schedule_slot_id');
            });
    }

    private function getPricingData()
    {
        $packages = Package::with(['variants.prices'])
            ->where('is_active', true)
            ->where(function($query) {
                $query->where('name', 'like', '%ATV%')
                      ->orWhere('name', 'like', '%UTV%');
            })
            ->get();

        $pricingData = [];

        foreach ($packages as $package) {
            foreach ($package->variants as $variant) {
                $weekdayPrice = $variant->prices->where('price_type', 'weekday')->first();
                $weekendPrice = $variant->prices->where('price_type', 'weekend')->first();

                $pricingData[$package->id][$variant->id] = [
                    'weekday' => $weekdayPrice ? $weekdayPrice->amount : 0,
                    'weekend' => $weekendPrice ? $weekendPrice->amount : 0,
                    'original_weekday' => $weekdayPrice ? $weekdayPrice->original_amount : 0,
                    'original_weekend' => $weekendPrice ? $weekendPrice->original_amount : 0,
                ];
            }
        }

        return $pricingData;
    }
}
