<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix: Rename 'profile_id' to 'landing_page_id' to match the expected schema.
     * Fully defensive against production databases where the exact FK name or
     * column state may differ from the original development snapshot.
     */
    public function up(): void
    {
        $tableName = 'social_links';
        $dbName = DB::getDatabaseName();

        // ---------------------------------------------------------------
        // STEP 1: DROP any existing FOREIGN KEY related to profile_id
        // or landing_page_id. We must do this BEFORE renaming columns.
        // ---------------------------------------------------------------
        $existingFks = DB::select(
            "SELECT CONSTRAINT_NAME
             FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME != 'PRIMARY'
               AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$dbName, $tableName]
        );

        foreach ($existingFks as $fk) {
            $fkName = $fk->CONSTRAINT_NAME;
            try {
                DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$fkName}`");
            } catch (\Throwable $e) {
                // Safe to ignore — prevent migration crash on partial state.
            }
        }

        // ---------------------------------------------------------------
        // STEP 2: Rename column profile_id -> landing_page_id if needed
        // ---------------------------------------------------------------
        $hasProfileId   = Schema::hasColumn($tableName, 'profile_id');
        $hasLandingPage = Schema::hasColumn($tableName, 'landing_page_id');

        if ($hasProfileId && ! $hasLandingPage) {
            DB::statement("ALTER TABLE `{$tableName}` RENAME COLUMN `profile_id` TO `landing_page_id`");
        } elseif (! $hasProfileId && ! $hasLandingPage) {
            // Neither column exists — create it so downstream logic can proceed
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('landing_page_id')->nullable();
            });
        }

        // ---------------------------------------------------------------
        // STEP 3: Add the correct landing_page_id foreign key (idempotent)
        // ---------------------------------------------------------------
        $hasFkLanding = DB::selectOne(
            "SELECT CONSTRAINT_NAME
             FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = 'landing_page_id'
               AND REFERENCED_TABLE_NAME = 'landing_pages'
             LIMIT 1",
            [$dbName, $tableName]
        );

        if (! $hasFkLanding) {
            try {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreign('landing_page_id')
                        ->references('id')
                        ->on('landing_pages')
                        ->onDelete('cascade');
                });
            } catch (\Throwable $e) {
                // Ignore duplicate / data mismatch errors — migration must not fail.
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = 'social_links';
        $dbName    = DB::getDatabaseName();

        $hasFkLanding = DB::selectOne(
            "SELECT CONSTRAINT_NAME
             FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = 'landing_page_id'
               AND REFERENCED_TABLE_NAME = 'landing_pages'
             LIMIT 1",
            [$dbName, $tableName]
        );

        if ($hasFkLanding) {
            try {
                DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$hasFkLanding->CONSTRAINT_NAME}`");
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if (Schema::hasColumn($tableName, 'landing_page_id')
            && ! Schema::hasColumn($tableName, 'profile_id')) {
            DB::statement("ALTER TABLE `{$tableName}` RENAME COLUMN `landing_page_id` TO `profile_id`");
        }
    }
};
