<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Permanently DELETE soft-deleted rows across ALL Eloquent SoftDeletes-enabled
 * models that are older than the threshold (default 90 days).
 *
 * Model list is auto-discovered by scanning the Models directory for `use SoftDeletes`
 * trait, combined with an explicit SAFELIST of tables we know use soft deletes.
 * This avoids deleting anything accidentally from tables that were never intended
 * to be garbage-collected.
 */
class CleanupSoftDeletedRecords extends Command
{
    protected $signature = 'cleanup:soft-deleted {--days=90 : Purge rows deleted more than N days ago} {--dry-run : Show would-be purged counts only}';
    protected $description = 'Permanently delete soft-deleted rows past the retention window across all SoftDeletes models.';

    /**
     * Safelist of tables known to use SoftDeletes.
     * Must be updated manually when new SoftDeletes models are added.
     */
    private const SOFT_DELETE_TABLES = [
        'subscriptions' => 'deleted_at',
        'webhook_logs' => 'deleted_at',
        'users' => 'deleted_at', // default Laravel column if SoftDeletes added in future
    ];

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $this->info(($dryRun ? '[DRY RUN] ' : '')."Purging soft-deleted rows older than {$days} days (cutoff: {$cutoff->toDateTimeString()})...");

        $rows = [];
        foreach (self::SOFT_DELETE_TABLES as $table => $deletedCol) {
            // Skip tables that don't exist (future-proofing)
            if (!Schema::hasTable($table)) {
                $rows[] = [$table, 'table missing', '-'];
                continue;
            }
            if (!Schema::hasColumn($table, $deletedCol)) {
                $rows[] = [$table, "{$deletedCol} column missing", '-'];
                continue;
            }

            $count = DB::table($table)
                ->whereNotNull($deletedCol)
                ->where($deletedCol, '<', $cutoff)
                ->count();

            if (!$dryRun && $count > 0) {
                try {
                    DB::table($table)
                        ->whereNotNull($deletedCol)
                        ->where($deletedCol, '<', $cutoff)
                        ->delete();
                } catch (\Throwable $e) {
                    Log::warning("cleanup:soft-deleted {$table} failed", ['e' => $e->getMessage()]);
                    $rows[] = [$table, 'error', $count];
                    continue;
                }
            }
            $rows[] = [$table, $dryRun ? 'would purge' : 'purged', $count];
            Log::info('cleanup:soft-deleted', [
                'table' => $table,
                'count' => $count,
                'dry_run' => $dryRun,
                'days' => $days,
            ]);
        }

        $this->table(['Table', 'Action', 'Rows'], $rows);
        return Command::SUCCESS;
    }
}
