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

class AutvPackageNewController extends Controller
{
    /**
 * Calculate package price for specific date
 */
public function calculatePrice(Request $request)
{
    $request->validate([
        'package_id' => 'required|exists:packages,id',
        'date' => 'required|date',
        'rider_type_id' => 'sometimes|in:1,2'
    ]);
    
    $package = Package::findOrFail($request->package_id);
    $date = \Carbon\Carbon::parse($request->date);
    
    // Determine day type
    $dayName = $date->isWeekend() ? 'weekend' : 'weekday';
    
    // Check if ATV/UTV package
    $isATVUTV = str_contains($package->name, 'ATV') || str_contains($package->name, 'UTV');
    
    if ($isATVUTV) {
        // Get both prices
        $singlePrice = get_package_price($package, $dayName, 1);
        $doublePrice = get_package_price($package, $dayName, 2);
        
        // If specific rider type requested
        if ($request->has('rider_type_id')) {
            $price = $request->rider_type_id == 1 ? $singlePrice : $doublePrice;
            $effectivePrice = $package->display_starting_price > 0 ? 
                $package->display_starting_price : $price;
            
            return response()->json([
                'success' => true,
                'price' => $price,
                'single_price' => $singlePrice,
                'double_price' => $doublePrice,
                'display_price' => $package->display_starting_price ?? 0,
                'effective_price' => $effectivePrice,
                'day_type' => $dayName,
                'is_atv_utv' => true
            ]);
        }
        
        // Return both prices
        $effectivePrice = $package->display_starting_price > 0 ? 
            $package->display_starting_price : min($singlePrice, $doublePrice);
        
        return response()->json([
            'success' => true,
            'single_price' => $singlePrice,
            'double_price' => $doublePrice,
            'display_price' => $package->display_starting_price ?? 0,
            'effective_price' => $effectivePrice,
            'day_type' => $dayName,
            'is_atv_utv' => true
        ]);
    } else {
        // Regular package
        $price = get_package_price($package, $dayName);
        $effectivePrice = $package->display_starting_price > 0 ? 
            $package->display_starting_price : $price;
        
        return response()->json([
            'success' => true,
            'price' => $price,
            'display_price' => $package->display_starting_price ?? 0,
            'effective_price' => $effectivePrice,
            'day_type' => $dayName,
            'is_atv_utv' => false
        ]);
    }
}

/**
 * Check package availability
 */
public function checkAvailability(Request $request)
{
    $request->validate([
        'package_id' => 'required|exists:packages,id',
        'date' => 'required|date',
        'slot_id' => 'required|exists:schedule_slots,id',
        'quantity' => 'sometimes|integer|min:1'
    ]);
    
    $package = Package::with('vehicleTypes')->findOrFail($request->package_id);
    $date = $request->date;
    $slotId = $request->slot_id;
    $quantity = $request->quantity ?? 1;
    
    // Calculate total available vehicles
    $totalAvailableVehicles = 0;
    foreach ($package->vehicleTypes as $vehicleType) {
        $vehicleCount = \App\Models\Vehicle::where('vehicle_type_id', $vehicleType->id)
            ->where('is_active', true)
            ->where(function($query) use ($date) {
                $query->whereNull('op_start_date')
                    ->orWhere('op_start_date', '<=', $date);
            })
            ->count();
        $totalAvailableVehicles += $vehicleCount;
    }
    
    // Check existing reservations
    $totalBooked = \App\Models\Reservation::where('package_id', $request->package_id)
        ->where('selected_date', $date)
        ->where('time_slot_id', $slotId)
        ->where('status', '!=', 'cancelled')
        ->sum('quantity');
    
    $availableQuantity = max(0, $totalAvailableVehicles - $totalBooked);
    $isAvailable = $availableQuantity >= $quantity;
    
    return response()->json([
        'success' => true,
        'available_quantity' => $availableQuantity,
        'total_available' => $totalAvailableVehicles,
        'total_booked' => $totalBooked,
        'available' => $isAvailable,
        'message' => $isAvailable ? 
            "Available: {$availableQuantity} vehicles" : 
            "Not available"
    ]);
}

/**
 * Add packages to cart
 */
public function addPackagesToCart(Request $request)
{
    $request->validate([
        'items' => 'required|array',
        'items.*.package_id' => 'required|exists:packages,id',
        'items.*.rider_type_id' => 'required|in:0,1,2',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.selected_date' => 'required|date',
        'items.*.time_slot_id' => 'required|exists:schedule_slots,id',
        'acknowledgment' => 'sometimes|array',
        'signature' => 'sometimes|string'
    ]);
    
    $cartItems = [];
    $totalAmount = 0;
    
    foreach ($request->items as $item) {
        $package = Package::find($item['package_id']);
        $date = \Carbon\Carbon::parse($item['selected_date']);
        $dayName = $date->isWeekend() ? 'weekend' : 'weekday';
        
        // Get price using get_package_price()
        $price = 0;
        if ($item['rider_type_id'] == 1) {
            $price = get_package_price($package, $dayName, 1);
        } elseif ($item['rider_type_id'] == 2) {
            $price = get_package_price($package, $dayName, 2);
        } else {
            $price = get_package_price($package, $dayName);
        }
        
        // Use display price if available
        $finalPrice = $package->display_starting_price > 0 ? $package->display_starting_price : $price;
        $itemTotal = $finalPrice * $item['quantity'];
        $totalAmount += $itemTotal;
        
        $cartItems[] = [
            'package_id' => $item['package_id'],
            'rider_type_id' => $item['rider_type_id'],
            'quantity' => $item['quantity'],
            'selected_date' => $item['selected_date'],
            'time_slot_id' => $item['time_slot_id'],
            'unit_price' => $finalPrice,
            'total_price' => $itemTotal,
            'metadata' => [
                'package_name' => $package->name,
                'rider_type' => $item['rider_type_id'] == 1 ? 'Single Rider' : 
                               ($item['rider_type_id'] == 2 ? 'Double Rider' : 'Standard'),
                'day_type' => $dayName
            ]
        ];
    }
    
    // Save to session cart
    $cartData = [
        'items' => $cartItems,
        'total_amount' => $totalAmount,
        'acknowledgment' => $request->acknowledgment ?? [],
        'signature' => $request->signature ?? null,
        'created_at' => now()
    ];
    
    // Store in session
    session(['atv_utv_cart' => $cartData]);
    
    return response()->json([
        'success' => true,
        'message' => 'Packages added to cart successfully',
        'cart_count' => count($cartItems),
        'total_amount' => $totalAmount,
        'redirect_url' => route('frontend.process-to-checkout')
    ]);
}
}
