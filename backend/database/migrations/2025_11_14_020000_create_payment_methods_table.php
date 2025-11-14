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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            
            // Payment method details
            $table->enum('type', ['card', 'bank', 'ewallet'])->index();
            $table->string('provider')->index(); // stripe, billplz, xendit, etc.
            $table->string('provider_payment_method_id')->nullable(); // external ID
            
            // Card details (tokenized)
            $table->string('card_brand')->nullable(); // visa, mastercard, amex
            $table->string('card_last4')->nullable();
            $table->string('card_exp_month')->nullable();
            $table->string('card_exp_year')->nullable();
            $table->string('card_fingerprint')->nullable();
            
            // Bank details
            $table->string('bank_name')->nullable();
            $table->string('bank_account_last4')->nullable();
            
            // E-wallet details
            $table->string('ewallet_type')->nullable(); // tng, grabpay, boost, shopeepay
            $table->string('ewallet_account_id')->nullable();
            
            // Metadata
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_verified')->default(false);
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'is_default']);
            $table->index('provider_payment_method_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
