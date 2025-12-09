<?php

namespace Database\Seeders;

use Exception;
use App\Models\Image;
use App\Models\Package;
use App\Models\Vehicle;
use App\Models\PriceType;
use App\Models\RiderType;
use App\Models\VehicleType;
use App\Models\PackageType;
use App\Models\PackagePrice;
use App\Models\ScheduleSlot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class AtvUtvPackageSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding ATV/UTV Packages with complete data...');

        try {
            // 1. Create/Update Package Types
            $this->seedPackageTypes();

            // 2. Create/Update Vehicle Types
            $this->seedVehicleTypes();

            // 3. Create/Update Rider Types
            $this->seedRiderTypes();

            // 4. Create/Update Price Types
            $this->seedPriceTypes();

            // 5. Create/Update Schedule Slots
            $this->seedScheduleSlots();

            // 6. Create/Update Vehicles
            $this->seedVehicles();

            // 7. Create/Update Packages with Pricing
            $this->seedPackages();

            $this->command->info('ATV/UTV Package seeding completed successfully!');

        } catch (Exception $e) {
            $this->command->error('Error seeding ATV/UTV packages: ' . $e->getMessage());
            Log::error('ATV/UTV package seeding failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }

    private function seedPackageTypes(): void
    {
        $this->command->info('  → Seeding package types...');

        $types = [
            ['name' => 'ATV/UTV', 'slug' => 'atv-utv', 'is_active' => true],
            ['name' => 'Regular Adventures', 'slug' => 'regular-adventures', 'is_active' => true],
            ['name' => 'Premium Experiences', 'slug' => 'premium-experiences', 'is_active' => true],
        ];

        foreach ($types as $type) {
            PackageType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }

    private function seedVehicleTypes(): void
    {
        $this->command->info('  → Seeding vehicle types...');

        $types = [
            [
                'name' => 'ATV',
                'slug' => 'atv',
                'description' => 'All-Terrain Vehicle - Single or double rider capable',
                'max_capacity' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'UTV',
                'slug' => 'utv',
                'description' => 'Utility Task Vehicle - Side-by-side seating',
                'max_capacity' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Regular',
                'slug' => 'regular',
                'description' => 'Regular adventure vehicles',
                'max_capacity' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            VehicleType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }

    private function seedRiderTypes(): void
    {
        $this->command->info('  → Seeding rider types...');

        $types = [
            ['name' => 'Single Rider', 'slug' => 'single-rider'],
            ['name' => 'Double Rider', 'slug' => 'double-rider'],
            ['name' => 'Adventure Tour', 'slug' => 'adventure-tour'],
            ['name' => 'Premium Single', 'slug' => 'premium-single'],
            ['name' => 'Premium Double', 'slug' => 'premium-double'],
        ];

        foreach ($types as $type) {
            RiderType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }

    private function seedPriceTypes(): void
    {
        $this->command->info('  → Seeding price types...');

        $types = [
            ['name' => 'Weekday', 'slug' => 'weekday', 'is_active' => true],
            ['name' => 'Weekend', 'slug' => 'weekend', 'is_active' => true],
        ];

        foreach ($types as $type) {
            PriceType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }

    private function seedScheduleSlots(): void
    {
        $this->command->info('  → Seeding schedule slots...');

        $slots = [
            [
                'name' => 'Morning Session',
                'report_time' => '08:30:00',
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Mid-Morning Session',
                'report_time' => '10:30:00',
                'start_time' => '11:00:00',
                'end_time' => '13:00:00',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Afternoon Session',
                'report_time' => '13:30:00',
                'start_time' => '14:00:00',
                'end_time' => '16:00:00',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Late Afternoon Session',
                'report_time' => '15:30:00',
                'start_time' => '16:00:00',
                'end_time' => '18:00:00',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Evening Session',
                'report_time' => '17:30:00',
                'start_time' => '18:00:00',
                'end_time' => '20:00:00',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($slots as $slot) {
            ScheduleSlot::updateOrCreate(
                ['name' => $slot['name']],
                $slot
            );
        }
    }

    private function seedVehicles(): void
    {
        $this->command->info('  → Seeding vehicles...');

        $atvType = VehicleType::where('slug', 'atv')->first();
        $utvType = VehicleType::where('slug', 'utv')->first();

        if (!$atvType || !$utvType) {
            throw new Exception('Vehicle types not found. Ensure seedVehicleTypes ran first.');
        }

        // ATV Vehicles
        $atvVehicles = [
            ['name' => 'ATV-001', 'details' => 'Honda TRX 450R - Red', 'is_active' => true],
            ['name' => 'ATV-002', 'details' => 'Yamaha Raptor 700 - Blue', 'is_active' => true],
            ['name' => 'ATV-003', 'details' => 'Can-Am DS 450 - Yellow', 'is_active' => true],
            ['name' => 'ATV-004', 'details' => 'Polaris Scrambler 850 - Black', 'is_active' => true],
            ['name' => 'ATV-005', 'details' => 'Kawasaki KFX 450R - Green', 'is_active' => true],
            ['name' => 'ATV-006', 'details' => 'Suzuki QuadSport Z400 - White', 'is_active' => true],
        ];

        foreach ($atvVehicles as $vehicle) {
            Vehicle::updateOrCreate(
                ['name' => $vehicle['name']],
                array_merge($vehicle, ['vehicle_type_id' => $atvType->id])
            );
        }

        // UTV Vehicles
        $utvVehicles = [
            ['name' => 'UTV-001', 'details' => 'Polaris RZR XP 1000 - Orange', 'is_active' => true],
            ['name' => 'UTV-002', 'details' => 'Can-Am Maverick X3 - Red', 'is_active' => true],
            ['name' => 'UTV-003', 'details' => 'Yamaha YXZ1000R - Blue', 'is_active' => true],
            ['name' => 'UTV-004', 'details' => 'Honda Talon 1000R - Black', 'is_active' => true],
        ];

        foreach ($utvVehicles as $vehicle) {
            Vehicle::updateOrCreate(
                ['name' => $vehicle['name']],
                array_merge($vehicle, ['vehicle_type_id' => $utvType->id])
            );
        }

        $this->command->info("    Created/Updated " . (count($atvVehicles) + count($utvVehicles)) . " vehicles");
    }

    private function seedPackages(): void
    {
        $this->command->info('  → Seeding packages with pricing...');

        // Get required data
        $atvType = VehicleType::where('slug', 'atv')->first();
        $utvType = VehicleType::where('slug', 'utv')->first();

        $singleRider = RiderType::where('slug', 'single-rider')->first();
        $doubleRider = RiderType::where('slug', 'double-rider')->first();

        $weekdayPrice = PriceType::where('slug', 'weekday')->first();
        $weekendPrice = PriceType::where('slug', 'weekend')->first();

        $atvUtvPackageType = PackageType::where('slug', 'atv-utv')->first();

        if (!$atvType || !$utvType || !$singleRider || !$doubleRider || !$weekdayPrice || !$weekendPrice) {
            throw new Exception('Required seed data not found. Ensure all prerequisite seeders ran.');
        }

        $packages = [
            [
                'name' => 'ATV Trail Adventure',
                'subtitle' => 'Experience the thrill of ATV riding on scenic trails',
                'type' => 'atv',
                'package_type_id' => $atvUtvPackageType?->id,
                'min_participants' => 1,
                'max_participants' => 10,
                'display_starting_price' => 1200.00,
                'is_active' => true,
                'notes' => 'Perfect for beginners and experienced riders alike.',
                'details' => 'Ride through forest trails and open fields on our well-maintained ATVs. Safety gear and brief training included.',
                'vehicle_type_ids' => [$atvType->id],
                'pricing' => [
                    [
                        'rider_type_id' => $singleRider->id,
                        'prices' => [
                            ['price_type_id' => $weekdayPrice->id, 'amount' => 1200.00],
                            ['price_type_id' => $weekendPrice->id, 'amount' => 1500.00],
                        ]
                    ],
                    [
                        'rider_type_id' => $doubleRider->id,
                        'prices' => [
                            ['price_type_id' => $weekdayPrice->id, 'amount' => 1800.00],
                            ['price_type_id' => $weekendPrice->id, 'amount' => 2200.00],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'UTV Family Explorer',
                'subtitle' => 'Family-friendly UTV adventure for all ages',
                'type' => 'utv',
                'package_type_id' => $atvUtvPackageType?->id,
                'min_participants' => 2,
                'max_participants' => 8,
                'display_starting_price' => 2500.00,
                'is_active' => true,
                'notes' => 'Ideal for families with children (min age 5 years with adult).',
                'details' => 'Explore scenic routes in our comfortable side-by-side UTVs. Perfect for families wanting an adventure together.',
                'vehicle_type_ids' => [$utvType->id],
                'pricing' => [
                    [
                        'rider_type_id' => $singleRider->id,
                        'prices' => [
                            ['price_type_id' => $weekdayPrice->id, 'amount' => 2500.00],
                            ['price_type_id' => $weekendPrice->id, 'amount' => 3000.00],
                        ]
                    ],
                    [
                        'rider_type_id' => $doubleRider->id,
                        'prices' => [
                            ['price_type_id' => $weekdayPrice->id, 'amount' => 3500.00],
                            ['price_type_id' => $weekendPrice->id, 'amount' => 4200.00],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Extreme ATV Challenge',
                'subtitle' => 'For experienced riders seeking an adrenaline rush',
                'type' => 'atv',
                'package_type_id' => $atvUtvPackageType?->id,
                'min_participants' => 1,
                'max_participants' => 6,
                'display_starting_price' => 2000.00,
                'is_active' => true,
                'notes' => 'Advanced course - requires motorcycle license. Age 18+.',
                'details' => 'Tackle challenging terrain, steep hills, and mud trails. For thrill-seekers who want the ultimate ATV experience.',
                'vehicle_type_ids' => [$atvType->id],
                'pricing' => [
                    [
                        'rider_type_id' => $singleRider->id,
                        'prices' => [
                            ['price_type_id' => $weekdayPrice->id, 'amount' => 2000.00],
                            ['price_type_id' => $weekendPrice->id, 'amount' => 2500.00],
                        ]
                    ],
                    [
                        'rider_type_id' => $doubleRider->id,
                        'prices' => [
                            ['price_type_id' => $weekdayPrice->id, 'amount' => 3000.00],
                            ['price_type_id' => $weekendPrice->id, 'amount' => 3800.00],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'ATV/UTV Combo Experience',
                'subtitle' => 'Try both ATV and UTV in one session',
                'type' => 'atv',
                'package_type_id' => $atvUtvPackageType?->id,
                'min_participants' => 1,
                'max_participants' => 4,
                'display_starting_price' => 3500.00,
                'is_active' => true,
                'notes' => 'Experience both vehicles - ATV solo riding and UTV group riding.',
                'details' => 'Start with an ATV adventure, then switch to a comfortable UTV for the second half. Best of both worlds!',
                'vehicle_type_ids' => [$atvType->id, $utvType->id],
                'pricing' => [
                    [
                        'rider_type_id' => $singleRider->id,
                        'prices' => [
                            ['price_type_id' => $weekdayPrice->id, 'amount' => 3500.00],
                            ['price_type_id' => $weekendPrice->id, 'amount' => 4200.00],
                        ]
                    ],
                    [
                        'rider_type_id' => $doubleRider->id,
                        'prices' => [
                            ['price_type_id' => $weekdayPrice->id, 'amount' => 5000.00],
                            ['price_type_id' => $weekendPrice->id, 'amount' => 6000.00],
                        ]
                    ]
                ]
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($packages as $packageData) {
            $pricing = $packageData['pricing'] ?? [];
            $vehicleTypeIds = $packageData['vehicle_type_ids'] ?? [];

            unset($packageData['pricing'], $packageData['vehicle_type_ids']);

            $package = Package::updateOrCreate(
                ['name' => $packageData['name']],
                $packageData
            );

            if ($package->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }

            // Attach vehicle types
            if (!empty($vehicleTypeIds)) {
                $package->vehicleTypes()->sync($vehicleTypeIds);
            }

            // Clear existing prices and create new ones
            $package->packagePrices()->delete();

            foreach ($pricing as $pricingOption) {
                foreach ($pricingOption['prices'] as $priceData) {
                    PackagePrice::create([
                        'package_id' => $package->id,
                        'rider_type_id' => $pricingOption['rider_type_id'],
                        'price_type_id' => $priceData['price_type_id'],
                        'price' => $priceData['amount'],
                        'is_active' => true,
                    ]);
                }
            }
        }

        $this->command->info("    Created {$created} packages, Updated {$updated} packages");
    }
}
