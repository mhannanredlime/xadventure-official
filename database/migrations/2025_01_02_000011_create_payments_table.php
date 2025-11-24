<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->string('method');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('BDT');
            $table->string('transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
