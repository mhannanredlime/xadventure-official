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

            // Foreign keys
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('price_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('package_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rider_type_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->enum('day', ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'])->nullable();

            $table->timestamps();

            $table->unique(
                ['package_id', 'price_type_id', 'package_type_id', 'rider_type_id', 'day'],
                'package_price_unique_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_prices');
    }
};
