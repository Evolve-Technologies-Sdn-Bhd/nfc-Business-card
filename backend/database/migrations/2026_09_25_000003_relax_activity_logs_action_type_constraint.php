<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Extend activity_logs.action_type CHECK/ENUM constraint so all admin mutation
 * routes (fallback admin mapping in LogActivity middleware) can be persisted.
 *
 * Also adds a SAFETY NET: since SQLite enforces CHECK constraints at write time
 * and these can't be reliably changed without rebuild, we also re-create the
 * action_type column as a plain indexed VARCHAR (no rigid CHECK/ENUM) so future
 * admin entity types never trigger "CHECK constraint failed: action_type" 500
 * errors again. The actual enum validation is done in middleware / model logic.
 */
return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('activity_logs', function (Blueprint $table) {
                // Drop the original action_type index first (depends on column)
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $idxs = $sm->listTableIndexes('activity_logs');
                $hasActionTypeIdx = false;
                foreach ($idxs as $idx) {
                    $cols = $idx->getColumns();
                    if (is_array($cols) && count($cols) === 1 && ($cols[0] ?? null) === 'action_type') {
                        $hasActionTypeIdx = true;
                        $table->dropIndex($idx->getName());
                        break;
                    }
                }
                if (!$hasActionTypeIdx) {
                    // SQLite sometimes hides the name, try conventional name
                    try { $table->dropIndex(['action_type']); } catch (\Throwable $e) {}
                }
            });
        } catch (\Throwable $e) {
            // If index drop fails, continue — column change handles the rest
        }

        try {
            Schema::table('activity_logs', function (Blueprint $table) {
                // Re-create the column as a plain indexed VARCHAR accepting any
                // action_type string, so LogActivity fallback admin mappings
                // (admin_update_user, admin_create_business_user, etc.) can
                // always be persisted. Length 100 is plenty.
                $table->string('action_type', 100)->nullable(false)->change();
            });
        } catch (\Throwable $e) {
            // Raw SQLite path: change() on enum/CHECK columns may fail in SQLite
            // via schema grammar — fall back to manual rebuild via raw SQL.
            DB::transaction(function () {
                DB::statement('ALTER TABLE activity_logs RENAME TO activity_logs_old');
                DB::statement(
                    "CREATE TABLE activity_logs (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                        business_account_id INTEGER NULL REFERENCES users(id) ON DELETE CASCADE,
                        action_type VARCHAR(100) NOT NULL,
                        action_description VARCHAR(255) NOT NULL,
                        entity_type VARCHAR(255) NULL,
                        entity_id INTEGER NULL,
                        old_values TEXT NULL,
                        new_values TEXT NULL,
                        metadata TEXT NULL,
                        ip_address VARCHAR(45) NULL,
                        user_agent TEXT NULL,
                        created_at DATETIME NULL,
                        updated_at DATETIME NULL
                    )"
                );
                DB::statement(
                    "INSERT INTO activity_logs
                        (id, user_id, business_account_id, action_type, action_description,
                         entity_type, entity_id, old_values, new_values, metadata,
                         ip_address, user_agent, created_at, updated_at)
                     SELECT id, user_id, business_account_id, action_type, action_description,
                            entity_type, entity_id, old_values, new_values, metadata,
                            ip_address, user_agent, created_at, updated_at
                     FROM activity_logs_old"
                );
                DB::statement('DROP TABLE activity_logs_old');
            });
        }

        try {
            Schema::table('activity_logs', function (Blueprint $table) {
                // Re-create useful indexes on the new varchar column
                $table->index('action_type', 'activity_logs_action_type_index');
                $table->index(['user_id', 'created_at'], 'activity_logs_user_created_index');
                $table->index(['business_account_id', 'created_at'], 'activity_logs_business_created_index');
                $table->index('created_at', 'activity_logs_created_at_index');
            });
        } catch (\Throwable $e) {
            // If re-index fails the table is still fully functional.
            report($e);
        }
    }

    public function down(): void
    {
        // Non-reversible: we intentionally loosened the CHECK/ENUM constraint.
        // A rollback won't restore the old rigid CHECK constraint to avoid
        // breaking rows inserted in the meantime.
    }
};
