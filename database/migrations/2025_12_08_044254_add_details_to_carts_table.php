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
        Schema::table('carts', function (Blueprint $table) {
            $table->unsignedBigInteger('rider_type_id')->nullable()->after('package_id');
            $table->date('date')->nullable()->after('rider_type_id');
            $table->unsignedBigInteger('schedule_slot_id')->nullable()->after('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['rider_type_id', 'date', 'schedule_slot_id']);
        });
    }
};
