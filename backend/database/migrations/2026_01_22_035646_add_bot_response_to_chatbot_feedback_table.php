<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chatbot_feedback', function (Blueprint $table) {
            $table->text('bot_response')->nullable()->after('user_question');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatbot_feedback', function (Blueprint $table) {
            $table->dropColumn('bot_response');
        });
    }
};
