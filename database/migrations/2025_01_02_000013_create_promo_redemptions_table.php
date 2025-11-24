<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained()->onDelete('cascade');
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->decimal('amount_discounted', 10, 2);
            $table->timestamp('redeemed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_redemptions');
    }
};
