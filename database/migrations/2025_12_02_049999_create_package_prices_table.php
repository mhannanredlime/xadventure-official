<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('price_type_id')->nullable()->constrained();  // regular / weekend / holiday/ date range
            $table->foreignId('rider_type_id')->nullable()->constrained(); // single / double / 4 riders
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['package_id', 'price_type_id', 'rider_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_prices');
    }
};
