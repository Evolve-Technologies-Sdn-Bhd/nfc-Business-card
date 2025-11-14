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
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            
            // Webhook details
            $table->string('provider')->index(); // stripe, billplz, xendit
            $table->string('event_type')->index();
            $table->string('event_id')->nullable()->index();
            
            // Payload
            $table->json('payload');
            
            // Verification
            $table->boolean('signature_verified')->default(false)->index();
            $table->string('signature')->nullable();
            
            // Processing status
            $table->enum('status', ['pending', 'processing', 'processed', 'failed'])->index();
            $table->integer('retry_count')->default(0);
            $table->timestamp('processed_at')->nullable();
            
            // Related records
            $table->string('transaction_id')->nullable()->index();
            $table->string('subscription_id')->nullable()->index();
            
            // Error tracking
            $table->text('error_message')->nullable();
            $table->json('error_trace')->nullable();
            
            // Request details
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['provider', 'event_type']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
