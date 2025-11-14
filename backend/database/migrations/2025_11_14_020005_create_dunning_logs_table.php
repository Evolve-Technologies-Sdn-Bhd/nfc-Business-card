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
        Schema::create('dunning_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id')->index();
            $table->unsignedBigInteger('transaction_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->index();
            
            // Dunning attempt details
            $table->integer('attempt_number')->default(1);
            $table->enum('action_taken', [
                'retry_payment',
                'send_notification',
                'suspend_subscription',
                'cancel_subscription'
            ])->index();
            
            // Status
            $table->enum('status', ['pending', 'succeeded', 'failed'])->index();
            
            // Payment retry details
            $table->timestamp('next_retry_at')->nullable();
            $table->string('failure_reason')->nullable();
            
            // Notification sent
            $table->boolean('notification_sent')->default(false);
            $table->string('notification_type')->nullable(); // email, sms, push
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['subscription_id', 'status']);
            $table->index('next_retry_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dunning_logs');
    }
};
