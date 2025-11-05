<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('nfc_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('card_id')->unique(); // Unique card identifier
            $table->string('nfc_card_id')->unique(); // Physical NFC card ID (encoded by admin)
            $table->string('card_owner');
            $table->text('billing_address');
            $table->string('contact_number');
            $table->date('purchase_date');
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'expired', 'replacement'])->default('active');
            $table->enum('subscription_plan', ['free', 'basic', 'pro', 'enterprise'])->default('free');
            $table->decimal('purchase_amount', 10, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('tracking_number')->nullable();
            $table->date('shipped_date')->nullable();
            $table->date('delivered_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['nfc_card_id']);
            $table->index(['subscription_plan']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('nfc_cards');
    }
}; 