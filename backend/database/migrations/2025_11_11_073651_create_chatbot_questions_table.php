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
        Schema::create('chatbot_questions', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->json('keywords'); // Array of keywords for matching
            $table->integer('priority')->default(0); // Higher priority shown first
            $table->boolean('is_active')->default(true);
            $table->integer('view_count')->default(0); // Track how often this Q&A is shown
            $table->integer('helpful_count')->default(0); // Track helpful ratings
            $table->integer('not_helpful_count')->default(0); // Track not helpful ratings
            $table->timestamps();
            
            // Indexes for performance
            $table->index('is_active');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_questions');
    }
};
