<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update regular package variants to have capacity of 6
        DB::table('package_variants')
            ->join('packages', 'package_variants.package_id', '=', 'packages.id')
            ->where('packages.type', 'regular')
            ->update(['package_variants.capacity' => 6]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert regular package variants back to their previous capacities
        // This is a data migration, so we'll set them back to reasonable defaults
        DB::table('package_variants')
            ->join('packages', 'package_variants.package_id', '=', 'packages.id')
            ->where('packages.type', 'regular')
            ->update(['package_variants.capacity' => 999]);
    }
};