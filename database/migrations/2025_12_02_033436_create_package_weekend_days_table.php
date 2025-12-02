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
        Schema::create('package_weekend_days', function (Blueprint $table) {
            $table->id();

            // Foreign key to packages table
            $table->foreignId('package_id')
                  ->constrained()       // references 'id' on 'packages'
                  ->cascadeOnDelete();  // deletes weekend days if package is deleted

            $table->string('day');      // day as string: 'mon', 'tue', etc.
            $table->boolean('is_weekend')->default(false);
            $table->timestamps();

            // Optionally, add a unique constraint to prevent duplicate package/day
            $table->unique(['package_id', 'day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_weekend_days');
    }
};
