<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PromoCode;
use App\Models\Package;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding promo codes...');

        try {
            // Get packages for promo code associations
            $packages = Package::where('is_active', true)->get();

            if ($packages->isEmpty()) {
                $this->command->warn('No active packages found. Skipping promo code seeding.');
                return;
            }

            $promoCodes = [
                [
                    'code' => 'WELCOME2024',
                    'remarks' => 'Welcome discount for new customers',
                    'discount_type' => 'percentage',
                    'discount_value' => 15.00,
                    'min_spend' => 100.00,
                    'max_discount' => 500.00,
                    'usage_limit_total' => 100,
                    'usage_limit_per_user' => 1,
                    'status' => 'active',
                    'starts_at' => Carbon::now()->subDays(30),
                    'ends_at' => Carbon::now()->addDays(60),
                    'package_ids' => $packages->pluck('id')->toArray(),
                ],
                [
                    'code' => 'SUMMER25',
                    'remarks' => 'Summer season discount',
                    'discount_type' => 'percentage',
                    'discount_value' => 25.00,
                    'min_spend' => 200.00,
                    'max_discount' => 750.00,
                    'usage_limit_total' => 50,
                    'usage_limit_per_user' => 1,
                    'status' => 'active',
                    'starts_at' => Carbon::now()->subDays(15),
                    'ends_at' => Carbon::now()->addDays(45),
                    'package_ids' => $packages->pluck('id')->toArray(),
                ],
                [
                    'code' => 'WEEKDAY50',
                    'remarks' => 'Weekday special discount',
                    'discount_type' => 'fixed',
                    'discount_value' => 50.00,
                    'min_spend' => 150.00,
                    'max_discount' => 50.00,
                    'usage_limit_total' => 200,
                    'usage_limit_per_user' => 1,
                    'status' => 'active',
                    'starts_at' => Carbon::now()->subDays(60),
                    'ends_at' => Carbon::now()->addDays(90),
                    'package_ids' => $packages->pluck('id')->toArray(),
                ],
                [
                    'code' => 'FIRSTTIME',
                    'remarks' => 'First-time customer discount',
                    'discount_type' => 'percentage',
                    'discount_value' => 20.00,
                    'min_spend' => 100.00,
                    'max_discount' => 300.00,
                    'usage_limit_total' => 75,
                    'usage_limit_per_user' => 1,
                    'status' => 'active',
                    'starts_at' => Carbon::now()->subDays(45),
                    'ends_at' => Carbon::now()->addDays(75),
                    'package_ids' => $packages->pluck('id')->toArray(),
                ],
                [
                    'code' => 'GROUP10',
                    'remarks' => 'Group booking discount',
                    'discount_type' => 'percentage',
                    'discount_value' => 10.00,
                    'min_spend' => 500.00,
                    'max_discount' => 1000.00,
                    'usage_limit_total' => 30,
                    'usage_limit_per_user' => 1,
                    'status' => 'active',
                    'starts_at' => Carbon::now()->subDays(20),
                    'ends_at' => Carbon::now()->addDays(40),
                    'package_ids' => $packages->pluck('id')->toArray(),
                ],
                [
                    'code' => 'FLASH30',
                    'remarks' => 'Flash sale - limited time offer',
                    'discount_type' => 'percentage',
                    'discount_value' => 30.00,
                    'min_spend' => 100.00,
                    'max_discount' => 600.00,
                    'usage_limit_total' => 25,
                    'usage_limit_per_user' => 1,
                    'status' => 'active',
                    'starts_at' => Carbon::now()->subDays(5),
                    'ends_at' => Carbon::now()->addDays(10),
                    'package_ids' => $packages->pluck('id')->toArray(),
                ],
                [
                    'code' => 'LOYALTY15',
                    'remarks' => 'Loyalty program discount',
                    'discount_type' => 'percentage',
                    'discount_value' => 15.00,
                    'min_spend' => 100.00,
                    'max_discount' => 400.00,
                    'usage_limit_total' => 150,
                    'usage_limit_per_user' => 1,
                    'status' => 'active',
                    'starts_at' => Carbon::now()->subDays(90),
                    'ends_at' => Carbon::now()->addDays(120),
                    'package_ids' => $packages->pluck('id')->toArray(),
                ],
                [
                    'code' => 'EXPIRED_TEST',
                    'remarks' => 'Expired promo code for testing',
                    'discount_type' => 'percentage',
                    'discount_value' => 10.00,
                    'min_spend' => 50.00,
                    'max_discount' => 100.00,
                    'usage_limit_total' => 10,
                    'usage_limit_per_user' => 1,
                    'status' => 'expired',
                    'starts_at' => Carbon::now()->subDays(100),
                    'ends_at' => Carbon::now()->subDays(10),
                    'package_ids' => $packages->pluck('id')->toArray(),
                ],
            ];

            $createdCount = 0;
            $updatedCount = 0;

            foreach ($promoCodes as $promoData) {
                $packageIds = $promoData['package_ids'] ?? [];
                unset($promoData['package_ids']);

                $promoCode = PromoCode::updateOrCreate(
                    ['code' => $promoData['code']],
                    $promoData
                );

                if ($promoCode->wasRecentlyCreated) {
                    $createdCount++;
                } else {
                    $updatedCount++;
                }

                // Note: PromoCode model only supports single package_id, not many-to-many
                // For now, we'll leave package_id as null (applies to all packages)
            }

            $this->command->info("Promo code seeding completed: {$createdCount} created, {$updatedCount} updated.");

        } catch (\Exception $e) {
            $this->command->error('Error seeding promo codes: ' . $e->getMessage());
            Log::error('Promo code seeding failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }
}
