<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RiderType;

class RiderTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Single Rider', 'slug' => 'single-rider'],
            ['name' => 'Double Rider', 'slug' => 'double-rider']
        ];

        foreach ($types as $type) {
            RiderType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}
