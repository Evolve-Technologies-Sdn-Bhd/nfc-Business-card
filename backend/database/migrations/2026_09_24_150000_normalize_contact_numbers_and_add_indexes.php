<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function isSqlite(): bool
    {
        return Schema::getConnection()->getDriverName() === 'sqlite';
    }

    private function getExistingIndexNames(string $table): \Illuminate\Support\Collection
    {
        try {
            if ($this->isSqlite()) {
                $rows = DB::select(
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

    private function safeCreateIndex(string $table, $columns, string $indexName): void
    {
        if ($this->hasIndex($table, $indexName)) {
            return;
        }
        try {
            Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                $t->index($columns, $indexName);
            });
        } catch (\Throwable) {
            // ignore
        }
    }

    private function safeDropIndex(string $table, string $indexName): void
    {
        if (!$this->hasIndex($table, $indexName)) {
            return;
        }
        try {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropIndex($indexName);
            });
        } catch (\Throwable) {
            // ignore
        }
    }

    private function normalizeColumnPhp(string $table, string $column, string $primaryKey = 'id'): void
    {
        DB::table($table)
            ->whereNotNull($column)
            ->where($column, '<>', '')
            ->select([$primaryKey, $column])
            ->chunkById(100, function ($rows) use ($table, $column, $primaryKey) {
                foreach ($rows as $row) {
                    $raw = $row->{$column};
                    if ($raw === null || $raw === '') {
                        continue;
                    }
                    $normalized = preg_replace('/[^0-9]/', '', (string) $raw);
                    if ($normalized !== (string) $raw) {
                        DB::table($table)
                            ->where($primaryKey, $row->{$primaryKey})
                            ->update([$column => $normalized]);
                    }
                }
            }, $primaryKey);
    }

    private function normalizeColumnSql(string $table, string $column): void
    {
        DB::statement("
            UPDATE {$table}
            SET {$column} = REGEXP_REPLACE(COALESCE({$column}, ''), '[^0-9]', '')
            WHERE {$column} IS NOT NULL AND {$column} <> ''
        ");
    }

    private function normalizeColumn(string $table, string $column, string $primaryKey = 'id'): void
    {
        if ($this->isSqlite()) {
            $this->normalizeColumnPhp($table, $column, $primaryKey);
        } else {
            $this->normalizeColumnSql($table, $column);
        }
    }

    /**
     * Run the migrations.
     *
     * Normalizes phone number columns (strips non-digit characters)
     * and adds performance indexes for phone-number-based lookups.
     */
    public function up(): void
    {
        // -------- Normalize existing contact_number values in nfc_cards --------
        $this->normalizeColumn('nfc_cards', 'contact_number');

        // -------- Normalize phone numbers in users (for reference consistency) --------
        $this->normalizeColumn('users', 'phone');

        // -------- Normalize phone numbers in landing_pages (for reference consistency) --------
        $this->normalizeColumn('landing_pages', 'phone');
        $this->normalizeColumn('landing_pages', 'phone_number');
        $this->normalizeColumn('landing_pages', 'whatsapp_number');
        $this->normalizeColumn('landing_pages', 'company_whatsapp');

        // -------- Add performance indexes for nfc_cards --------
        $this->safeCreateIndex('nfc_cards', 'contact_number', 'nfc_cards_contact_number_index');
        $this->safeCreateIndex('nfc_cards', ['status', 'contact_number'], 'nfc_cards_status_contact_number_index');

        // -------- Optional: index users.phone for quick cross-reference lookups --------
        $this->safeCreateIndex('users', 'phone', 'users_phone_index');
    }

    /**
     * Reverse the migrations.
     *
     * NOTE: We cannot restore original formatting of phone numbers (stripped characters are lost).
     *       This only rolls back the added indexes. Data normalization is intentionally irreversible.
     */
    public function down(): void
    {
        $this->safeDropIndex('nfc_cards', 'nfc_cards_status_contact_number_index');
        $this->safeDropIndex('nfc_cards', 'nfc_cards_contact_number_index');
        $this->safeDropIndex('users', 'users_phone_index');
    }
};
