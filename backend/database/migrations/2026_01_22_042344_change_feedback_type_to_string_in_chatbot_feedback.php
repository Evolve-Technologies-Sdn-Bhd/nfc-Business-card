<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (! Schema::hasColumn('chatbot_feedback', 'feedback_type')) {
            return;
        }

        try {
            DB::statement("ALTER TABLE chatbot_feedback MODIFY COLUMN feedback_type VARCHAR(255) DEFAULT 'general'");
        } catch (\Throwable $e) {
            // Ignore if column type is already correct / lock / driver-specific error.
            // Must never break the migration chain.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to avoid data loss on rollback
    }
};
