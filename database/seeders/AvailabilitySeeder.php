<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;
use App\Models\ScheduleSlot;
use App\Models\Availability;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding availability data...');

        try {
            $packages = Package::where('is_active', true)->get();
            $slots = ScheduleSlot::where('is_active', true)->orderBy('sort_order')->get();

            if ($packages->isEmpty()) {
                $this->command->warn('No active packages found. Skipping availability seeding.');
                return;
            }

            if ($slots->isEmpty()) {
                $this->command->warn('No active schedule slots found. Skipping availability seeding.');
                return;
            }

            $this->command->info("Found {$packages->count()} packages and {$slots->count()} slots.");

            // Seed availability for the next 30 days (more comprehensive)
            $startDate = Carbon::today();
            $endDate = Carbon::today()->addDays(30);

            $this->command->info("Creating availability from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

            $createdCount = 0;
            $updatedCount = 0;

            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                $dateString = $date->format('Y-m-d');
                $isWeekend = $date->isWeekend();
                $isDayOff = $this->shouldBeDayOff($date);

                foreach ($packages as $package) {
                    foreach ($slots as $slot) {
                        $availability = Availability::updateOrCreate(
                            [
                                'date' => $dateString,
                                'package_id' => $package->id,
                                'schedule_slot_id' => $slot->id,
                            ],
                            [
                                'capacity_total' => $isDayOff ? 0 : $this->getCapacityForPackage($package, $isWeekend),
                                'capacity_reserved' => 0,
                                'is_day_off' => $isDayOff,
                            ]
                        );

                        if ($availability->wasRecentlyCreated) {
                            $createdCount++;
                        } else {
                            $updatedCount++;
                        }
                    }
                }
            }

            $this->command->info("Availability seeding completed: {$createdCount} created, {$updatedCount} updated.");

        } catch (\Exception $e) {
            $this->command->error('Error seeding availability: ' . $e->getMessage());
            Log::error('Availability seeding failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }

    /**
     * Determine if a date should be marked as a day off
     */
    private function shouldBeDayOff(Carbon $date): bool
    {
        // Mark every Monday as day off (example business rule)
        if ($date->isMonday()) {
            return true;
        }

        // Mark specific holidays (example)
        $holidays = [
            '12-25', // Christmas
            '01-01', // New Year
            '07-04', // Independence Day
        ];

        if (in_array($date->format('m-d'), $holidays)) {
            return true;
        }

        return false;
    }

    /**
     * Get capacity for a package based on day type
     */
    private function getCapacityForPackage(Package $package, bool $isWeekend): int
    {
        // Base capacity on package settings (e.g., max_participants or a default)
        // Since packages can be flexible, we'll assign a standard slot capacity
        $baseCapacity = 20; // Default slot capacity for the package

        // Weekend might have different capacity
        if ($isWeekend) {
            return min($baseCapacity + 5, 30); // Slightly higher capacity on weekends
        }

        return $baseCapacity;
    }
}


