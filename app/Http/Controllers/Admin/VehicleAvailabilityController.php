<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\VehicleAvailabilityService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VehicleAvailabilityController extends Controller
{
    protected $availabilityService;

    public function __construct(VehicleAvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        
        // Get detailed vehicle breakdown
        $vehicleBreakdown = $this->availabilityService->getDetailedVehicleBreakdown($date);
        
        // Get vehicle type availability summary
        $vehicleTypeAvailability = $this->availabilityService->getVehicleTypeAvailability($date);
        
        // Get availability for all package variants
        $packageAvailability = $this->availabilityService->getAvailabilityForDate($date);

        return view('admin.vehicle-availability', compact(
            'vehicleBreakdown',
            'vehicleTypeAvailability', 
            'packageAvailability',
            'date'
        ));
    }

    public function getAvailabilityForDate(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $scheduleSlotId = $request->get('schedule_slot_id');

        if ($scheduleSlotId) {
            $availability = $this->availabilityService->getAvailabilityForTimeSlot($date, $scheduleSlotId);
        } else {
            $availability = $this->availabilityService->getAvailabilityForDate($date);
        }

        return response()->json($availability);
    }

    public function getVehicleTypeBreakdown(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $breakdown = $this->availabilityService->getDetailedVehicleBreakdown($date);

        return response()->json($breakdown);
    }
}
