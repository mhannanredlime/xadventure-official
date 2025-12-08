<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RiderType;

class RiderTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Single Rider', 
                'slug' => 'single-rider',
                // 'description' => 'One person operating the vehicle',
                // 'max_passengers' => 0,
                // 'is_active' => true
            ],
            [
                'name' => 'Double Rider', 
                'slug' => 'double-rider',
                // 'description' => 'Two passengers with one operator',     
                // 'max_passengers' => 1,
                // 'is_active' => true
            ],
            [
                'name' => 'Adventure Tour', 
                'slug' => 'adventure-tour',
            ],
            [
                'name' => 'Premium Single', 
                'slug' => 'premium-single',
            ],
            [
                'name' => 'Premium Double', 
                'slug' => 'premium-double',
            ]
        ];

        foreach ($types as $type) {
            RiderType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
        
        $this->command->info(count($types) . ' rider types seeded.');
    }
}