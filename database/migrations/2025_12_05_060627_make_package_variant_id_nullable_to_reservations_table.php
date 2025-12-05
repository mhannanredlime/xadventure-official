<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // package_variant_id make nullable
            if (Schema::hasColumn('reservations', 'package_variant_id')) {
                $table->unsignedBigInteger('package_variant_id')->nullable()->change();
            } else {
                $table->unsignedBigInteger('package_variant_id')->nullable();
            }

            // schedule_slot_id add or make nullable
            if (Schema::hasColumn('reservations', 'schedule_slot_id')) {
                $table->unsignedBigInteger('schedule_slot_id')->nullable()->change();
            } else {
                $table->unsignedBigInteger('schedule_slot_id')->nullable();
            }

            // package_price_id add if not exists
            if (!Schema::hasColumn('reservations', 'package_price_id')) {
                $table->unsignedBigInteger('package_price_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {

            // Drop foreign key constraints first
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'reservations' 
                  AND COLUMN_NAME IN ('package_variant_id', 'schedule_slot_id') 
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            foreach ($foreignKeys as $fk) {
                $table->dropForeign($fk->CONSTRAINT_NAME);
            }

            // package_variant_id: set default 0 for nulls before making non-nullable
            DB::table('reservations')
                ->whereNull('package_variant_id')
                ->update(['package_variant_id' => 0]); // অথবা valid id

            $table->unsignedBigInteger('package_variant_id')->nullable(false)->change();

            // Drop schedule_slot_id safely
            if (Schema::hasColumn('reservations', 'schedule_slot_id')) {
                $table->dropColumn('schedule_slot_id');
            }

            // Drop package_price_id
            if (Schema::hasColumn('reservations', 'package_price_id')) {
                $table->dropColumn('package_price_id');
            }
        });
    }
};
