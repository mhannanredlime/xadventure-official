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
        Schema::create('price_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_price_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('price_tag', ['premium', 'discounted']);
            $table->decimal('price_amount', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Ensure one override per variant per date
            $table->unique(['package_price_id', 'date']);
            
            // Indexes for performance
            $table->index(['date', 'package_price_id']);
            $table->index('price_tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_overrides');
    }
};
