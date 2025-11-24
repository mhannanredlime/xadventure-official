<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScheduleSlot;
use App\Models\SlotPreset;
use App\Models\SlotPresetItem;
use Illuminate\Support\Facades\DB;

class CleanupScheduleSlotsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Cleaning up schedule slots system...');

        DB::transaction(function () {
            // Step 1: Deactivate all existing slots
            ScheduleSlot::query()->update(['is_active' => false]);

            // Step 2: Delete all seasonal slots (Winter, Summer, etc.)
            ScheduleSlot::where('name', 'like', '%Winter%')->delete();
            ScheduleSlot::where('name', 'like', '%Summer%')->delete();
            ScheduleSlot::where('name', 'like', '%Spring%')->delete();
            ScheduleSlot::where('name', 'like', '%Fall%')->delete();
            ScheduleSlot::where('name', 'like', '%Autumn%')->delete();

            // Step 3: Create exactly 10 continuous slots from 9 AM to 7 PM
            $slots = [
                [
                    'name' => '9 AM - 10 AM',
                    'report_time' => '08:45:00',
                    'start_time' => '09:00:00',
                    'end_time' => '10:00:00',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'name' => '10 AM - 11 AM',
                    'report_time' => '09:45:00',
                    'start_time' => '10:00:00',
                    'end_time' => '11:00:00',
                    'sort_order' => 2,
                    'is_active' => true,
                ],
                [
                    'name' => '11 AM - 12 PM',
                    'report_time' => '10:45:00',
                    'start_time' => '11:00:00',
                    'end_time' => '12:00:00',
                    'sort_order' => 3,
                    'is_active' => true,
                ],
                [
                    'name' => '12 PM - 1 PM',
                    'report_time' => '11:45:00',
                    'start_time' => '12:00:00',
                    'end_time' => '13:00:00',
                    'sort_order' => 4,
                    'is_active' => true,
                ],
                [
                    'name' => '1 PM - 2 PM',
                    'report_time' => '12:45:00',
                    'start_time' => '13:00:00',
                    'end_time' => '14:00:00',
                    'sort_order' => 5,
                    'is_active' => true,
                ],
                [
                    'name' => '2 PM - 3 PM',
                    'report_time' => '13:45:00',
                    'start_time' => '14:00:00',
                    'end_time' => '15:00:00',
                    'sort_order' => 6,
                    'is_active' => true,
                ],
                [
                    'name' => '3 PM - 4 PM',
                    'report_time' => '14:45:00',
                    'start_time' => '15:00:00',
                    'end_time' => '16:00:00',
                    'sort_order' => 7,
                    'is_active' => true,
                ],
                [
                    'name' => '4 PM - 5 PM',
                    'report_time' => '15:45:00',
                    'start_time' => '16:00:00',
                    'end_time' => '17:00:00',
                    'sort_order' => 8,
                    'is_active' => true,
                ],
                [
                    'name' => '5 PM - 6 PM',
                    'report_time' => '16:45:00',
                    'start_time' => '17:00:00',
                    'end_time' => '18:00:00',
                    'sort_order' => 9,
                    'is_active' => true,
                ],
                [
                    'name' => '6 PM - 7 PM',
                    'report_time' => '17:45:00',
                    'start_time' => '18:00:00',
                    'end_time' => '19:00:00',
                    'sort_order' => 10,
                    'is_active' => true,
                ],
            ];

            foreach ($slots as $slotData) {
                ScheduleSlot::updateOrCreate(
                    [
                        'name' => $slotData['name']
                    ],
                    [
                        'report_time' => $slotData['report_time'],
                        'start_time' => $slotData['start_time'],
                        'end_time' => $slotData['end_time'],
                        'sort_order' => $slotData['sort_order'],
                        'is_active' => $slotData['is_active'],
                    ]
                );
            }

            // Step 4: Clean up slot presets
            // Remove all existing presets
            SlotPreset::query()->delete();

            // Create a single standard preset
            $standardPreset = SlotPreset::create([
                'name' => 'Standard Timing',
                'is_active' => true,
                'is_default' => true,
            ]);

            // Get all active slots
            $activeSlots = ScheduleSlot::where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            // Create preset items for all slots
            foreach ($activeSlots as $slot) {
                SlotPresetItem::create([
                    'slot_preset_id' => $standardPreset->id,
                    'schedule_slot_id' => $slot->id,
                    'sort_order' => $slot->sort_order,
                ]);
            }

            $this->command->info('Schedule slots cleanup completed successfully!');
            $this->command->info('Created ' . count($slots) . ' standard time slots from 9 AM to 7 PM');
            $this->command->info('Removed all seasonal variations');
            $this->command->info('Updated slot presets to use only standard timing');
        });
    }
}
