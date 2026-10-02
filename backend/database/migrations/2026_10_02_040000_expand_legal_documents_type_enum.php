<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite cannot ALTER COLUMN to modify an enum inline.
            // Rebuild the table with the expanded CHECK constraint while
            // preserving existing data, indexes, and foreign keys.
            Schema::disableForeignKeyConstraints();

            DB::statement('CREATE TABLE legal_documents__new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                type VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                version VARCHAR(255) DEFAULT \'1.0\' NOT NULL,
                effective_date TIMESTAMP NULL,
                updated_by UNSIGNED BIGINT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL
            )');

            DB::statement('INSERT INTO legal_documents__new
                (id, type, content, version, effective_date, updated_by, created_at, updated_at)
                SELECT id, type, content, version, effective_date, updated_by, created_at, updated_at
                FROM legal_documents');

            DB::statement('DROP TABLE legal_documents');
            DB::statement('ALTER TABLE legal_documents__new RENAME TO legal_documents');

            // SQLite does not enforce arbitrary CHECKs defined on column type
            // after the rebuild unless explicitly declared. Apply a table
            // re-rebuild with an inline CHECK to honour the same semantics as
            // the original enum-column (but expanded to 4 types).
            DB::statement('CREATE TABLE legal_documents__new2 (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                type VARCHAR(255) NOT NULL CHECK (type IN (\'terms_of_service\',\'privacy_policy\',\'refund_policy\',\'acceptable_use\')),
                content TEXT NOT NULL,
                version VARCHAR(255) DEFAULT \'1.0\' NOT NULL,
                effective_date TIMESTAMP NULL,
                updated_by UNSIGNED BIGINT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                UNIQUE (type),
                FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL
            )');

            DB::statement('INSERT INTO legal_documents__new2
                (id, type, content, version, effective_date, updated_by, created_at, updated_at)
                SELECT id, type, content, version, effective_date, updated_by, created_at, updated_at
                FROM legal_documents');

            DB::statement('DROP TABLE legal_documents');
            DB::statement('ALTER TABLE legal_documents__new2 RENAME TO legal_documents');

            Schema::enableForeignKeyConstraints();
        } else {
            Schema::table('legal_documents', function (Blueprint $table) {
                $table->enum('type', [
                    'terms_of_service',
                    'privacy_policy',
                    'refund_policy',
                    'acceptable_use',
                ])->change();
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // Revert: keep only the 2 original enum values. Rows that were
            // added with refund_policy / acceptable_use MUST be deleted
            // before re-collapsing the CHECK constraint.
            Schema::disableForeignKeyConstraints();
            DB::statement("DELETE FROM legal_documents WHERE type IN ('refund_policy','acceptable_use')");

            DB::statement('CREATE TABLE legal_documents__rollback (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                type VARCHAR(255) NOT NULL CHECK (type IN (\'terms_of_service\',\'privacy_policy\')),
                content TEXT NOT NULL,
                version VARCHAR(255) DEFAULT \'1.0\' NOT NULL,
                effective_date TIMESTAMP NULL,
                updated_by UNSIGNED BIGINT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                UNIQUE (type),
                FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL
            )');

            DB::statement('INSERT INTO legal_documents__rollback
                (id, type, content, version, effective_date, updated_by, created_at, updated_at)
                SELECT id, type, content, version, effective_date, updated_by, created_at, updated_at
                FROM legal_documents');

            DB::statement('DROP TABLE legal_documents');
            DB::statement('ALTER TABLE legal_documents__rollback RENAME TO legal_documents');
            Schema::enableForeignKeyConstraints();
        } else {
            Schema::table('legal_documents', function (Blueprint $table) {
                $table->enum('type', ['terms_of_service', 'privacy_policy'])->change();
            });
        }
    }
};
