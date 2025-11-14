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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_id')->unique();
            $table->unsignedBigInteger('transaction_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            
            // Refund details
            $table->string('provider')->index();
            $table->string('provider_refund_id')->nullable()->index();
            
            // Amount
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('MYR');
            
            // Status
            $table->enum('status', ['pending', 'succeeded', 'failed', 'cancelled'])->index();
            
            // Reason
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            
            // Processing
            $table->unsignedBigInteger('processed_by')->nullable()->index();
            $table->timestamp('processed_at')->nullable();
            
            // Failure details
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['transaction_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
