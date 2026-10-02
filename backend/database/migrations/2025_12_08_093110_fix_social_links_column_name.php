<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = 'social_links';
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $dbName = DB::getDatabaseName();
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
                    // ignore
                }
            }
        } else {
            try {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $existing = Schema::getForeignKeys($tableName);
                    foreach ($existing as $fk) {
                        $cols = $fk['columns'] ?? [];
                        if (in_array('profile_id', $cols, true) || in_array('landing_page_id', $cols, true)) {
                            try {
                                $table->dropForeign($fk['name']);
                            } catch (\Throwable $e) {
                                // ignore
                            }
                        }
                    }
                });
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $hasProfileId   = Schema::hasColumn($tableName, 'profile_id');
        $hasLandingPage = Schema::hasColumn($tableName, 'landing_page_id');

        if ($hasProfileId && ! $hasLandingPage) {
            try {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('profile_id', 'landing_page_id');
                });
            } catch (\Throwable $e) {
                try {
                    DB::statement("ALTER TABLE `{$tableName}` RENAME COLUMN `profile_id` TO `landing_page_id`");
                } catch (\Throwable $e2) {
                    // ignore
                }
            }
        } elseif (! $hasProfileId && ! $hasLandingPage) {
            try {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('landing_page_id')->nullable();
                });
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $needFk = true;
        if ($driver === 'mysql') {
            $dbName = DB::getDatabaseName();
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
            $needFk = ! $hasFkLanding;
        } else {
            try {
                $existing = Schema::getForeignKeys($tableName);
                foreach ($existing as $fk) {
                    $cols = $fk['columns'] ?? [];
                    $refTable = $fk['foreign_table'] ?? ($fk['references_table'] ?? null);
                    if (in_array('landing_page_id', $cols, true) && $refTable === 'landing_pages') {
                        $needFk = false;
                        break;
                    }
                }
            } catch (\Throwable $e) {
                $needFk = true;
            }
        }

        if ($needFk) {
            try {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreign('landing_page_id')
                        ->references('id')
                        ->on('landing_pages')
                        ->onDelete('cascade');
                });
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    public function down(): void
    {
        $tableName = 'social_links';
        $driver = DB::getDriverName();

        $hasFkLanding = false;
        $fkNameToDrop = null;

        if ($driver === 'mysql') {
            $dbName = DB::getDatabaseName();
            $row = DB::selectOne(
                "SELECT CONSTRAINT_NAME
                 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = ?
                   AND TABLE_NAME = ?
                   AND COLUMN_NAME = 'landing_page_id'
                   AND REFERENCED_TABLE_NAME = 'landing_pages'
                 LIMIT 1",
                [$dbName, $tableName]
            );
            if ($row) {
                $hasFkLanding = true;
                $fkNameToDrop = $row->CONSTRAINT_NAME;
            }
        } else {
            try {
                $existing = Schema::getForeignKeys($tableName);
                foreach ($existing as $fk) {
                    $cols = $fk['columns'] ?? [];
                    $refTable = $fk['foreign_table'] ?? ($fk['references_table'] ?? null);
                    if (in_array('landing_page_id', $cols, true) && $refTable === 'landing_pages') {
                        $hasFkLanding = true;
                        $fkNameToDrop = $fk['name'];
                        break;
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if ($hasFkLanding) {
            try {
                if ($driver === 'mysql' && $fkNameToDrop) {
                    DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$fkNameToDrop}`");
                } else {
                    Schema::table($tableName, function (Blueprint $table) use ($fkNameToDrop) {
                        if ($fkNameToDrop) {
                            $table->dropForeign($fkNameToDrop);
                        } else {
                            $table->dropForeign(['landing_page_id']);
                        }
                    });
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if (Schema::hasColumn($tableName, 'landing_page_id')
            && ! Schema::hasColumn($tableName, 'profile_id')) {
            try {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('landing_page_id', 'profile_id');
                });
            } catch (\Throwable $e) {
                try {
                    DB::statement("ALTER TABLE `{$tableName}` RENAME COLUMN `landing_page_id` TO `profile_id`");
                } catch (\Throwable $e2) {
                    // ignore
                }
            }
        }
    }
};
