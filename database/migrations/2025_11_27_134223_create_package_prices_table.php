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
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->enum('day', ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']);
            $table->integer('rider_count')->nullable(); 
            $table->decimal('price', 10, 2);
            $table->string('day_type')->default('weekday');
            $table->string('package_type')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['package_id', 'day', 'rider_count']);

        });

    }

    public function down(): void
    {
        Schema::dropIfExists('package_prices');
    }
};
