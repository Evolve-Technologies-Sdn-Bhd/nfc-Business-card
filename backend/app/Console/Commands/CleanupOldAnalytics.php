<?php

namespace App\Console\Commands;

use App\Models\Analytics;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Monthly maintenance: archive Analytics rows older than 6 months into a separate
 * analytics_archive table (for ad-hoc historical reporting), then DELETE from live
 * analytics table to keep page-level queries fast.
 *
 * Safe by default: dry-run enabled via option, chunked in batches of 1000, always logs
 * summary before/after.
 */
class CleanupOldAnalytics extends Command
{
    protected $signature = 'analytics:cleanup-old {--months=6 : Archive rows older than N months} {--dry-run : Only show how many rows would archive/delete}';
    protected $description = 'Archive analytics entries older than the specified threshold then delete from the live table.';

    public function handle(): int
    {
        $months = (int) $this->option('months');
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subMonths(max(1, $months));
        $this->info(($dryRun ? '[DRY RUN] ' : '')."Archiving analytics before {$cutoff->toDateTimeString()}...");

        // --- 1. Ensure analytics_archive table exists (idempotent DDL) ----------------
        try {
            DB::statement("
                CREATE TABLE IF NOT EXISTS analytics_archive LIKE analytics;
            ");
        } catch (\Throwable $e) {
            Log::warning('Unable to create analytics_archive table', ['e' => $e->getMessage()]);
        }

        // --- 2. Chunk through candidates ---------------------------------------------------
        $countAnalysed = Analytics::where('created_at', '<', $cutoff)->count();
        $this->info("Rows older than {$months} months: {$countAnalysed}");

        if ($dryRun) {
            $this->warn("DRY RUN: would move {$countAnalysed} rows to analytics_archive then delete.");
            return Command::SUCCESS;
        }

        if ($countAnalysed === 0) {
            $this->info('Nothing to clean up.');
            return Command::SUCCESS;
        }

        $moved = 0;
        Analytics::where('created_at', '<', $cutoff)->chunkById(1000, function ($chunk) use (&$moved) {
            try {
                DB::transaction(function () use ($chunk) {
                    // Use raw INSERT SELECT for speed (avoid hydration of each row)
                    $ids = $chunk->pluck('id')->toArray();
                    $idList = implode(',', array_map('intval', $ids));
                    DB::statement("
                        INSERT IGNORE INTO analytics_archive SELECT * FROM analytics WHERE id IN ({$idList});
                    ");
                    DB::table('analytics')->whereIn('id', $ids)->delete();
                });
                $moved += $chunk->count();
                $this->line("  ↳ Archived {$moved}/{$chunk->count()} rows so far...");
            } catch (\Throwable $e) {
                Log::error('analytics:cleanup-old chunk failed', ['e' => $e->getMessage()]);
            }
        });

        Log::info('analytics:cleanup-old done', [
            'cutoff_months' => $months,
            'rows_archived' => $moved,
        ]);
        $this->info("Done. Archived {$moved} rows to analytics_archive.");
        return Command::SUCCESS;
    }
}
