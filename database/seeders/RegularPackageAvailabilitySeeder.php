<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Package;
use App\Models\PackageVariant;
use App\Models\VariantPrice;
use App\Models\ScheduleSlot;
use App\Models\SlotPreset;
use Illuminate\Support\Facades\DB;

class RegularPackageAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Setting up regular package availability data...');

        // Create or update regular packages
        $regularPackages = [
            [
                'name' => 'Adventure Activities',
                'subtitle' => 'General adventure and outdoor activities',
                'type' => 'regular',
                'min_participants' => 1,
                'max_participants' => 50,
            ],
            [
                'name' => 'Group Activities',
                'subtitle' => 'Team building and group activities',
                'type' => 'regular',
                'min_participants' => 5,
                'max_participants' => 30,
            ],
            [
                'name' => 'Family Package',
                'subtitle' => 'Family-friendly adventure activities',
                'type' => 'regular',
                'min_participants' => 2,
                'max_participants' => 20,
            ],
        ];

        foreach ($regularPackages as $packageData) {
            $package = Package::updateOrCreate(
                ['name' => $packageData['name']],
                array_merge($packageData, [
                    'is_active' => true,
                ])
            );

            $this->command->info("Created/Updated package: {$package->name}");

            // Create package variants for each regular package
            $variants = [
                [
                    'variant_name' => 'Standard',
                    'capacity' => 999, // High capacity for regular packages
                ],
                [
                    'variant_name' => 'Premium',
                    'capacity' => 999, // High capacity for regular packages
                ],
            ];

            foreach ($variants as $variantData) {
                $variant = PackageVariant::updateOrCreate(
                    [
                        'package_id' => $package->id,
                        'variant_name' => $variantData['variant_name'],
                    ],
                    array_merge($variantData, [
                        'is_active' => true,
                    ])
                );

                $this->command->info("  Created/Updated variant: {$variant->variant_name}");

                // Create pricing for each variant
                $prices = [
                    [
                        'price_type' => 'weekday',
                        'amount' => $variantData['variant_name'] === 'Premium' ? 1500 : 1000,
                    ],
                    [
                        'price_type' => 'weekend',
                        'amount' => $variantData['variant_name'] === 'Premium' ? 1800 : 1200,
                    ],
                ];

                foreach ($prices as $priceData) {
                    VariantPrice::updateOrCreate(
                        [
                            'package_variant_id' => $variant->id,
                            'price_type' => $priceData['price_type'],
                        ],
                        array_merge($priceData, [
                            'original_amount' => $priceData['amount'],
                        ])
                    );
                }
            }
        }

        // Ensure we have proper schedule slots
        $this->ensureScheduleSlots();

        // Ensure we have a default slot preset
        $this->ensureDefaultSlotPreset();

        $this->command->info('Regular package availability setup completed!');
    }

    private function ensureScheduleSlots(): void
    {
        $slots = [
            ['name' => 'Morning Session', 'report_time' => '08:30:00', 'start_time' => '09:00:00', 'end_time' => '12:00:00', 'sort_order' => 1],
            ['name' => 'Afternoon Session', 'report_time' => '12:30:00', 'start_time' => '13:00:00', 'end_time' => '16:00:00', 'sort_order' => 2],
            ['name' => 'Evening Session', 'report_time' => '16:30:00', 'start_time' => '17:00:00', 'end_time' => '20:00:00', 'sort_order' => 3],
            ['name' => 'Full Day', 'report_time' => '08:30:00', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'sort_order' => 4],
        ];

        foreach ($slots as $slotData) {
            ScheduleSlot::updateOrCreate(
                ['name' => $slotData['name']],
                array_merge($slotData, [
                    'is_active' => true,
                ])
            );
        }

        $this->command->info('Schedule slots ensured');
    }

    private function ensureDefaultSlotPreset(): void
    {
        $preset = SlotPreset::updateOrCreate(
            ['name' => 'Default Schedule'],
            [
                'is_default' => true,
                'is_active' => true,
            ]
        );

        // Get all active schedule slots
        $slots = ScheduleSlot::where('is_active', true)->get();

        // Sync the slots with the preset (this will handle the pivot table)
        $preset->scheduleSlots()->sync($slots->pluck('id')->toArray());

        $this->command->info('Default slot preset ensured');
    }
}