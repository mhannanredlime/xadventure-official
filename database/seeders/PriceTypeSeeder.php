<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PriceType;

class PriceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Week Day', 'slug' => 'weekday'],
            ['name' => 'Weekend Day', 'slug' => 'weekend'],
        ];

        foreach ($types as $type) {
            PriceType::updateOrCreate(
                ['slug' => $type['slug']], // unique key
                $type // values to insert/update
            );
        }
    }
}
