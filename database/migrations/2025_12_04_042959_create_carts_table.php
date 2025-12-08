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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('package_type');
            $table->unsignedBigInteger('package_id');

            // cart_amount min 200
            $table->decimal('amount', 10, 2)->default(200)->check('amount >= 200');

            // quantity min 1
            $table->unsignedInteger('quantity')->default(1)->check('quantity >= 1');

            $table->text('metadata')->nullable();
            $table->string('session_id');

            // expires_at default next 24 hours
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
