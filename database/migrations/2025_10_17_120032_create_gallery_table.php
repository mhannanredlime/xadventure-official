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
        Schema::create('gallery', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->string('thumbnail_path')->nullable();
            $table->string('original_name');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->string('alt_text')->nullable();
            $table->text('tags')->nullable(); // JSON string for categorization
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['uploaded_by', 'created_at']);
            $table->index('mime_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery');
    }
};