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
        Schema::table('chatbot_feedback', function (Blueprint $table) {
            $table->enum('status', ['pending', 'in_progress', 'resolved'])->default('pending')->after('feedback_type');
            $table->string('category')->nullable()->after('feedback_type'); // bug, feature, question, complaint, other
            $table->text('admin_notes')->nullable()->after('user_message'); // Admin internal notes
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null')->after('status'); // Admin who resolved it
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');
            
            // Indexes
            $table->index('status');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatbot_feedback', function (Blueprint $table) {
            $table->dropColumn(['status', 'category', 'admin_notes', 'resolved_by', 'resolved_at']);
        });
    }
};
