<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // 1. Drop old foreign key
            $table->dropForeign(['package_sub_type_id']);

            // 2. Rename the column
            $table->renameColumn('package_sub_type_id', 'vehicle_type_id');
        });

        Schema::table('packages', function (Blueprint $table) {
            // 3. Add new foreign key
            $table->foreign('vehicle_type_id')
                  ->references('id')->on('vehicle_types')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Reverse: drop new FK
            $table->dropForeign(['vehicle_type_id']);

            // Rename back
            $table->renameColumn('vehicle_type_id', 'package_sub_type_id');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->foreign('package_sub_type_id')
                  ->references('id')->on('vehicle_types')
                  ->onDelete('set null');
        });
    }
};
