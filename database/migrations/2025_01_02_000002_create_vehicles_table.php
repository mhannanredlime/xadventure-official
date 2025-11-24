<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_type_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('details')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('op_start_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
