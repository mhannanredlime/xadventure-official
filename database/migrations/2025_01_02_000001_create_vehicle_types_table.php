<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subtitle')->nullable(); // For "2 Seater ATV" type descriptions
            $table->text('description')->nullable();
            $table->string('image_path')->nullable(); // For vehicle type images
            $table->integer('seating_capacity')->default(2); // Number of seats
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_types');
    }
};
