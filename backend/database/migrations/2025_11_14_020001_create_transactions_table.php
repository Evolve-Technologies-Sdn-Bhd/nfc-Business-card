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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique(); // our unique reference
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('payment_method_id')->nullable()->index();
            
            // Transaction details
            $table->enum('type', ['payment', 'refund', 'subscription'])->index();
            $table->enum('payment_rail', ['card', 'fpx', 'duitnow', 'ewallet', 'manual_bank'])->index();
            $table->string('provider')->index(); // stripe, billplz, etc.
            $table->string('provider_transaction_id')->nullable()->index();
            
            // Amount details
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('MYR');
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2);
            
            // Status tracking
            $table->enum('status', [
                'pending',
                'processing',
                'requires_action', // 3DS or additional verification
                'succeeded',
                'failed',
                'cancelled',
                'refunded',
                'partially_refunded'
            ])->index();
            
            // Subscription related
            $table->unsignedBigInteger('subscription_id')->nullable()->index();
            $table->boolean('is_recurring')->default(false)->index();
            
            // Payment method specific details
            $table->string('card_last4')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('ewallet_type')->nullable();
            
            // Manual bank transfer details
            $table->string('bank_reference_code')->nullable()->index(); // unique VA number or reference
            $table->string('payment_proof_url')->nullable(); // uploaded proof
            $table->timestamp('payment_proof_uploaded_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable()->index();
            $table->timestamp('verified_at')->nullable();
            
            // Failure tracking
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();
            
            // 3DS tracking
            $table->string('three_ds_status')->nullable(); // authenticated, not_authenticated, challenge_required
            $table->text('client_secret')->nullable(); // for frontend 3DS flow
            
            // Metadata and description
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // store order_id, item_id, etc.
            
            // Audit
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
