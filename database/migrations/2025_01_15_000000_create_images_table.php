<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->morphs('imageable'); // This creates imageable_type and imageable_id for polymorphic relationship
            $table->string('image_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->text('alt_text')->nullable();
            $table->timestamps();
            
            // Indexes for better performance (removed duplicate morphs index)
            $table->index('sort_order');
            $table->index('is_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};

