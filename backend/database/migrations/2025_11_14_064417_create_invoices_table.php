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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // INV-2025-00001
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null');
            
            // Invoice Details
            $table->decimal('subtotal', 10, 2); // Amount before tax
            $table->decimal('tax_rate', 5, 2)->default(0); // Tax percentage
            $table->decimal('tax_amount', 10, 2)->default(0); // Calculated tax
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2); // Final amount
            $table->string('currency', 3)->default('MYR');
            
            // Line Items (JSON for flexibility)
            $table->json('line_items'); // [{description, quantity, unit_price, amount}]
            
            // Status and Versioning
            $table->enum('status', ['draft', 'issued', 'paid', 'cancelled', 'refunded'])->default('draft');
            $table->integer('version')->default(1); // Track regenerations
            $table->foreignId('parent_invoice_id')->nullable()->constrained('invoices')->onDelete('set null'); // Link to original if regenerated
            
            // PDF Storage
            $table->string('pdf_path')->nullable(); // storage/invoices/INV-2025-00001-v1.pdf
            $table->string('pdf_filename')->nullable();
            $table->integer('pdf_size')->nullable(); // bytes
            
            // Company/Billing Details (snapshot at time of invoice)
            $table->json('company_details')->nullable(); // Company name, address, tax ID
            $table->json('billing_details'); // Customer name, email, address
            
            // Metadata and Notes
            $table->json('metadata')->nullable(); // Additional custom data
            $table->text('notes')->nullable(); // Internal notes
            $table->text('terms')->nullable(); // Payment terms
            
            // Timestamps
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('due_date')->nullable();
            
            // Audit
            $table->foreignId('issued_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('transaction_id');
            $table->index('invoice_number');
            $table->index('status');
            $table->index('issued_at');
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
