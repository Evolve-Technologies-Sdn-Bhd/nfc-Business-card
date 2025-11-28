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
            // Make user_question nullable since we generate it server-side for feedback submissions
            $table->string('user_question')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatbot_feedback', function (Blueprint $table) {
            $table->string('user_question')->change();
        });
    }
};
