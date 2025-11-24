<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('package_variant_id')->constrained()->onDelete('cascade');
            $table->foreignId('schedule_slot_id')->constrained()->onDelete('cascade');
            $table->integer('capacity_total')->default(10);
            $table->integer('capacity_reserved')->default(0);
            $table->boolean('is_day_off')->default(false);
            $table->decimal('price_override', 10, 2)->nullable();
            $table->enum('price_tag', ['regular', 'premium', 'discounted'])->default('regular');
            $table->timestamps();
            
            $table->unique(['date', 'package_variant_id', 'schedule_slot_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
