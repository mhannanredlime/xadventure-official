<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\Package;
use App\Models\PackageVariant;
use App\Models\Reservation;
use App\Models\ScheduleSlot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VehicleAvailabilityService
{
    /**
     * Calculate available vehicles for a specific package variant on a given date and time
     */
    public function calculateAvailabilityForPackageVariant(
        PackageVariant $packageVariant,
        string $date,
        int $scheduleSlotId = null,
        array $excludeCartItems = []
    ): array {
        // Get the package and its vehicle types
        $package = $packageVariant->package;
        $vehicleTypes = $package->vehicleTypes;

        // Determine which specific vehicle type this variant represents
        $targetVehicleType = $this->getVehicleTypeForVariant($packageVariant);

        // Calculate total capacity and bookings for this package
        $totalCapacity = 0;
        $totalBookedCapacity = 0;
        $vehicleTypeBreakdown = [];

        // Get total capacity from all vehicle types for this package
        foreach ($vehicleTypes as $vehicleType) {
            // Get total active vehicles of this type
            $totalVehicles = Vehicle::where('vehicle_type_id', $vehicleType->id)
                ->where('is_active', true)
                ->count();

            // Also get count without the op_start_date condition for debugging
            $totalVehiclesWithoutDate = Vehicle::where('vehicle_type_id', $vehicleType->id)
                ->where('is_active', true)
                ->count();





            $vehicleTypeBreakdown[$vehicleType->name] = [
                'total_vehicles' => $totalVehicles,
                'booked_vehicles' => 0, // Will be calculated at package level
                'available_vehicles' => $totalVehicles, // Will be calculated at package level
                'vehicle_type_id' => $vehicleType->id,
                'seating_capacity' => $vehicleType->seating_capacity,
            ];

            $totalCapacity += $totalVehicles * $vehicleType->seating_capacity;
        }

        // Get booked capacity for this specific package (not per vehicle type)
        $totalBookedCapacity = $this->getBookedCapacityForPackage($package->id, $date, $scheduleSlotId);

        // Check if there's a manually set capacity override for this package variant and date
        // First check for slot-specific override, then fall back to date-level override
        $manualCapacityOverride = \App\Models\Availability::where([
            'package_variant_id' => $packageVariant->id,
            'date' => $date,
            'schedule_slot_id' => $scheduleSlotId
        ])->first();

        // If no slot-specific override, check for date-level override (for all time slots)
        if (!$manualCapacityOverride) {
            $manualCapacityOverride = \App\Models\Availability::where([
                'package_variant_id' => $packageVariant->id,
                'date' => $date
            ])->whereNull('schedule_slot_id')->first();
        }

        // Check if this is a day off
        $isDayOff = false;
        if ($manualCapacityOverride && $manualCapacityOverride->is_day_off) {
            $isDayOff = true;
        }

        // If it's a day off, return 0 availability
        if ($isDayOff) {
            Log::info('Day off detected for package variant', [
                'package_variant_id' => $packageVariant->id,
                'date' => $date,
                'schedule_slot_id' => $scheduleSlotId
            ]);

            return [
                'total_available' => 0,
                'total_booked' => 0,
                'vehicle_types' => [],
                'is_available' => false,
                'available_capacity' => 0,
                'total_vehicles' => 0,
                'is_day_off' => true
            ];
        }

        // Log the manual capacity override check
        Log::info('Manual capacity override check', [
            'package_variant_id' => $packageVariant->id,
            'date' => $date,
            'schedule_slot_id' => $scheduleSlotId,
            'slot_specific_override_found' => !!$manualCapacityOverride,
            'date_level_override_found' => !!$manualCapacityOverride,
            'manual_capacity_override' => $manualCapacityOverride ? $manualCapacityOverride->toArray() : null
        ]);

        // If there's a manual capacity override, use that instead of calculated capacity
        if ($manualCapacityOverride && $manualCapacityOverride->capacity_total !== null) {
            // Validate that manual capacity doesn't exceed actual vehicle count
            $actualVehicleCount = array_sum(array_column($vehicleTypeBreakdown, 'total_vehicles'));
            $manualCapacity = $manualCapacityOverride->capacity_total;

            if ($manualCapacity > $actualVehicleCount) {
                // If manual capacity exceeds actual vehicles, cap it at the actual vehicle count
                $totalCapacity = $actualVehicleCount;
                // Log a warning about the invalid manual capacity
                Log::warning("Manual capacity override exceeds actual vehicle count", [
                    'package_variant_id' => $packageVariant->id,
                    'date' => $date,
                    'manual_capacity' => $manualCapacity,
                    'actual_vehicle_count' => $actualVehicleCount,
                    'capped_capacity' => $totalCapacity
                ]);
            } else {
                $totalCapacity = $manualCapacity;
            }

            $availableCapacity = max(0, $totalCapacity - $totalBookedCapacity);

            // Update vehicle type breakdown to reflect manual capacity (capped if necessary)
            foreach ($vehicleTypeBreakdown as $vehicleTypeName => &$breakdown) {
                $breakdown['available_vehicles'] = $totalCapacity;
            }
        } else {
            // Calculate available capacity for this package based on actual vehicles
            $availableCapacity = max(0, $totalCapacity - $totalBookedCapacity);
        }

        // FIX: Calculate per-slot availability correctly
        // Each time slot should have independent capacity based on total vehicles
        $totalVehicles = array_sum(array_column($vehicleTypeBreakdown, 'total_vehicles'));

        if ($totalVehicles > 0) {
            foreach ($vehicleTypeBreakdown as $vehicleTypeName => &$breakdown) {
                // For per-slot availability, calculate booked vehicles for this specific slot
                $slotBookedVehicles = $this->getBookedVehiclesForVehicleType($breakdown['vehicle_type_id'], $date, $scheduleSlotId);

                $breakdown['booked_vehicles'] = $slotBookedVehicles;
                // If there's a manual capacity override, use that for available vehicles
                if ($manualCapacityOverride && $manualCapacityOverride->capacity_total !== null) {
                    $breakdown['available_vehicles'] = max(0, $manualCapacityOverride->capacity_total - $breakdown['booked_vehicles']);
                } else {
                    $breakdown['available_vehicles'] = max(0, $breakdown['total_vehicles'] - $breakdown['booked_vehicles']);
                }
            }
        }

        // Calculate how many of this variant can be booked for this specific slot
        $maxVariantBookings = 0;
        if ($availableCapacity > 0) {
            $maxVariantBookings = floor($availableCapacity / $packageVariant->capacity);
        }

        // FIX: Return availability based on the total available vehicles for the package
        // Each variant should be able to use the total available vehicles, not be limited to a subset
        $totalAvailableFromVehicleTypes = array_sum(array_column($vehicleTypeBreakdown, 'available_vehicles'));
        
        // Account for cart items by reducing available capacity
        $cartBookedCapacity = $this->getCartBookedCapacityForPackage($package->id, $date, $scheduleSlotId, $excludeCartItems);
        $finalAvailableCapacity = max(0, $totalAvailableFromVehicleTypes - $cartBookedCapacity);
        
        



        // Calculate total vehicles for the return value
        $totalVehiclesForReturn = $manualCapacityOverride && $manualCapacityOverride->capacity_total !== null
            ? $manualCapacityOverride->capacity_total
            : array_sum(array_column($vehicleTypeBreakdown, 'total_vehicles'));

        return [
            'total_available' => $totalCapacity,
            'total_booked' => $totalBookedCapacity,
            'vehicle_types' => $vehicleTypeBreakdown,
            'is_available' => $finalAvailableCapacity > 0,
            'available_capacity' => $finalAvailableCapacity,
            'total_vehicles' => $totalVehiclesForReturn,
            'is_day_off' => false
        ];
    }

    /**
     * Determine which vehicle type a specific variant represents
     */
    private function getVehicleTypeForVariant(PackageVariant $packageVariant): ?VehicleType
    {
        $package = $packageVariant->package;

        // For ATV/UTV Trail Rides package
        if ($package->name === 'ATV/UTV Trail Rides') {
            if ($packageVariant->capacity == 1) {
                // 1 person variant = ATV
                return VehicleType::where('name', 'ATV')->first();
            } elseif ($packageVariant->capacity == 2) {
                // 2 person variant = UTV
                return VehicleType::where('name', 'UTV')->first();
            }
        }

        // For UTV Trail Rides 2 package
        if ($package->name === 'UTV Trail Rides 2') {
            return VehicleType::where('name', 'UTV')->first();
        }

        // For Regular Package
        if ($package->name === 'Regular Package') {
            return VehicleType::where('name', 'Regular')->first();
        }

        // Fallback: return the first vehicle type associated with the package
        return $package->vehicleTypes->first();
    }

    /**
     * Get booked vehicles count for a specific vehicle type and date
     * Note: Now respects schedule_slot_id for per-slot availability
     */
    private function getBookedVehiclesForDate(int $vehicleTypeId, string $date, int $scheduleSlotId = null): int
    {
        // First, try to get booked vehicles from new reservation items
        $newBookingsQuery = Reservation::join('reservation_items', 'reservations.id', '=', 'reservation_items.reservation_id')
            ->join('package_variants', 'reservation_items.package_variant_id', '=', 'package_variants.id')
            ->join('packages', 'package_variants.package_id', '=', 'packages.id')
            ->join('package_vehicle_types', 'packages.id', '=', 'package_vehicle_types.package_id')
            ->where('package_vehicle_types.vehicle_type_id', $vehicleTypeId)
            ->where('reservations.date', $date)
            ->whereIn('reservations.booking_status', ['pending', 'confirmed']);

        // Add schedule_slot_id filter for per-slot availability
        if ($scheduleSlotId) {
            $newBookingsQuery->where('reservations.schedule_slot_id', $scheduleSlotId);
        }

        $newBookedCount = $newBookingsQuery->sum('reservation_items.qty');

        // Also check for old reservations that don't have reservation items
        $oldBookingsQuery = Reservation::join('package_variants', 'reservations.package_variant_id', '=', 'package_variants.id')
            ->join('packages', 'package_variants.package_id', '=', 'packages.id')
            ->join('package_vehicle_types', 'packages.id', '=', 'package_vehicle_types.package_id')
            ->where('package_vehicle_types.vehicle_type_id', $vehicleTypeId)
            ->where('reservations.date', $date)
            ->whereIn('reservations.booking_status', ['pending', 'confirmed'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('reservation_items')
                    ->whereRaw('reservation_items.reservation_id = reservations.id');
            });

        // Add schedule_slot_id filter for old bookings as well
        if ($scheduleSlotId) {
            $oldBookingsQuery->where('reservations.schedule_slot_id', $scheduleSlotId);
        }

        $oldBookedCount = $oldBookingsQuery->sum('reservations.party_size');

        $totalBookedCount = $newBookedCount + $oldBookedCount;

        // Debug logging
        Log::info('Booked vehicles calculation (per-slot model)', [
            'vehicle_type_id' => $vehicleTypeId,
            'date' => $date,
            'schedule_slot_id' => $scheduleSlotId,
            'new_booked_count' => $newBookedCount,
            'old_booked_count' => $oldBookedCount,
            'total_booked_count' => $totalBookedCount,
            'note' => $scheduleSlotId ? 'Vehicles calculated per time slot' : 'Vehicles shared across all time slots for this date'
        ]);

        return $totalBookedCount;
    }

    /**
     * Get booked capacity for a specific package and date
     * Note: Now respects schedule_slot_id for per-slot availability
     */
    private function getBookedCapacityForPackage(int $packageId, string $date, int $scheduleSlotId = null): int
    {
        // First, try to get booked capacity from new reservation items
        $newBookingsQuery = Reservation::join('reservation_items', 'reservations.id', '=', 'reservation_items.reservation_id')
            ->join('package_variants', 'reservation_items.package_variant_id', '=', 'package_variants.id')
            ->where('package_variants.package_id', $packageId)
            ->where('reservations.date', $date)
            ->whereIn('reservations.booking_status', ['pending', 'confirmed']);

        // Add schedule_slot_id filter for per-slot availability
        if ($scheduleSlotId) {
            $newBookingsQuery->where('reservations.schedule_slot_id', $scheduleSlotId);
        }

        $newBookedCapacity = $newBookingsQuery->sum(DB::raw('reservation_items.qty * package_variants.capacity'));

        // Also check for old reservations that don't have reservation items
        $oldBookingsQuery = Reservation::join('package_variants', 'reservations.package_variant_id', '=', 'package_variants.id')
            ->where('package_variants.package_id', $packageId)
            ->where('reservations.date', $date)
            ->whereIn('reservations.booking_status', ['pending', 'confirmed'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('reservation_items')
                    ->whereRaw('reservation_items.reservation_id = reservations.id');
            });

        // Add schedule_slot_id filter for old bookings as well
        if ($scheduleSlotId) {
            $oldBookingsQuery->where('reservations.schedule_slot_id', $scheduleSlotId);
        }

        $oldBookedCapacity = $oldBookingsQuery->sum('reservations.party_size');

        $totalBookedCapacity = $newBookedCapacity + $oldBookedCapacity;

        // Cart items will be handled in the final availability calculation

        // Debug logging
        Log::info('Booked capacity calculation for package', [
            'package_id' => $packageId,
            'date' => $date,
            'schedule_slot_id' => $scheduleSlotId,
            'new_booked_capacity' => $newBookedCapacity,
            'old_booked_capacity' => $oldBookedCapacity,
            'total_booked_capacity' => $totalBookedCapacity,
            'note' => $scheduleSlotId ? 'Capacity calculated per time slot' : 'Capacity calculated at package level'
        ]);

        return $totalBookedCapacity;
    }

    /**
     * Calculate available vehicles for a specific vehicle type on a given date
     */
    public function calculateAvailabilityForVehicleType(string $vehicleTypeName, string $date): array
    {
        // Find the vehicle type by name
        $vehicleType = VehicleType::where('name', $vehicleTypeName)
            ->where('is_active', true)
            ->first();

        if (!$vehicleType) {
            Log::info("Vehicle type not found: {$vehicleTypeName}");
            return [
                'total_available' => 0,
                'total_vehicles' => 0,
                'booked_vehicles' => 0,
                'vehicle_type' => $vehicleTypeName,
                'is_available' => false,
            ];
        }

        Log::info("Found vehicle type: {$vehicleType->name} (ID: {$vehicleType->id})");

        // Get total active vehicles of this type
        $totalVehicles = Vehicle::where('vehicle_type_id', $vehicleType->id)
            ->where('is_active', true)
            ->where(function ($query) use ($date) {
                $query->whereNull('op_start_date')
                    ->orWhere('op_start_date', '<=', $date);
            })
            ->count();

        Log::info("Total active vehicles for {$vehicleType->name}: {$totalVehicles}");

        // Get booked vehicles of this type for the date
        $bookedVehicles = $this->getBookedVehiclesForVehicleType($vehicleType->id, $date, null);

        Log::info("Booked vehicles for {$vehicleType->name} on {$date}: {$bookedVehicles}");

        $available = max(0, $totalVehicles - $bookedVehicles);

        Log::info("Available vehicles for {$vehicleType->name}: {$available}");

        return [
            'total_available' => $available,
            'total_vehicles' => $totalVehicles,
            'booked_vehicles' => $bookedVehicles,
            'vehicle_type' => $vehicleTypeName,
            'is_available' => $available > 0,
        ];
    }

    /**
     * Get booked vehicles count for a specific vehicle type and date
     */
    private function getBookedVehiclesForVehicleType(int $vehicleTypeId, string $date, int $scheduleSlotId = null): int
    {
        // First, try to get booked vehicles from new reservation items
        $newBookedCountQuery = Reservation::join('reservation_items', 'reservations.id', '=', 'reservation_items.reservation_id')
            ->join('package_variants', 'reservation_items.package_variant_id', '=', 'package_variants.id')
            ->join('packages', 'package_variants.package_id', '=', 'packages.id')
            ->join('package_vehicle_types', 'packages.id', '=', 'package_vehicle_types.package_id')
            ->where('package_vehicle_types.vehicle_type_id', $vehicleTypeId)
            ->where('reservations.date', $date)
            ->whereIn('reservations.booking_status', ['pending', 'confirmed']);

        // Add schedule_slot_id filter for per-slot availability
        if ($scheduleSlotId) {
            $newBookedCountQuery->where('reservations.schedule_slot_id', $scheduleSlotId);
        }

        $newBookedCount = $newBookedCountQuery->sum('reservation_items.qty');

        // Also check for old reservations that don't have reservation items
        $oldBookedCountQuery = Reservation::join('package_variants', 'reservations.package_variant_id', '=', 'package_variants.id')
            ->join('packages', 'package_variants.package_id', '=', 'packages.id')
            ->join('package_vehicle_types', 'packages.id', '=', 'package_vehicle_types.package_id')
            ->where('package_vehicle_types.vehicle_type_id', $vehicleTypeId)
            ->where('reservations.date', $date)
            ->whereIn('reservations.booking_status', ['pending', 'confirmed'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('reservation_items')
                    ->whereRaw('reservation_items.reservation_id = reservations.id');
            });

        // Add schedule_slot_id filter for old bookings as well
        if ($scheduleSlotId) {
            $oldBookedCountQuery->where('reservations.schedule_slot_id', $scheduleSlotId);
        }

        $oldBookedCount = $oldBookedCountQuery->sum('reservations.party_size');

        $totalBookedCount = $newBookedCount + $oldBookedCount;

        // Debug logging
        Log::info('Booked vehicles for vehicle type (per-slot model)', [
            'vehicle_type_id' => $vehicleTypeId,
            'date' => $date,
            'schedule_slot_id' => $scheduleSlotId,
            'new_booked_count' => $newBookedCount,
            'old_booked_count' => $oldBookedCount,
            'total_booked_count' => $totalBookedCount
        ]);

        return $totalBookedCount;
    }

    /**
     * Get availability for all package variants on a specific date
     */
    public function getAvailabilityForDate(string $date): array
    {
        $packageVariants = PackageVariant::with(['package.vehicleTypes', 'package.images'])
            ->where('is_active', true)
            ->get();

        $availability = [];

        foreach ($packageVariants as $variant) {
            $availability[$variant->id] = $this->calculateAvailabilityForPackageVariant($variant, $date);
        }

        return $availability;
    }

    /**
     * Get availability for a specific time slot on a date
     * Note: Now uses per-slot availability model - each time slot has independent capacity
     */
    public function getAvailabilityForTimeSlot(string $date, int $scheduleSlotId): array
    {
        $packageVariants = PackageVariant::with(['package.vehicleTypes', 'package.images'])
            ->where('is_active', true)
            ->get();

        $availability = [];

        foreach ($packageVariants as $variant) {
            // scheduleSlotId is now used in calculation for per-slot availability
            $availability[$variant->id] = $this->calculateAvailabilityForPackageVariant($variant, $date, $scheduleSlotId);
        }

        return $availability;
    }

    /**
     * Check if a specific booking can be made
     * Note: Now uses per-slot availability model - each time slot has independent capacity
     */
    public function canMakeBooking(
        PackageVariant $packageVariant,
        string $date,
        int $partySize,
        int $scheduleSlotId = null
    ): bool {
        // scheduleSlotId is now used in calculation for per-slot availability
        $availability = $this->calculateAvailabilityForPackageVariant($packageVariant, $date, $scheduleSlotId);

        return $availability['total_available'] >= $partySize;
    }

    /**
     * Get vehicle type availability summary
     */
    public function getVehicleTypeAvailability(string $date): array
    {
        $vehicleTypes = VehicleType::where('is_active', true)->get();
        $availability = [];

        foreach ($vehicleTypes as $vehicleType) {
            $totalVehicles = Vehicle::where('vehicle_type_id', $vehicleType->id)
                ->where('is_active', true)
                ->where(function ($query) use ($date) {
                    $query->whereNull('op_start_date')
                        ->orWhere('op_start_date', '<=', $date);
                })
                ->count();

            $bookedVehicles = $this->getBookedVehiclesForDate($vehicleType->id, $date);

            $availability[$vehicleType->id] = [
                'vehicle_type' => $vehicleType,
                'total_vehicles' => $totalVehicles,
                'booked_vehicles' => $bookedVehicles,
                'available_vehicles' => max(0, $totalVehicles - $bookedVehicles),
                'utilization_percentage' => $totalVehicles > 0 ? round(($bookedVehicles / $totalVehicles) * 100, 2) : 0,
            ];
        }

        return $availability;
    }

    /**
     * Get detailed vehicle breakdown for admin dashboard
     */
    public function getDetailedVehicleBreakdown(string $date): array
    {
        $vehicleTypes = VehicleType::with(['vehicles' => function ($query) use ($date) {
            $query->where('is_active', true)
                ->where(function ($q) use ($date) {
                    $q->whereNull('op_start_date')
                        ->orWhere('op_start_date', '<=', $date);
                });
        }])->where('is_active', true)->get();

        $breakdown = [];

        foreach ($vehicleTypes as $vehicleType) {
            $totalVehicles = $vehicleType->vehicles->count();
            $bookedVehicles = $this->getBookedVehiclesForDate($vehicleType->id, $date);

            $breakdown[$vehicleType->id] = [
                'vehicle_type' => $vehicleType,
                'total_vehicles' => $totalVehicles,
                'booked_vehicles' => $bookedVehicles,
                'available_vehicles' => max(0, $totalVehicles - $bookedVehicles),
                'vehicles' => $vehicleType->vehicles,
                'utilization_percentage' => $totalVehicles > 0 ? round(($bookedVehicles / $totalVehicles) * 100, 2) : 0,
            ];
        }

        return $breakdown;
    }

    /**
     * Get booked capacity from cart items for a specific package and date
     */
    private function getCartBookedCapacityForPackage(int $packageId, string $date, int $scheduleSlotId = null, array $excludeCartItems = []): int
    {
        $cart = session()->get('cart', []);
        $cartBookedCapacity = 0;

        foreach ($cart as $key => $item) {
            // Skip if this cart item should be excluded (e.g., when editing the same item)
            if (in_array($key, $excludeCartItems)) {
                continue;
            }

            // Get the variant for this cart item
            $variant = PackageVariant::find($item['variant_id']);
            if (!$variant || $variant->package_id !== $packageId) {
                continue;
            }

            // Check if the date and slot match
            if ($item['date'] !== $date) {
                continue;
            }

            if ($scheduleSlotId && $item['slot_id'] != $scheduleSlotId) {
                continue;
            }

            // Add the capacity for this cart item
            $cartBookedCapacity += $item['quantity'] * $variant->capacity;
        }

        return $cartBookedCapacity;
    }
}
