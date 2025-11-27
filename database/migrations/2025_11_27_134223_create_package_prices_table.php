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
        Schema::create('package_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            // pricing type
            $table->enum('type', ['weekday', 'weekend', 'specific_days', 'date_range']);

            $table->json('days')->nullable();

            // date range pricing (seasonal)
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->integer('price'); // per-day price
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_prices');
    }
};
