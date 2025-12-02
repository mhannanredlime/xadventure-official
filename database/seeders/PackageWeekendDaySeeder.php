<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;
use App\Models\PackageWeekendDay;

class PackageWeekendDaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example: Define weekend days per package
        $packageWeekends = [
            // package_id => ['day1', 'day2', ...]
            1 => ['fri', 'sat'], // Package 1 → Fri, Sat
            2 => ['sat', 'sun'], // Package 2 → Sat, Sun
            3 => ['thu', 'fri'], // Package 3 → Thu, Fri
        ];

        foreach ($packageWeekends as $packageId => $days) {
            foreach ($days as $day) {
                PackageWeekendDay::updateOrCreate(
                    ['package_id' => $packageId, 'day' => $day],
                    ['is_active' => true]
                );
            }
        }
    }
}
