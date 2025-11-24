<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\PriceOverride;
use Carbon\Carbon;

class CalendarService
{
    public function getDateStatus($variantId, $date): string
    {
        $availability = Availability::where([
            'package_variant_id' => $variantId,
            'date' => $date
        ])->first();

        $priceOverride = PriceOverride::where([
            'package_variant_id' => $variantId,
            'date' => $date
        ])->first();

        if ($availability && $availability->is_day_off) {
            return 'day-off';
        }

        if ($availability && $availability->capacity_reserved >= $availability->capacity_total) {
            return 'booked';
        }

        if ($priceOverride) {
            return $priceOverride->price_tag; // 'premium' or 'discounted'
        }

        return 'available';
    }

    public function getMonthData($packageId, $month, $year): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $availabilities = Availability::whereHas('packageVariant', function($query) use ($packageId) {
            $query->where('package_id', $packageId);
        })
        ->whereBetween('date', [$startDate, $endDate])
        ->get()
        ->groupBy(function($item) {
            return $item->date->format('Y-m-d'); // Group by date only, not datetime
        })
        ->map(function($group) {
            return $group->first(); // Return first record for each date
        });

        $priceOverrides = PriceOverride::whereHas('packageVariant', function($query) use ($packageId) {
            $query->where('package_id', $packageId);
        })
        ->whereBetween('date', [$startDate, $endDate])
        ->get()
        ->groupBy(function($item) {
            return $item->date->format('Y-m-d'); // Group by date only, not datetime
        })
        ->map(function($group) {
            return $group->first(); // Return first record for each date
        });

        return [
            'availabilities' => $availabilities,
            'price_overrides' => $priceOverrides
        ];
    }

    public function getCalendarDataForPackage($packageId, $month, $year): array
    {
        $monthData = $this->getMonthData($packageId, $month, $year);
        
        $calendarData = [];
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            
            $calendarData[$dateString] = [
                'date' => $dateString,
                'availability' => $monthData['availabilities'][$dateString] ?? null,
                'price_override' => $monthData['price_overrides'][$dateString] ?? null,
                'status' => $this->getDateStatusForPackage($packageId, $dateString)
            ];
        }
        
        return $calendarData;
    }

    private function getDateStatusForPackage($packageId, $date): string
    {
        // Get the first variant for the package
        $variant = \App\Models\PackageVariant::where('package_id', $packageId)->first();
        
        if (!$variant) {
            return 'available';
        }
        
        return $this->getDateStatus($variant->id, $date);
    }
}
