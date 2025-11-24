<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('package_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('package_variants', 'capacity')) {
                $table->integer('capacity')->default(1)->after('variant_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_variants', function (Blueprint $table) {
            if (Schema::hasColumn('package_variants', 'capacity')) {
                $table->dropColumn('capacity');
            }
        });
    }
};
