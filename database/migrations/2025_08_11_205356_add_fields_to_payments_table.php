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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->string('payment_method')->nullable()->after('method');
            $table->json('payment_details')->nullable()->after('meta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'payment_method', 'payment_details']);
        });
    }
};
