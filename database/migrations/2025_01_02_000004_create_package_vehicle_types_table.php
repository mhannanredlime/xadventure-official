<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_type_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['package_id', 'vehicle_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_vehicle_types');
    }
};
