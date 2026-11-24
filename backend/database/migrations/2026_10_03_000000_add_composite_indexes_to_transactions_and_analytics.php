<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the missing composite indexes for the two highest-volume query paths:
 *
 *   • transactions — filtered per user by status and ranged by created_at
 *     (payment history / pending-payment listings).
 *   • analytics    — filtered by trackable + action and ranged by created_at
 *     (the exact shape used by AnalyticsController).
 *
 * Index creation is guarded so re-running on a database where the index was
 * added manually is a safe no-op.
 */
return new class extends Migration
{
    private function getExistingIndexNames(string $table): \Illuminate\Support\Collection
    {
        try {
            $connection = Schema::getConnection();
            $driver = $connection->getDriverName();
            if ($driver === 'sqlite') {
                $rows = $connection->select(
                    "SELECT name FROM sqlite_master WHERE type='index' AND tbl_name = ?",
                    [$table]
                );
                return collect($rows)->map(fn ($r) => $r->name ?? null)
                    ->filter(fn ($n) => is_string($n))
                    ->map(fn ($n) => strtolower($n))
                    ->flip();
            }
            return collect(Schema::getIndexListing($table))
                ->pluck('name')
                ->filter(fn ($name) => is_string($name) || is_int($name))
                ->map(fn ($n) => strtolower((string) $n))
                ->flip();
        } catch (\Throwable) {
            return collect();
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        return $this->getExistingIndexNames($table)->has(strtolower($indexName));
    }

    private function safeCreateIndex(string $table, array $columns, string $indexName): void
    {
        if ($this->hasIndex($table, $indexName)) {
            return;
        }
        try {
            Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        } catch (\Throwable) {
            // ignore pre-existing or driver-specific issues
        }
    }

    private function safeDropIndex(string $table, string $indexName): void
    {
        if (!$this->hasIndex($table, $indexName)) {
            return;
        }
        try {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        } catch (\Throwable) {
            // ignore
        }
    }

    public function up(): void
    {
        // transactions — user_id + status + created_at for scoped, time-ordered listings
        $this->safeCreateIndex('transactions', ['user_id', 'status', 'created_at'], 'transactions_user_id_status_created_at_index');

        // analytics — trackable_type + action + created_at for scoped, time-ranged aggregations
        $this->safeCreateIndex('analytics', ['trackable_type', 'action', 'created_at'], 'analytics_trackable_type_action_created_at_index');
    }

    public function down(): void
    {
        $this->safeDropIndex('transactions', 'transactions_user_id_status_created_at_index');
        $this->safeDropIndex('analytics', 'analytics_trackable_type_action_created_at_index');
    }
};
