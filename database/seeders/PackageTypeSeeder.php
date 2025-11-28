<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PackageType;

class PackageTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Main categories
        $regular = PackageType::updateOrCreate(
            ['slug' => 'regular'],
            [
                'name' => 'Regular',
                'parent_id' => null,
                'is_active' => true
            ]
        );

        $atv = PackageType::updateOrCreate(
            ['slug' => 'atv'],
            [
                'name' => 'ATV',
                'parent_id' => null,
                'is_active' => true
            ]
        );

        // Regular → Subtypes
        $subtypes = ['Single', 'Bundle', 'Group'];

        foreach ($subtypes as $subtype) {
            PackageType::updateOrCreate(
                [
                    'slug' => strtolower($subtype),
                    'parent_id' => $regular->id,
                ],
                [
                    'name' => $subtype,
                    'is_active' => true,
                ]
            );
        }
    }
}
