<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PackageType;

class PackageTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packageTypes = [
            'Single Package',
            'Bundle Package',
            'Group Package',
        ];

        foreach ($packageTypes as $type) {
            PackageType::updateOrCreate(
                ['name' => $type],
                ['is_active' => true]
            );
        }
    }
}
