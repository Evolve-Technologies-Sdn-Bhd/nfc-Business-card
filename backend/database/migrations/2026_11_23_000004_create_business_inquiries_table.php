<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Business plan enquiries submitted from the public website / plan selection.
 *
 * Replaces the previous "open a prefilled Gmail compose window" workaround with
 * a real, reviewable record that admins can triage.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->text('company_address')->nullable();
            $table->unsignedInteger('quota')->nullable();
            $table->text('notes')->nullable();
            $table->string('source', 50)->default('homepage');
            $table->string('status', 30)->default('new');
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_inquiries');
    }
};
