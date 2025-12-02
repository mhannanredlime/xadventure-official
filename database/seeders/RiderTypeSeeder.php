<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RiderType;

class RiderTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Single Rider', 'rider_count' => 1, 'is_active' => true],
            ['name' => 'Double Rider', 'rider_count' => 2, 'is_active' => true],
            ['name' => 'Quad Rider',   'rider_count' => 4, 'is_active' => true],
            ['name' => 'Six Rider',    'rider_count' => 6, 'is_active' => false], // you can activate later
        ];

        foreach ($types as $type) {
            RiderType::updateOrCreate(
                ['rider_count' => $type['rider_count']],  // unique key
                $type
            );
        }
    }
}
