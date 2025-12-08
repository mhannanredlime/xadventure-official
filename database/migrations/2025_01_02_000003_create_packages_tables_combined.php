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
        // 1. Package Types (Dictionary)
        if (!Schema::hasTable('package_types')) {
            Schema::create('package_types', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('name');
                $table->string('slug')->unique()->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('parent_id')
                      ->references('id')->on('package_types')
                      ->onDelete('cascade');
            });
        }

        // 2. Price Types (Dictionary)
        if (!Schema::hasTable('price_types')) {
            Schema::create('price_types', function (Blueprint $table) {
                $table->id();
                $table->string('name'); 
                $table->string('slug')->unique(); 
                $table->timestamps();
            });
        }

        // 3. Rider Types (Dictionary)
        if (!Schema::hasTable('rider_types')) {
            Schema::create('rider_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->timestamps();
            });
        }

        // 4. Packages (Main Table)
        if (!Schema::hasTable('packages')) {
            Schema::create('packages', function (Blueprint $table) {
                $table->id();
                
                // Foreign Keys
                $table->unsignedBigInteger('package_type_id')->nullable();
                $table->unsignedBigInteger('vehicle_type_id')->nullable(); // Renamed from package_sub_type_id

                // Core Fields
                $table->string('name');
                $table->string('subtitle')->nullable();
                $table->enum('type', ['regular', 'atv', 'utv', 'bundle'])->default('regular'); // Added bundle
                $table->integer('min_participants')->nullable(); // modified to nullable
                $table->integer('max_participants')->nullable(); // modified to nullable
                
                // Details
                $table->decimal('display_starting_price', 10, 2)->nullable();
                $table->text('details')->nullable();
                $table->text('notes')->nullable();
                $table->string('image_path')->nullable(); // Legacy, moving to images table but keeping for now
                
                // Defaults
                $table->string('selected_weekday')->default('monday');
                $table->string('selected_weekend')->default('friday');
                $table->boolean('is_active')->default(true);
                
                $table->timestamps();

                // Constraints
                $table->foreign('package_type_id')
                      ->references('id')->on('package_types')
                      ->onDelete('set null');
                
                $table->foreign('vehicle_type_id')
                      ->references('id')->on('vehicle_types')
                      ->onDelete('set null');
            });
        }

        // 5. Package Variants (REMOVED)
        // 6. Variant Prices (REMOVED)

        // 7. Package Vehicle Types (Pivot/Relation)
        if (!Schema::hasTable('package_vehicle_types')) {
            Schema::create('package_vehicle_types', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->constrained()->onDelete('cascade');
                $table->foreignId('vehicle_type_id')->constrained()->onDelete('cascade');
                $table->timestamps();
                
                $table->unique(['package_id', 'vehicle_type_id']);
            });
        }

        // 8. Package Weekend Days
        if (!Schema::hasTable('package_weekend_days')) {
            Schema::create('package_weekend_days', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->constrained()->cascadeOnDelete();
                $table->string('day'); 
                $table->boolean('is_weekend')->default(false);
                $table->timestamps();

                $table->unique(['package_id', 'day']);
            });
        }

        // 9. Package Prices (Complex Pricing)
        if (!Schema::hasTable('package_prices')) {
            Schema::create('package_prices', function (Blueprint $table) {
                $table->id();

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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_prices');
        Schema::dropIfExists('package_weekend_days');
        Schema::dropIfExists('package_vehicle_types');
        // Schema::dropIfExists('variant_prices');
        // Schema::dropIfExists('package_variants');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('rider_types');
        Schema::dropIfExists('price_types');
        Schema::dropIfExists('package_types');
    }
};
