<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix: Rename 'profile_id' to 'landing_page_id' to match the expected schema
     */
    public function up(): void
    {
        Schema::table('social_links', function (Blueprint $table) {
            // Rename column from profile_id to landing_page_id
            if (Schema::hasColumn('social_links', 'profile_id')) {
                $table->renameColumn('profile_id', 'landing_page_id');
            }
        });

        // Add foreign key constraint if it doesn't exist
        Schema::table('social_links', function (Blueprint $table) {
            if (Schema::hasColumn('social_links', 'landing_page_id')) {
                // Drop old foreign key if exists
                try {
                    $table->dropForeign(['profile_id']);
                } catch (\Exception $e) {
                    // Ignore if not exists
                }
                
                // Add new foreign key
                $table->foreign('landing_page_id')
                    ->references('id')
                    ->on('landing_pages')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_links', function (Blueprint $table) {
            if (Schema::hasColumn('social_links', 'landing_page_id')) {
                $table->dropForeign(['landing_page_id']);
                $table->renameColumn('landing_page_id', 'profile_id');
            }
        });
    }
};
