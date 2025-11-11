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
        Schema::create('chatbot_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->nullable()->constrained('chatbot_questions')->onDelete('set null');
            $table->string('user_question'); // The question user asked
            $table->text('user_message')->nullable(); // User's feedback message
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->integer('rating')->nullable(); // 1-5 stars
            $table->enum('feedback_type', ['not_found', 'rating', 'general'])->default('general');
            $table->boolean('is_read')->default(false);
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('is_read');
            $table->index('feedback_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_feedback');
    }
};
