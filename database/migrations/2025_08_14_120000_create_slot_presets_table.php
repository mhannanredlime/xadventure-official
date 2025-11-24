<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slot_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('slot_preset_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_preset_id')->constrained('slot_presets')->onDelete('cascade');
            $table->foreignId('schedule_slot_id')->constrained('schedule_slots')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['slot_preset_id', 'schedule_slot_id']);
        });

        Schema::create('slot_preset_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_variant_id')->constrained('package_variants')->onDelete('cascade');
            $table->date('date');
            $table->foreignId('slot_preset_id')->constrained('slot_presets')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['package_variant_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slot_preset_overrides');
        Schema::dropIfExists('slot_preset_items');
        Schema::dropIfExists('slot_presets');
    }
};


