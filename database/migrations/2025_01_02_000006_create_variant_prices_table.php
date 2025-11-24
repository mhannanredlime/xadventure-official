<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variant_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_variant_id')->constrained()->onDelete('cascade');
            $table->enum('price_type', ['weekday', 'weekend'])->default('weekday');
            $table->decimal('amount', 10, 2);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_prices');
    }
};
