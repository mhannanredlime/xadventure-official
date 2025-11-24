<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\PackageVariant;
use App\Models\ScheduleSlot;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $packageVariants = PackageVariant::with('package')->where('is_active', true)->get();
        $scheduleSlots = ScheduleSlot::orderBy('sort_order')->get();
        
        $selectedVariant = $request->get('package_variant_id');
        $selectedSlot = $request->get('schedule_slot_id');
        $selectedMonth = $request->get('month', now()->format('Y-m'));
        
        $query = Availability::with(['packageVariant.package', 'scheduleSlot']);
        
        if ($selectedVariant) {
            $query->where('package_variant_id', $selectedVariant);
        }
        
        if ($selectedSlot) {
            $query->where('schedule_slot_id', $selectedSlot);
        }
        
        // Filter by month
        $startDate = Carbon::parse($selectedMonth)->startOfMonth();
        $endDate = Carbon::parse($selectedMonth)->endOfMonth();
        $query->whereBetween('date', [$startDate, $endDate]);
        
        $availabilities = $query->orderBy('date')->orderBy('schedule_slot_id')->get();
        
        return view('admin.availabilities.index', compact(
            'availabilities', 
            'packageVariants', 
            'scheduleSlots', 
            'selectedVariant', 
            'selectedSlot', 
            'selectedMonth'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_variant_id' => 'required|exists:package_variants,id',
            'schedule_slot_id' => 'required|exists:schedule_slots,id',
            'date' => 'required|date',
            'capacity_total' => 'required|integer|min:0',
            'capacity_reserved' => 'required|integer|min:0',
            'is_day_off' => 'boolean',
            'price_override' => 'nullable|numeric|min:0',
            'price_tag' => 'required|in:regular,premium,discounted',
        ]);

        Availability::updateOrCreate(
            [
                'package_variant_id' => $validated['package_variant_id'],
                'schedule_slot_id' => $validated['schedule_slot_id'],
                'date' => $validated['date'],
            ],
            $validated
        );

        return redirect()->back()->with('success', 'Availability updated successfully.');
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'package_variant_id' => 'required|exists:package_variants,id',
            'schedule_slot_id' => 'required|exists:schedule_slots,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'capacity_total' => 'required|integer|min:0',
            'is_day_off' => 'boolean',
            'price_override' => 'nullable|numeric|min:0',
            'price_tag' => 'required|in:regular,premium,discounted',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            // Skip if it's a day off
            if ($validated['is_day_off']) {
                Availability::updateOrCreate(
                    [
                        'package_variant_id' => $validated['package_variant_id'],
                        'schedule_slot_id' => $validated['schedule_slot_id'],
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'capacity_total' => 0,
                        'capacity_reserved' => 0,
                        'is_day_off' => true,
                        'price_override' => null,
                        'price_tag' => 'regular',
                    ]
                );
            } else {
                Availability::updateOrCreate(
                    [
                        'package_variant_id' => $validated['package_variant_id'],
                        'schedule_slot_id' => $validated['schedule_slot_id'],
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'capacity_total' => $validated['capacity_total'],
                        'capacity_reserved' => 0,
                        'is_day_off' => false,
                        'price_override' => $validated['price_override'],
                        'price_tag' => $validated['price_tag'],
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Bulk availability updated successfully.');
    }

    public function destroy(Availability $availability)
    {
        $availability->delete();
        return redirect()->back()->with('success', 'Availability deleted successfully.');
    }
}
