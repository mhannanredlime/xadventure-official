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
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number', 20)->comment('Recipient phone number');
            $table->text('message')->comment('SMS message content');
            $table->string('template_name', 100)->nullable()->comment('Template used for this SMS');
            $table->string('provider', 50)->comment('SMS provider used (mim, twilio, etc.)');
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed'])->default('pending')->comment('SMS delivery status');
            $table->string('message_id', 100)->nullable()->comment('Provider message ID for tracking');
            $table->text('error_message')->nullable()->comment('Error message if SMS failed');
            $table->timestamp('sent_at')->nullable()->comment('When SMS was sent to provider');
            $table->timestamp('delivered_at')->nullable()->comment('When SMS was delivered to recipient');
            $table->json('metadata')->nullable()->comment('Additional metadata (cost, retry count, etc.)');
            $table->timestamps();

            // Indexes for performance
            $table->index(['phone_number']);
            $table->index(['status']);
            $table->index(['provider']);
            $table->index(['template_name']);
            $table->index(['created_at']);
            $table->index(['sent_at']);
            $table->index(['delivered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
