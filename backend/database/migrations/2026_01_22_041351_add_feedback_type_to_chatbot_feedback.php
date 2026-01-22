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
        if (!Schema::hasColumn('chatbot_feedback', 'feedback_type')) {
            Schema::table('chatbot_feedback', function (Blueprint $table) {
                $table->string('feedback_type')->default('general')->after('rating');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('chatbot_feedback', 'feedback_type')) {
            Schema::table('chatbot_feedback', function (Blueprint $table) {
                $table->dropColumn('feedback_type');
            });
        }
    }
};
