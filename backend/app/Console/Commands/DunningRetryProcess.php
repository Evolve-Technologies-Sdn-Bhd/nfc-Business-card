<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Dunning / Retry process for subscriptions with status == past_due.
 *
 * Retry schedule (configurable in DunningLog rows):
 *   Attempt 1 → immediate (already done by ProcessSubscriptionRenewals)
 *   Attempt 2 → +2 days after first failure
 *   Attempt 3 → +5 days after attempt 2
 * After 3 total failures: subscription transitions -> suspended -> cancelled after 14 more days
 *
 * As with ProcessSubscriptionRenewals, the actual charge() call is a plugin-point for
 * the real card-on-file integration. This command implements the state machine +
 * populates DunningLogs accordingly.
 */
class DunningRetryProcess extends Command
{
    protected $signature = 'dunning:process-retries {--dry-run : Log without applying writes}';
    protected $description = 'Retry failed subscription payments; suspend/cancel after max attempts.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->info(($dryRun ? '[DRY RUN] ' : '').'Processing dunning retries...');

        $pastDue = Subscription::query()
            ->with(['user', 'dunningLogs'])
            ->where('status', 'past_due')
            ->orderBy('updated_at')
            ->get();

        $retriesScheduled = 0;
        $retriesAttempted = 0;
        $movedToSuspended = 0;
        $movedToCancelled = 0;

        foreach ($pastDue as $sub) {
            try {
                $logs = $sub->dunningLogs->sortBy('attempt_number');
                $lastAttempt = $logs->last();
                $attemptCount = $logs->count();
                $now = now();

                // Already too many total attempts → cancel after grace period
                if ($attemptCount >= 3) {
                    // Grace period: 14 days from last attempt → cancel permanently
                    $graceUntil = $lastAttempt?->next_retry_at ?? $lastAttempt?->created_at ?? $sub->updated_at;
                    if ($graceUntil && $now->diffInDays($graceUntil, false) >= 14) {
                        DB::transaction(function () use ($sub, $dryRun, &$movedToCancelled) {
                            if (!$dryRun) {
                                $sub->cancel('max_dunning_attempts_exceeded');
                            }
                            $movedToCancelled++;
                        });
                        continue;
                    }

                    // Still in grace period of last dunning, but 3 attempts exhausted → SUSPEND
                    if ($sub->status !== 'suspended') {
                        DB::transaction(function () use ($sub, $dryRun, &$movedToSuspended) {
                            if (!$dryRun) {
                                $sub->suspend();
                            }
                            $movedToSuspended++;
                        });
                    }
                    continue;
                }

                // Next retry schedule based on attempt count 1/2
                $nextRetryAt = $lastAttempt?->next_retry_at
                    ?? $sub->updated_at
                        ->copy()
                        ->addDays($attemptCount === 0 ? 2 : 5);

                if (!$now->gte($nextRetryAt)) {
                    $retriesScheduled++;
                    continue;
                }

                // Perform retry now
                DB::transaction(function () use ($sub, $dryRun, $attemptCount, &$retriesAttempted) {
                    $retriesAttempted++;
                    $succeeded = false; // TODO: wire to actual payment gateway card-on-file retry

                    if ($succeeded) {
                        if (!$dryRun) {
                            $sub->reactivate();
                            $sub->update([
                                'next_billing_date' => now(),
                                'current_period_start' => now(),
                                'current_period_end' => now()->addMonth(),
                            ]);
                        }
                    } else {
                        if (!$dryRun) {
                            $thisAttempt = $attemptCount + 1;
                            $retryInDays = $thisAttempt <= 2 ? 2 : 5;
                            try {
                                $sub->dunningLogs()->create([
                                    'user_id' => $sub->user_id,
                                    'attempt_number' => $thisAttempt,
                                    'status' => 'failed',
                                    'error_message' => 'Dunning retry failed (no payment method auto-debit).',
                                    'next_retry_at' => now()->addDays($retryInDays),
                                ]);
                            } catch (\Throwable) { /* dunning relation optional */ }
                        }
                    }
                });
            } catch (\Throwable $e) {
                Log::error('Dunning retry process failed for sub '.$sub->id, ['e' => $e->getMessage()]);
            }
        }

        $this->table(['Metric', 'Count'], [
            ['Past-due subscriptions scanned', $pastDue->count()],
            ['Waiting (retry window not reached yet)', $retriesScheduled],
            ['Retries attempted this run', $retriesAttempted],
            ['Suspended (3 fails, in grace period)', $movedToSuspended],
            ['Permanently cancelled (grace expired)', $movedToCancelled],
        ]);

        Log::info('dunning:process-retries done', [
            'scanned' => $pastDue->count(),
            'scheduled' => $retriesScheduled,
            'attempted' => $retriesAttempted,
            'suspended' => $movedToSuspended,
            'cancelled' => $movedToCancelled,
            'dry_run' => $dryRun,
        ]);

        return Command::SUCCESS;
    }
}
