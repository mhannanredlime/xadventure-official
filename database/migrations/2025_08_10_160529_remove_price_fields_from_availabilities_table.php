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
        Schema::table('availabilities', function (Blueprint $table) {
            if (Schema::hasColumn('availabilities', 'price_override')) {
                $table->dropColumn('price_override');
            }
            if (Schema::hasColumn('availabilities', 'price_tag')) {
                $table->dropColumn('price_tag');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('availabilities', function (Blueprint $table) {
            if (!Schema::hasColumn('availabilities', 'price_override')) {
                $table->decimal('price_override', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('availabilities', 'price_tag')) {
                $table->enum('price_tag', ['regular', 'premium', 'discounted'])->default('regular');
            }
        });
    }
};
