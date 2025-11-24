<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PromoCode;
use Carbon\Carbon;

class CreateSamplePromoCodesSeeder extends Seeder
{
    public function run(): void
    {
        $promoCodes = [
            [
                'code' => 'WELCOME2024',
                'applies_to' => 'all',
                'package_id' => null,
                'vehicle_type_id' => null,
                'discount_type' => 'percentage',
                'discount_value' => 10.00, // 10% discount
                'max_discount' => 500.00, // Maximum discount of TK 500
                'min_spend' => 1000.00, // Minimum spend of TK 1000
                'usage_limit_total' => 100,
                'usage_limit_per_user' => 1,
                'starts_at' => Carbon::now()->subDays(30),
                'ends_at' => Carbon::now()->addDays(30),
                'status' => 'active',
                'remarks' => 'Welcome discount for new customers'
            ],
            [
                'code' => 'SUMMER50',
                'applies_to' => 'all',
                'package_id' => null,
                'vehicle_type_id' => null,
                'discount_type' => 'fixed',
                'discount_value' => 50.00, // Fixed discount of TK 50
                'max_discount' => 50.00,
                'min_spend' => 500.00,
                'usage_limit_total' => 50,
                'usage_limit_per_user' => 2,
                'starts_at' => Carbon::now()->subDays(15),
                'ends_at' => Carbon::now()->addDays(15),
                'status' => 'active',
                'remarks' => 'Summer season discount'
            ],
            [
                'code' => 'VRICW2024',
                'applies_to' => 'all',
                'package_id' => null,
                'vehicle_type_id' => null,
                'discount_type' => 'percentage',
                'discount_value' => 15.00, // 15% discount
                'max_discount' => 1000.00, // Maximum discount of TK 1000
                'min_spend' => 2000.00, // Minimum spend of TK 2000
                'usage_limit_total' => 25,
                'usage_limit_per_user' => 1,
                'starts_at' => Carbon::now()->subDays(10),
                'ends_at' => Carbon::now()->addDays(20),
                'status' => 'active',
                'remarks' => 'Special VRICW2024 promotion'
            ],
            [
                'code' => 'FLAT100',
                'applies_to' => 'all',
                'package_id' => null,
                'vehicle_type_id' => null,
                'discount_type' => 'fixed',
                'discount_value' => 100.00, // Fixed discount of TK 100
                'max_discount' => 100.00,
                'min_spend' => 1500.00,
                'usage_limit_total' => 75,
                'usage_limit_per_user' => 1,
                'starts_at' => Carbon::now()->subDays(5),
                'ends_at' => Carbon::now()->addDays(25),
                'status' => 'active',
                'remarks' => 'Flat discount promotion'
            ]
        ];

        foreach ($promoCodes as $promoData) {
            PromoCode::updateOrCreate(
                ['code' => $promoData['code']],
                $promoData
            );
        }

        $this->command->info('Sample promo codes created successfully!');
        $this->command->info('Available codes: WELCOME2024, SUMMER50, VRICW2024, FLAT100');
    }
}
