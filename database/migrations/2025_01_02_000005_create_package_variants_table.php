<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->string('variant_name');
            $table->integer('capacity')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_variants');
    }
};
