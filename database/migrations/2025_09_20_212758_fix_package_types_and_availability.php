<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, ensure all packages have a proper type
        // Update packages that might have null or incorrect type values
        DB::table('packages')
            ->whereNull('type')
            ->orWhere('type', '')
            ->update(['type' => 'regular']);

        // Update packages based on their names to ensure correct types
        DB::table('packages')
            ->where(function($query) {
                $query->where('name', 'like', '%ATV%')
                      ->orWhere('name', 'like', '%atv%')
                      ->orWhere('name', 'like', '%UTV%')
                      ->orWhere('name', 'like', '%utv%')
                      ->orWhere('name', 'like', '%Trail%')
                      ->orWhere('name', 'like', '%trail%');
            })
            ->update(['type' => 'atv']);

        // Ensure we have at least one regular package for testing
        $regularPackageExists = DB::table('packages')
            ->where('type', 'regular')
            ->where('is_active', true)
            ->exists();

        if (!$regularPackageExists) {
            // Create a sample regular package if none exists
            $packageId = DB::table('packages')->insertGetId([
                'name' => 'Regular Adventure Package',
                'subtitle' => 'General adventure activities',
                'type' => 'regular',
                'min_participants' => 1,
                'max_participants' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create a variant for the regular package
            $variantId = DB::table('package_variants')->insertGetId([
                'package_id' => $packageId,
                'variant_name' => 'Standard',
                'capacity' => 999, // High capacity for regular packages
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create pricing for the variant
            $prices = [
                ['package_variant_id' => $variantId, 'price_type' => 'weekday', 'amount' => 1000, 'original_amount' => 1000],
                ['package_variant_id' => $variantId, 'price_type' => 'weekend', 'amount' => 1200, 'original_amount' => 1200],
            ];

            foreach ($prices as $price) {
                $price['created_at'] = now();
                $price['updated_at'] = now();
                DB::table('variant_prices')->insert($price);
            }
        }

        // Ensure all package variants have proper capacity settings
        // For regular packages, set a high capacity to simulate unlimited availability
        DB::table('package_variants')
            ->join('packages', 'package_variants.package_id', '=', 'packages.id')
            ->where('packages.type', 'regular')
            ->where(function($query) {
                $query->whereNull('package_variants.capacity')
                      ->orWhere('package_variants.capacity', 0);
            })
            ->update(['package_variants.capacity' => 999]);

        // Ensure ATV/UTV packages have reasonable capacity limits
        DB::table('package_variants')
            ->join('packages', 'package_variants.package_id', '=', 'packages.id')
            ->whereIn('packages.type', ['atv', 'utv'])
            ->where(function($query) {
                $query->whereNull('package_variants.capacity')
                      ->orWhere('package_variants.capacity', 0);
            })
            ->update(['package_variants.capacity' => 10]);

        // Create default schedule slots if none exist
        $slotExists = DB::table('schedule_slots')->exists();
        if (!$slotExists) {
            $slots = [
                ['name' => 'Morning Session', 'report_time' => '08:30:00', 'start_time' => '09:00:00', 'end_time' => '12:00:00', 'sort_order' => 1, 'is_active' => true],
                ['name' => 'Afternoon Session', 'report_time' => '12:30:00', 'start_time' => '13:00:00', 'end_time' => '16:00:00', 'sort_order' => 2, 'is_active' => true],
                ['name' => 'Evening Session', 'report_time' => '16:30:00', 'start_time' => '17:00:00', 'end_time' => '20:00:00', 'sort_order' => 3, 'is_active' => true],
            ];

            foreach ($slots as $slot) {
                $slot['created_at'] = now();
                $slot['updated_at'] = now();
                DB::table('schedule_slots')->insert($slot);
            }
        }

        // Create a default slot preset if none exists
        $presetExists = DB::table('slot_presets')->exists();
        if (!$presetExists) {
            $presetId = DB::table('slot_presets')->insertGetId([
                'name' => 'Default Schedule',
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Associate all active schedule slots with the default preset
            $slotIds = DB::table('schedule_slots')
                ->where('is_active', true)
                ->pluck('id');

            foreach ($slotIds as $slotId) {
                DB::table('slot_preset_items')->insert([
                    'slot_preset_id' => $presetId,
                    'schedule_slot_id' => $slotId,
                    'sort_order' => $slotId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is mostly data fixes, so we don't need to reverse it
        // The data changes are safe and improve the system
    }
};