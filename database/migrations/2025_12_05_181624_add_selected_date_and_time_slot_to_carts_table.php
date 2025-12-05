<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->date('selected_date')->nullable()->after('quantity');
            $table->unsignedBigInteger('time_slot_id')->nullable()->after('selected_date');
        });
    }

    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('selected_date');
            $table->dropColumn('time_slot_id');
        });
    }
};
