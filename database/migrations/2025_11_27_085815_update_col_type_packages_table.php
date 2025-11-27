<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Convert existing values to valid JSON arrays
        DB::table('packages')->get()->each(function ($package) {
            $weekday = $package->selected_weekday;
            $weekend = $package->selected_weekend;

            $weekdayJson = $weekday
                ? json_encode(array_map('trim', explode(',', $weekday)))
                : null;

            $weekendJson = $weekend
                ? json_encode(array_map('trim', explode(',', $weekend)))
                : null;

            DB::table('packages')
                ->where('id', $package->id)
                ->update([
                    'selected_weekday' => $weekdayJson,
                    'selected_weekend' => $weekendJson,
                ]);
        });

        // Step 2: Change column types to JSON
        Schema::table('packages', function (Blueprint $table) {
            $table->json('selected_weekday')->nullable()->change();
            $table->json('selected_weekend')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('selected_weekday')->nullable()->change();
            $table->string('selected_weekend')->nullable()->change();
        });
    }
};
