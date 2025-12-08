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
        Schema::table('packages', function (Blueprint $table) {
            // Add indexes for Foreign Keys if they don't exist
            if (!Schema::hasIndex('packages', 'packages_package_type_id_index')) {
                $table->index('package_type_id');
            }
            if (!Schema::hasIndex('packages', 'packages_vehicle_type_id_index')) {
                $table->index('vehicle_type_id');
            }
        });

        Schema::table('reservations', function (Blueprint $table) {
            // Add index for User FK
            if (!Schema::hasIndex('reservations', 'reservations_user_id_index')) {
                $table->index('user_id');
            }
            
            // Add index for Payment Status (common filter)
            if (!Schema::hasIndex('reservations', 'reservations_payment_status_index')) {
                $table->index('payment_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex(['package_type_id']);
            $table->dropIndex(['vehicle_type_id']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['payment_status']);
        });
    }
};
