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
                'is_active' => true // no riser wise price for this type package 
            ]
        );

        $atv = PackageType::updateOrCreate(
            ['slug' => 'atv'],
            [
                'name' => 'ATV',
                'parent_id' => null,
                'is_active' => true // it has rider wise price for weekend and weekday this type package because this package has vehicle or 1 rider or two rider can seat  
            ]
        );

        $utv = PackageType::updateOrCreate(
            ['slug' => 'utv'],
            [
                'name' => 'UTV',
                'parent_id' => null,
                'is_active' => true // it has rider wise price for weekend and weekday 
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
