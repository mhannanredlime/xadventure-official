<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageVariant;
use App\Models\Availability;
use App\Models\PriceOverride;
use App\Models\SlotPreset;
use App\Models\SlotPresetOverride;
use App\Models\ScheduleSlot;
use App\Models\Reservation;
use App\Services\CalendarService;
use App\Services\PriceCalculationService;
use App\Services\VehicleAvailabilityService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class CalendarController extends Controller
{
    protected $calendarService;
    protected $priceService;
    protected $vehicleAvailabilityService;

    public function __construct(CalendarService $calendarService, PriceCalculationService $priceService, VehicleAvailabilityService $vehicleAvailabilityService)
    {
        $this->calendarService = $calendarService;
        $this->priceService = $priceService;
        $this->vehicleAvailabilityService = $vehicleAvailabilityService;
    }

    public function index()
    {
        $packages = Package::with(['variants.prices', 'vehicleTypes'])
            ->where('is_active', true)
            ->get();

        $defaultPackage = $packages->first();
        $currentMonth = now()->format('Y-m');

        return view('admin.calendar.index', compact('packages', 'defaultPackage', 'currentMonth'));
    }

    public function testApi()
    {
        return response()->json([
            'success' => true,
            'message' => 'API is working correctly',
            'user' => Auth::user()->name ?? 'Unknown',
            'timestamp' => now()
        ]);
    }

    public function getPackageData(Package $package, Request $request)
    {
        try {
            Log::info('getPackageData called for package: ' . $package->id);
            
            $month = $request->get('month', now()->format('Y-m'));
            $year = Carbon::parse($month)->year;
            $monthNum = Carbon::parse($month)->month;

            Log::info('Processing month data', [
                'month' => $month,
                'year' => $year,
                'monthNum' => $monthNum
            ]);

            $package->load(['variants.prices', 'vehicleTypes']);
            
            Log::info('Package loaded with relationships', [
                'variants_count' => $package->variants->count(),
                'vehicle_types_count' => $package->vehicleTypes->count()
            ]);
            
            $monthData = $this->calendarService->getMonthData($package->id, $monthNum, $year);
            $calendarData = $this->calendarService->getCalendarDataForPackage($package->id, $monthNum, $year);

            Log::info('Calendar data generated successfully', [
                'month_data_keys' => array_keys($monthData),
                'calendar_data_count' => count($calendarData)
            ]);
            
            return response()->json([
                'package' => $package,
                'month_data' => $monthData,
                'calendar_data' => $calendarData,
                'current_month' => $month
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getPackageData: ' . $e->getMessage(), [
                'package_id' => $package->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to load package data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAvailabilityForDate(Request $request)
    {
        try {
            $validated = $request->validate([
                'package_variant_id' => 'required|exists:package_variants,id',
                'date' => 'required|date'
            ]);

            $variant = PackageVariant::find($validated['package_variant_id']);
            $date = $validated['date'];

            if (!$variant) {
                return response()->json(['error' => 'Package variant not found'], 404);
            }

            $availability = Availability::where([
                'package_variant_id' => $variant->id,
                'date' => $date
            ])->first();

            // Get all price overrides for this date for all variants in the same package
            $priceOverrides = PriceOverride::whereHas('packageVariant', function($query) use ($variant) {
                $query->where('package_id', $variant->package_id);
            })
            ->where('date', $date)
            ->with('packageVariant')
            ->get();

            // Get the specific price override for this variant
            $priceOverride = $priceOverrides->where('package_variant_id', $variant->id)->first();

            $currentPrice = $this->priceService->getPriceForDate($variant, $date);
            $defaultPrice = $this->priceService->getDefaultPriceForDate($variant, $date);

            // Load package relationship if not already loaded
            if (!$variant->relationLoaded('package')) {
                $variant->load('package');
            }

            // Check if this is a regular package
            $packageType = $variant->package->type ?? 'regular';
            $isRegularPackage = $packageType === 'regular';

            // Get dynamic vehicle availability
            if ($isRegularPackage) {
                // For regular packages, get the actual regular vehicle type data
                $vehicleAvailability = $this->vehicleAvailabilityService->calculateAvailabilityForPackageVariant($variant, $date);
            } else {
                // For ATV/UTV packages, use the vehicle availability service
                $vehicleAvailability = $this->vehicleAvailabilityService->calculateAvailabilityForPackageVariant($variant, $date);
            }

            // Calculate time slot availability (booked vs available slots)
            $timeSlotAvailability = $this->calculateTimeSlotAvailability($variant, $date);

            // Determine slot preset context for this date
            $presetOverride = SlotPresetOverride::where([
                'package_variant_id' => $variant->id,
                'date' => $date,
            ])->first();
            $defaultPreset = SlotPreset::where(['is_default' => true, 'is_active' => true])->first();

            return response()->json([
                'availability' => $availability,
                'price_override' => $priceOverride,
                'all_price_overrides' => $priceOverrides, // Return all price overrides for the date
                'current_price' => $currentPrice,
                'default_price' => $defaultPrice,
                'variant' => $variant->load('prices'),
                'vehicle_availability' => $vehicleAvailability,
                'time_slot_availability' => $timeSlotAvailability,
                'slot_preset' => $presetOverride ? $presetOverride->preset : $defaultPreset,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getAvailabilityForDate: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to load availability data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSlotPresets()
    {
        $presets = SlotPreset::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
        return response()->json($presets);
    }

    public function setSlotPresetOverride(Request $request)
    {
        $validated = $request->validate([
            'package_variant_id' => 'required|exists:package_variants,id',
            'date' => 'required|date',
            'slot_preset_id' => 'nullable|exists:slot_presets,id',
        ]);

        if (!empty($validated['slot_preset_id'])) {
            $override = SlotPresetOverride::updateOrCreate(
                [
                    'package_variant_id' => $validated['package_variant_id'],
                    'date' => $validated['date'],
                ],
                [
                    'slot_preset_id' => $validated['slot_preset_id'],
                ]
            );
        } else {
            SlotPresetOverride::where([
                'package_variant_id' => $validated['package_variant_id'],
                'date' => $validated['date'],
            ])->delete();
            $override = null;
        }

        return response()->json([
            'success' => true,
            'override' => $override ? $override->load('preset') : null,
        ]);
    }

    public function updateAvailability(Request $request)
    {
        $validated = $request->validate([
            'package_variant_id' => 'required|exists:package_variants,id',
            'date' => 'required|date',
            'is_day_off' => 'boolean',
            'capacity_total' => 'nullable|integer|min:0',
            'special_price_enabled' => 'boolean',
            'price_tag' => 'nullable|in:premium,discounted',
            'price_override_amount' => 'nullable|numeric|min:0',
            'price_override_id' => 'nullable|exists:variant_prices,id'
        ]);

        // Update availability only if capacity_total is provided
        $availability = null;
        if (isset($validated['capacity_total'])) {
            // Get the package ID for this variant
            $variant = PackageVariant::find($validated['package_variant_id']);
            if ($variant) {
                // Get all variants for this package
                $packageVariants = PackageVariant::where('package_id', $variant->package_id)->get();
                
                // Update availability for all variants in this package
                foreach ($packageVariants as $packageVariant) {
                    $availability = Availability::updateOrCreate(
                        [
                            'package_variant_id' => $packageVariant->id,
                            'date' => $validated['date']
                        ],
                        [
                            'is_day_off' => $validated['is_day_off'] ?? false,
                            'capacity_total' => ($validated['is_day_off'] ?? false) ? 0 : $validated['capacity_total'],
                            'capacity_reserved' => 0 // Reset reserved when updating
                        ]
                    );
                    
                    Log::info('Updated availability for package variant', [
                        'package_variant_id' => $packageVariant->id,
                        'package_id' => $variant->package_id,
                        'date' => $validated['date'],
                        'is_day_off' => $validated['is_day_off'] ?? false,
                        'capacity_total' => ($validated['is_day_off'] ?? false) ? 0 : $validated['capacity_total']
                    ]);
                }
            } else {
                // Fallback to original behavior if variant not found
                $availability = Availability::updateOrCreate(
                    [
                        'package_variant_id' => $validated['package_variant_id'],
                        'date' => $validated['date']
                    ],
                    [
                        'is_day_off' => $validated['is_day_off'] ?? false,
                        'capacity_total' => ($validated['is_day_off'] ?? false) ? 0 : $validated['capacity_total'],
                        'capacity_reserved' => 0 // Reset reserved when updating
                    ]
                );
            }
        }

        // Handle special pricing mode with date-specific price overrides
        if ($validated['special_price_enabled'] && $validated['price_tag']) {
            // If we have a specific price override amount, create/update that specific override for this variant only
            if (isset($validated['price_override_amount'])) {
                // Get the base price for this variant on the selected date for validation
                $dayOfWeek = \Carbon\Carbon::parse($validated['date'])->dayOfWeek;
                $priceType = ($dayOfWeek >= 6) ? 'weekend' : 'weekday';
                
                $basePrice = PackageVariant::find($validated['package_variant_id'])
                    ->prices()
                    ->where('price_type', $priceType)
                    ->first();
                
                $basePriceAmount = $basePrice ? $basePrice->amount : 0;
                $overrideAmount = $validated['price_override_amount'];
                
                // Validate premium price must be higher than base price
                if ($validated['price_tag'] === 'premium' && !$this->priceService->validatePremiumPrice($overrideAmount, $basePriceAmount)) {
                    return response()->json([
                        'success' => false,
                        'error' => $this->priceService->getPriceValidationMessage('premium', $overrideAmount, $basePriceAmount)
                    ], 422);
                }
                
                // Validate discounted price must be lower than base price
                if ($validated['price_tag'] === 'discounted' && !$this->priceService->validateDiscountedPrice($overrideAmount, $basePriceAmount)) {
                    return response()->json([
                        'success' => false,
                        'error' => $this->priceService->getPriceValidationMessage('discounted', $overrideAmount, $basePriceAmount)
                    ], 422);
                }
                
                Log::info('Creating specific price override for variant', [
                    'package_variant_id' => $validated['package_variant_id'],
                    'date' => $validated['date'],
                    'price_tag' => $validated['price_tag'],
                    'price_amount' => $validated['price_override_amount'],
                    'base_price' => $basePriceAmount
                ]);
                
                $priceOverride = PriceOverride::updateOrCreate(
                    [
                        'package_variant_id' => $validated['package_variant_id'],
                        'date' => $validated['date']
                    ],
                    [
                        'price_tag' => $validated['price_tag'],
                        'price_amount' => $validated['price_override_amount']
                    ]
                );
                
                Log::info('Specific price override created/updated', ['id' => $priceOverride->id]);
            } else {
                // Apply the price tag to ALL variants under the same package with their respective calculated prices
                $baseVariant = PackageVariant::find($validated['package_variant_id']);
                if ($baseVariant) {
                    $siblingVariants = PackageVariant::where('package_id', $baseVariant->package_id)->get();
                    foreach ($siblingVariants as $variant) {
                        // Get the default price for this variant on the selected date
                        $dayOfWeek = \Carbon\Carbon::parse($validated['date'])->dayOfWeek;
                        $priceType = ($dayOfWeek >= 6) ? 'weekend' : 'weekday';
                        
                        $defaultPrice = $variant->prices()
                            ->where('price_type', $priceType)
                            ->first();
                        
                        $priceAmount = $defaultPrice ? $defaultPrice->amount : 0;
                        
                        // Apply premium or discount based on price tag
                        if ($validated['price_tag'] === 'premium') {
                            $priceAmount = $priceAmount * 1.2; // 20% premium
                        } elseif ($validated['price_tag'] === 'discounted') {
                            $priceAmount = $priceAmount * 0.8; // 20% discount
                        }
                        
                        Log::info('Creating price override for variant', [
                            'variant_id' => $variant->id,
                            'variant_name' => $variant->variant_name,
                            'date' => $validated['date'],
                            'price_type' => $priceType,
                            'default_price' => $defaultPrice ? $defaultPrice->amount : 0,
                            'calculated_price' => $priceAmount,
                            'price_tag' => $validated['price_tag']
                        ]);
                        
                        PriceOverride::updateOrCreate(
                            [
                                'package_variant_id' => $variant->id,
                                'date' => $validated['date']
                            ],
                            [
                                'price_tag' => $validated['price_tag'],
                                'price_amount' => $priceAmount
                            ]
                        );
                    }
                }
            }
        } else {
            // Remove price override for ALL variants in the same package if disabled
            $baseVariant = PackageVariant::find($validated['package_variant_id']);
            if ($baseVariant) {
                $siblingVariantIds = PackageVariant::where('package_id', $baseVariant->package_id)->pluck('id');
                PriceOverride::whereIn('package_variant_id', $siblingVariantIds)
                    ->where('date', $validated['date'])
                    ->delete();
            } else {
                PriceOverride::where([
                    'package_variant_id' => $validated['package_variant_id'],
                    'date' => $validated['date']
                ])->delete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => isset($validated['capacity_total']) ? 'Availability updated successfully' : 'Price override updated successfully',
            'availability' => $availability ? $availability->fresh() : null,
            'price_override' => $validated['special_price_enabled'] ? 
                PriceOverride::where([
                    'package_variant_id' => $validated['package_variant_id'],
                    'date' => $validated['date']
                ])->first() : null
        ]);
    }

    public function updateSlotAvailability(Request $request)
    {
        $validated = $request->validate([
            'package_variant_id' => 'required|exists:package_variants,id',
            'date' => 'required|date',
            'schedule_slot_id' => 'required|exists:schedule_slots,id',
            'is_day_off' => 'boolean',
            'capacity_total' => 'required|integer|min:0',
        ]);

        $availability = Availability::updateOrCreate(
            [
                'package_variant_id' => $validated['package_variant_id'],
                'date' => $validated['date'],
                'schedule_slot_id' => $validated['schedule_slot_id']
            ],
            [
                'is_day_off' => $validated['is_day_off'] ?? false,
                'capacity_total' => ($validated['is_day_off'] ?? false) ? 0 : $validated['capacity_total'],
                'capacity_reserved' => 0
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Slot availability updated successfully',
            'availability' => $availability->fresh()
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'package_variant_id' => 'required|exists:package_variants,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_day_off' => 'boolean',
            'capacity_total' => 'required|integer|min:0',
            'special_price_enabled' => 'boolean',
            'price_tag' => 'nullable|in:premium,discounted',
            'price_amounts' => 'nullable|array',
            'price_amounts.*' => 'numeric|min:0',
            'weekday_pricing' => 'nullable|array',
            'weekday_pricing.*' => 'numeric|min:0',
            'weekend_pricing' => 'nullable|array',
            'weekend_pricing.*' => 'numeric|min:0'
        ]);

        // Validate date range (max 90 days to prevent performance issues)
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $daysDifference = $startDate->diffInDays($endDate) + 1;
        
        if ($daysDifference > 90) {
            return response()->json([
                'success' => false,
                'error' => 'Date range cannot exceed 90 days'
            ], 422);
        }

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            
            Log::info('Processing date in bulk update', [
                'date' => $dateString,
                'special_price_enabled' => $validated['special_price_enabled'],
                'price_tag' => $validated['price_tag'] ?? 'none'
            ]);

            // Update availability
            Availability::updateOrCreate(
                [
                    'package_variant_id' => $validated['package_variant_id'],
                    'date' => $dateString
                ],
                [
                    'is_day_off' => $validated['is_day_off'],
                    'capacity_total' => $validated['is_day_off'] ? 0 : $validated['capacity_total'],
                    'capacity_reserved' => 0
                ]
            );

            // Handle special pricing mode across all variants of the same package
            if ($validated['special_price_enabled'] && $validated['price_tag']) {
                $baseVariant = PackageVariant::find($validated['package_variant_id']);
                if ($baseVariant) {
                    $siblingVariants = PackageVariant::where('package_id', $baseVariant->package_id)->get();
                    foreach ($siblingVariants as $variant) {
                        // Determine if this is a weekday or weekend
                        $dayOfWeek = $date->dayOfWeek;
                        $isWeekend = ($dayOfWeek == 0 || $dayOfWeek == 6); // Sunday = 0, Saturday = 6
                        $priceType = $isWeekend ? 'weekend' : 'weekday';
                        
                        // Get the appropriate pricing based on day type
                        $pricingData = $isWeekend ? $validated['weekend_pricing'] : $validated['weekday_pricing'];
                        
                        // Get the default price for this variant on the selected date
                        $defaultPrice = $variant->prices()
                            ->where('price_type', $priceType)
                            ->first();
                        
                        $basePriceAmount = $defaultPrice ? $defaultPrice->original_amount : 0;
                        
                        // Use specific price amount if provided for this day type
                        if (isset($pricingData[$variant->id])) {
                            $priceAmount = $pricingData[$variant->id];
                            
                            // Validate premium price must be higher than base price
                            if ($validated['price_tag'] === 'premium' && $priceAmount <= $basePriceAmount) {
                                Log::warning('Premium price validation failed', [
                                    'variant_id' => $variant->id,
                                    'price_amount' => $priceAmount,
                                    'base_price' => $basePriceAmount,
                                    'day_type' => $priceType
                                ]);
                                continue; // Skip this variant
                            }
                            
                            // Validate discounted price must be lower than base price
                            if ($validated['price_tag'] === 'discounted' && $priceAmount >= $basePriceAmount) {
                                Log::warning('Discounted price validation failed', [
                                    'variant_id' => $variant->id,
                                    'price_amount' => $priceAmount,
                                    'base_price' => $basePriceAmount,
                                    'day_type' => $priceType
                                ]);
                                continue; // Skip this variant
                            }
                        } else {
                            // Fallback to percentage-based pricing
                            $priceAmount = $basePriceAmount;
                            
                            if ($validated['price_tag'] === 'premium') {
                                $priceAmount = $priceAmount * 1.2; // 20% premium
                            } elseif ($validated['price_tag'] === 'discounted') {
                                $priceAmount = $priceAmount * 0.8; // 20% discount
                            }
                        }
                        
                        PriceOverride::updateOrCreate(
                            [
                                'package_variant_id' => $variant->id,
                                'date' => $dateString
                            ],
                            [
                                'price_tag' => $validated['price_tag'],
                                'price_amount' => $priceAmount
                            ]
                        );
                        
                        Log::info('Created price override for variant', [
                            'variant_id' => $variant->id,
                            'variant_name' => $variant->variant_name,
                            'date' => $dateString,
                            'price_tag' => $validated['price_tag'],
                            'price_amount' => $priceAmount,
                            'base_price' => $basePriceAmount,
                            'day_type' => $priceType,
                            'used_specific_amount' => isset($pricingData[$variant->id])
                        ]);
                    }
                }
            } else {
                $baseVariant = PackageVariant::find($validated['package_variant_id']);
                if ($baseVariant) {
                    $siblingVariantIds = PackageVariant::where('package_id', $baseVariant->package_id)->pluck('id');
                    PriceOverride::whereIn('package_variant_id', $siblingVariantIds)
                        ->where('date', $dateString)
                        ->delete();
                } else {
                    PriceOverride::where([
                        'package_variant_id' => $validated['package_variant_id'],
                        'date' => $dateString
                    ])->delete();
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Bulk update completed successfully'
        ]);
    }

    public function updatePrice(Request $request)
    {
        $validated = $request->validate([
            'price_id' => 'required|exists:variant_prices,id',
            'amount' => 'required|numeric|min:0'
        ]);

        try {
            $price = \App\Models\VariantPrice::find($validated['price_id']);
            
            // Set original_amount if it's not already set (first time updating)
            if (is_null($price->original_amount)) {
                $price->original_amount = $price->amount;
                Log::info('Set original_amount for price', [
                    'price_id' => $price->id,
                    'original_amount' => $price->original_amount
                ]);
            }
            
            $price->amount = $validated['amount'];
            $price->save();

            Log::info('Price updated successfully', [
                'price_id' => $price->id,
                'old_amount' => $price->getOriginal('amount'),
                'new_amount' => $price->amount,
                'original_amount' => $price->original_amount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Price updated successfully',
                'price' => $price
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update price: ' . $e->getMessage(), [
                'price_id' => $validated['price_id'],
                'amount' => $validated['amount'],
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update price: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset pricing for a specific date and price type, clearing both base prices and overrides
     */
    public function resetPricingForDate(Request $request)
    {
        $validated = $request->validate([
            'package_variant_id' => 'required|exists:package_variants,id',
            'date' => 'required|date',
            'price_type' => 'required|in:weekday,weekend'
        ]);

        try {
            $variant = \App\Models\PackageVariant::find($validated['package_variant_id']);
            if (!$variant) {
                throw new Exception('Package variant not found');
            }

            // Get all variants in the same package
            $packageVariants = \App\Models\PackageVariant::where('package_id', $variant->package_id)->get();
            
            $resetCount = 0;
            $overrideCount = 0;

            foreach ($packageVariants as $packageVariant) {
                // Reset base prices to original amounts
                $prices = $packageVariant->prices()->where('price_type', $validated['price_type'])->get();
                
                foreach ($prices as $price) {
                    if ($price->original_amount !== null) {
                        $price->amount = $price->original_amount;
                        $price->save();
                        $resetCount++;
                        
                        Log::info('Reset base price to original amount', [
                            'price_id' => $price->id,
                            'variant_id' => $packageVariant->id,
                            'original_amount' => $price->original_amount
                        ]);
                    }
                }

                // Clear price overrides for this date
                $overrides = \App\Models\PriceOverride::where([
                    'package_variant_id' => $packageVariant->id,
                    'date' => $validated['date']
                ])->get();

                foreach ($overrides as $override) {
                    $override->delete();
                    $overrideCount++;
                    
                    Log::info('Deleted price override', [
                        'override_id' => $override->id,
                        'variant_id' => $packageVariant->id,
                        'date' => $validated['date']
                    ]);
                }
            }

            Log::info('Pricing reset completed', [
                'package_id' => $variant->package_id,
                'date' => $validated['date'],
                'price_type' => $validated['price_type'],
                'prices_reset' => $resetCount,
                'overrides_cleared' => $overrideCount
            ]);

            return response()->json([
                'success' => true,
                'message' => "Reset {$resetCount} prices and cleared {$overrideCount} price overrides",
                'prices_reset' => $resetCount,
                'overrides_cleared' => $overrideCount
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reset pricing for date: ' . $e->getMessage(), [
                'package_variant_id' => $validated['package_variant_id'],
                'date' => $validated['date'],
                'price_type' => $validated['price_type'],
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset pricing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ensure all prices have original_amount set
     */
    public function ensureOriginalAmounts()
    {
        try {
            $pricesWithoutOriginal = \App\Models\VariantPrice::whereNull('original_amount')->get();
            $updatedCount = 0;
            
            foreach ($pricesWithoutOriginal as $price) {
                $price->original_amount = $price->amount;
                $price->save();
                $updatedCount++;
            }
            
            return response()->json([
                'success' => true,
                'message' => "Updated {$updatedCount} prices with original_amount",
                'updated_count' => $updatedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to ensure original amounts: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to ensure original amounts: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getVehicleAvailability(Request $request)
    {
        $validated = $request->validate([
            'package_variant_id' => 'required|exists:package_variants,id',
            'date' => 'required|date',
            'vehicle_type' => 'nullable|string'
        ]);

        $variant = PackageVariant::find($validated['package_variant_id']);
        $date = $validated['date'];
        $vehicleType = $validated['vehicle_type'] ?? null;

        // If specific vehicle type is requested, calculate availability for that type only
        if ($vehicleType) {
            $vehicleAvailability = $this->vehicleAvailabilityService->calculateAvailabilityForVehicleType($vehicleType, $date);
            
            return response()->json([
                'total_available' => $vehicleAvailability['total_available'],
                'total_vehicles' => $vehicleAvailability['total_vehicles'],
                'booked_vehicles' => $vehicleAvailability['booked_vehicles'],
                'vehicle_type' => $vehicleType,
                'vehicle_types' => [
                    $vehicleType => [
                        'total_vehicles' => $vehicleAvailability['total_vehicles'],
                        'booked_vehicles' => $vehicleAvailability['booked_vehicles'],
                        'available_vehicles' => $vehicleAvailability['total_available']
                    ]
                ]
            ]);
        }

        // Get dynamic vehicle availability for all vehicle types in the package
        $vehicleAvailability = $this->vehicleAvailabilityService->calculateAvailabilityForPackageVariant($variant, $date);



        return response()->json([
            'total_available' => $vehicleAvailability['total_available'],
            'total_vehicles' => $vehicleAvailability['total_vehicles'] ?? array_sum(array_column($vehicleAvailability['vehicle_types'], 'total_vehicles')),
            'booked_vehicles' => $vehicleAvailability['total_booked'],
            'vehicle_types' => $vehicleAvailability['vehicle_types']
        ]);
    }

    private function getDefaultPrice(PackageVariant $variant, $date)
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $priceType = ($dayOfWeek >= 6) ? 'weekend' : 'weekday';

        $defaultPrice = $variant->prices()
            ->where('price_type', $priceType)
            ->first();

        return $defaultPrice ? $defaultPrice->amount : 0;
    }

    /**
     * Calculate time slot availability (booked vs available slots)
     */
    private function calculateTimeSlotAvailability(PackageVariant $variant, string $date): array
    {
        // Load package relationship if not already loaded
        if (!$variant->relationLoaded('package')) {
            $variant->load('package');
        }

        // Check if this is a regular package
        $packageType = $variant->package->type ?? 'regular';
        $isRegularPackage = $packageType === 'regular';

        // Get all active schedule slots for this date
        $presetOverride = SlotPresetOverride::where([
            'package_variant_id' => $variant->id,
            'date' => $date,
        ])->first();

        if ($presetOverride) {
            $scheduleSlots = $presetOverride->preset->scheduleSlots;
        } else {
            $defaultPreset = SlotPreset::where(['is_default' => true, 'is_active' => true])->first();
            if ($defaultPreset) {
                $scheduleSlots = $defaultPreset->scheduleSlots;
            } else {
                $scheduleSlots = ScheduleSlot::where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('start_time')
                    ->get();
            }
        }

        $totalSlots = $scheduleSlots->count();
        $bookedSlots = 0;
        $availableSlots = 0;

        if ($isRegularPackage) {
            // For regular packages, all slots are considered available
            $availableSlots = $totalSlots;
            $bookedSlots = 0;
        } else {
            // For ATV/UTV packages, check actual availability
            // Get actual vehicle availability for this variant (overall capacity)
            $vehicleAvailability = $this->vehicleAvailabilityService->calculateAvailabilityForPackageVariant($variant, $date);
            $totalAvailableCapacity = $vehicleAvailability['available_capacity'];

            foreach ($scheduleSlots as $slot) {
                // Calculate booked capacity for this specific slot
                $slotBookedCapacity = $this->getBookedCapacityForSlot($variant->id, $date, $slot->id);
                
                // Check if this slot has any remaining capacity
                if ($slotBookedCapacity > 0) {
                    $bookedSlots++;
                } else {
                    // Only count as available if there's overall vehicle capacity
                    if ($totalAvailableCapacity > 0) {
                        $availableSlots++;
                    }
                }
            }
        }

        return [
            'total_slots' => $totalSlots,
            'booked_slots' => $bookedSlots,
            'available_slots' => $availableSlots,
        ];
    }

    /**
     * Get booked capacity for a specific slot
     */
    private function getBookedCapacityForSlot(int $variantId, string $date, int $slotId): int
    {
        // Get booked capacity from reservation items for this specific slot
        $newBookedCapacity = \App\Models\Reservation::join('reservation_items', 'reservations.id', '=', 'reservation_items.reservation_id')
            ->join('package_variants', 'reservation_items.package_variant_id', '=', 'package_variants.id')
            ->where('reservation_items.package_variant_id', $variantId)
            ->where('reservations.date', $date)
            ->where('reservations.schedule_slot_id', $slotId)
            ->whereIn('reservations.booking_status', ['pending', 'confirmed'])
            ->sum(\Illuminate\Support\Facades\DB::raw('reservation_items.qty * package_variants.capacity'));

        // Also check for old reservations that don't have reservation items
        $oldBookedCapacity = \App\Models\Reservation::join('package_variants', 'reservations.package_variant_id', '=', 'package_variants.id')
            ->where('package_variants.id', $variantId)
            ->where('reservations.date', $date)
            ->where('reservations.schedule_slot_id', $slotId)
            ->whereIn('reservations.booking_status', ['pending', 'confirmed'])
            ->whereNotExists(function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                      ->from('reservation_items')
                      ->whereRaw('reservation_items.reservation_id = reservations.id');
            })
            ->sum('reservations.party_size');

        return $newBookedCapacity + $oldBookedCapacity;
    }
}
