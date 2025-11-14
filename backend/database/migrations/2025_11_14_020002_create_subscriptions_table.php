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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('payment_method_id')->nullable()->index();
            
            // Subscription details
            $table->string('subscription_id')->unique();
            $table->string('provider')->index(); // stripe, billplz
            $table->string('provider_subscription_id')->nullable()->index();
            
            // Plan details
            $table->string('plan_name');
            $table->enum('plan_type', ['basic', 'business', 'premium'])->index();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('MYR');
            $table->enum('interval', ['monthly', 'yearly'])->default('monthly');
            
            // Status
            $table->enum('status', [
                'active',
                'past_due',
                'cancelled',
                'suspended',
                'expired'
            ])->index();
            
            // Billing cycle
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('next_billing_date')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            
            // Cancellation
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('next_billing_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
