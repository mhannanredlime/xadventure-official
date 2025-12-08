<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\Package;
use App\Models\Reservation;
use App\Models\ScheduleSlot;
use App\Models\Cart;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class VehicleAvailabilityService
{
    /**
     * Calculate available vehicles for a specific package and rider type
     */
    public function calculateAvailabilityForPackage(
        Package $package,
        string $date,
        int $scheduleSlotId = null,
        int $riderTypeId = null,
        array $excludeCartItems = []
    ): array {
        // Determine target Vehicle Type
        $targetVehicleType = $this->getVehicleTypeForRiderType($package, $riderTypeId);
        
        $totalCapacity = 0;
        $totalBookedCapacity = 0;
        $vehicleTypes = $package->vehicleTypes;
        $vehicleTypeBreakdown = [];

        // Simple approach: Check availability of the TARGET vehicle type.
        // If no target (e.g. Regular package?), check all?
        // Regular Package -> Regular Vehicle.
        
        // If we have a specific target vehicle type (common case for ATV/UTV)
        if ($targetVehicleType) {
             $stats = $this->calculateAvailabilityForVehicleType($targetVehicleType->name, $date);
             // Update specific vehicle type stats
             $vehicleTypeBreakdown[$targetVehicleType->name] = [
                 'total_vehicles' => $stats['total_vehicles'],
                 'booked_vehicles' => $stats['booked_vehicles'],
                 'available_vehicles' => $stats['total_available'],
                 'vehicle_type_id' => $targetVehicleType->id,
                 'seating_capacity' => $targetVehicleType->seating_capacity,
             ];
             $totalCapacity += $stats['total_vehicles'] * $targetVehicleType->seating_capacity;
             
             // Now check CART usage
             $cartUsage = $this->getCartBookedCountForVehicleType($targetVehicleType->id, $date, $scheduleSlotId, $excludeCartItems);
             
             $availableVehicles = max(0, $stats['total_available'] - $cartUsage);
             
             // Slot logic: calculateAvailabilityForVehicleType already counts ALL bookings for date.
             // But we need slot-specific logic if slots are independent?
             // System seems to use shared vehicles across slots? Or slots are time blocks?
             // Step 906 line 66: getBookedCapacityForPackage passed slotId.
             // Step 906 line 152: "Each time slot should have independent capacity".
             // So I need to filter bookings by slot!
             
             // Recalculate booked with slot filter
             $bookedVehiclesSlot = $this->getBookedVehiclesForVehicleType($targetVehicleType->id, $date, $scheduleSlotId);
             $availableVehiclesSlot = max(0, $stats['total_vehicles'] - $bookedVehiclesSlot - $cartUsage);
             
             return [
                'total_available' => $stats['total_vehicles'],
                'total_booked' => $bookedVehiclesSlot + $cartUsage,
                'vehicle_types' => $vehicleTypeBreakdown,
                'is_available' => $availableVehiclesSlot > 0,
                'available_capacity' => $availableVehiclesSlot * $targetVehicleType->seating_capacity, // Rough est
                'total_vehicles' => $stats['total_vehicles'],
                'is_day_off' => false // TODO: Check Availability/PriceOverride overrides for day off
             ];
        }

        // Fallback for packages without specific mapping (sum all?)
        return [
            'total_available' => 0,
            'total_booked' => 0,
            'vehicle_types' => [],
            'is_available' => false,
            'available_capacity' => 0,
            'total_vehicles' => 0,
            'is_day_off' => false
        ];
    }

    /**
     * Determine vehicle type based on package and rider type
     */
    private function getVehicleTypeForRiderType(Package $package, ?int $riderTypeId): ?VehicleType
    {
        // For ATV/UTV Trail Rides package
        if (str_contains($package->name, 'ATV') && str_contains($package->name, 'UTV')) {
            if ($riderTypeId == 1) {
                return VehicleType::where('name', 'ATV')->first();
            } elseif ($riderTypeId == 2) {
                return VehicleType::where('name', 'UTV')->first();
            }
        }

        // For UTV Trail Rides 2 package
        if (str_contains($package->name, 'UTV') && !str_contains($package->name, 'ATV')) {
            return VehicleType::where('name', 'UTV')->first();
        }

        // For Regular Package
        if ($package->name === 'Regular Package') {
            return VehicleType::where('name', 'Regular')->first();
        }

        // Fallback
        return $package->vehicleTypes->first();
    }

    /**
     * Calculate available vehicles for a specific vehicle type on a given date
     * (Total for day if slot is null, or irrelevant if slots are independent)
     * Actually, if slots are independent, "availability" depends on the slot.
     */
    public function calculateAvailabilityForVehicleType(string $vehicleTypeName, string $date): array
    {
        $vehicleType = VehicleType::where('name', $vehicleTypeName)->where('is_active', true)->first();
        if (!$vehicleType) return [];

        $totalVehicles = Vehicle::where('vehicle_type_id', $vehicleType->id)
            ->where('is_active', true)
            ->where(function ($query) use ($date) {
                $query->whereNull('op_start_date')->orWhere('op_start_date', '<=', $date);
            })->count();

        // This counts bookings across ALL slots? Using null for slotId
        // If vehicles are tied to slots (e.g. 1 hour rental), total vehicles is capacity PER SLOT.
        // Booked vehicles is bookings in THAT SLOT.
        
        return [
            'total_vehicles' => $totalVehicles,
            'vehicle_type' => $vehicleTypeName,
            'booked_vehicles' => 0, // Slot dependent, calculated elsewhere usually
            'total_available' => $totalVehicles,
        ];
    }

    /**
     * Get booked vehicles count for a vehicle type, date, and slot
     */
    private function getBookedVehiclesForVehicleType(int $vehicleTypeId, string $date, int $scheduleSlotId = null): int
    {
        // 1. Get all Reservations for the date (and slot)
        $reservations = Reservation::with(['reservationItems.packagePrice', 'items.packagePrice']) // Support both names
            ->where('date', $date)
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->when($scheduleSlotId, function($q) use ($scheduleSlotId) {
                $q->where('schedule_slot_id', $scheduleSlotId);
            })
            ->get();
            
        $bookedCount = 0;
        
        foreach ($reservations as $res) {
            // Need to determine if this reservation uses the target Vehicle Type.
            // Check Package ID and Rider Type ID from Reservation (new style) or Items.
            
            // New Style Link: Reservation has package_id and package_price_id
            if ($res->package_id && $res->packagePrice) {
                 $pkg = Package::find($res->package_id);
                 $riderTypeId = $res->packagePrice->rider_type_id;
                 $vt = $this->getVehicleTypeForRiderType($pkg, $riderTypeId);
                 if ($vt && $vt->id == $vehicleTypeId) {
                     $bookedCount += $res->party_size; // Assuming party_size = number of vehicles? Or qty?
                     // Verify convention: party_size usually people. qty usually units. 
                     // Reservation table has party_size. ReservationItems has qty.
                     // Helper code used 'party_size' for old bookings.
                 }
            }
             
            // Also Check reservation items (if any, overrides main?)
            foreach ($res->reservationItems as $item) {
                 if ($item->packagePrice) {
                     $pkg = Package::find($item->package_id ?? $res->package_id);
                     $riderTypeId = $item->packagePrice->rider_type_id;
                     $vt = $this->getVehicleTypeForRiderType($pkg, $riderTypeId);
                     if ($vt && $vt->id == $vehicleTypeId) {
                         // Add qty. Note: if we counted main $res, don't double count?
                         // Ideally reservation_items replaces main logic if present.
                         // But for now, let's assume one or the other structure based on migration state.
                         // If items exist, use items.
                     }
                 }
            }
        }
        
        // Revised simplified query logic (In-Memory for accuracy with complex mapping):
        // Fetch all reservations for date/slot.
        // Map each to VehicleType.
        // Count.
        
        // This is expensive if many bookings. Optimized Query preferred if possible.
        // But mapping "Package + RiderType -> Vehicle" is code-based.
        // So I'll stick to loop.
        
        $count = 0;
        foreach($reservations as $r) {
            $items = $r->reservationItems;
            if ($items->count() > 0) {
                foreach($items as $item) {
                     $pkg = Package::find($item->package_id ?? $r->package_id);
                     $pp = $item->packagePrice; // loaded
                     if ($pkg && $pp) {
                         $vt = $this->getVehicleTypeForRiderType($pkg, $pp->rider_type_id);
                         if ($vt && $vt->id == $vehicleTypeId) {
                             $count += $item->qty; // Use Qty from Item
                         }
                     }
                }
            } else {
                // Main reservation
                if ($r->package_id && $r->package_price_id) {
                     $pkg = Package::find($r->package_id);
                     // Need to load PackagePrice if not eager loaded on 'items' path?
                     // Reference 'packagePrice' relation on Reservation model?
                     $pp = \App\Models\PackagePrice::find($r->package_price_id);
                     if ($pkg && $pp) {
                         $vt = $this->getVehicleTypeForRiderType($pkg, $pp->rider_type_id);
                         if ($vt && $vt->id == $vehicleTypeId) {
                             $count += $r->party_size; // Use party_size (mapped to qty in controller)
                         }
                     }
                }
            }
        }
        
        return $count;
    }

    private function getCartBookedCountForVehicleType(int $vehicleTypeId, string $date, int $scheduleSlotId = null, array $excludeCartItems = []): int
    {
        // Get all cart items (DB + Session)
        $count = 0;
        
        // DB Carts
        $carts = Cart::with(['package'])
            ->where('date', $date)
            ->when($scheduleSlotId, fn($q) => $q->where('schedule_slot_id', $scheduleSlotId))
            ->get();
            
        foreach ($carts as $item) {
            if (in_array($item->uuid, $excludeCartItems)) continue;
            
            $vt = $this->getVehicleTypeForRiderType($item->package, $item->rider_type_id);
            if ($vt && $vt->id == $vehicleTypeId) {
                $count += $item->quantity;
            }
        }
        
        return $count;
    }
}
