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
            $table->index(['is_active', 'type'], 'packages_active_type_index');
            $table->index('display_starting_price');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->index('date');
            $table->index(['booking_status', 'date']); // For filtering by status and date range
        });

        // Availabilities has unique(['date', 'package_id', 'schedule_slot_id']) which starts with date
        // So basic date filtering is covered.
        // We might want index on package_id for "Get availability for this package" queries if date is not the first constraint?
        // Actually unique index is effective. But if we query `where('package_id', 1)`, the composite index on `date` first might not be fully used.
        // Adding index on package_id might help.
        Schema::table('availabilities', function (Blueprint $table) {
             $table->index('package_id');
        });
        
        Schema::table('payments', function (Blueprint $table) {
            $table->index('paid_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex('packages_active_type_index');
            $table->dropIndex(['display_starting_price']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['booking_status', 'date']);
        });

        Schema::table('availabilities', function (Blueprint $table) {
            $table->dropIndex(['package_id']);
        });
        
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['paid_at']);
            $table->dropIndex(['status']);
        });
    }
};
