<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\EntitlementService;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Deactivate free accounts whose window has ended without a card purchase.
 *
 * A free account (users.subscription_plan = 'free', subscription_active = true) is
 * only granted a limited window — config('payment.subscriptions.trial_days') days,
 * written to users.subscription_end_date when the plan is chosen in onboarding.
 *
 * Once that window has passed and the user still holds no entitled NFC card, the
 * account is switched to inactive (subscription_active = false) and the moment is
 * recorded in users.deactivated_at. The user is notified in-app (and by e-mail,
 * because the notification is high priority).
 *
 * This is a SOFT deactivation: login is never blocked — the account simply loses the
 * free-plan entitlement until the user purchases a card.
 *
 * Idempotent: a deactivated row no longer matches the query, so re-running has no effect.
 */
class DeactivateExpiredFreePlans extends Command
{
    protected $signature = 'free-plans:deactivate-expired {--dry-run : Report only, do not deactivate}';
    protected $description = 'Deactivate free accounts whose limited window ended without an NFC card purchase.';

    public function __construct(
        private readonly EntitlementService $entitlements,
        private readonly NotificationService $notifications,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $trialDays = (int) config('payment.subscriptions.trial_days', 14);
        $cutoff = now()->subDays($trialDays);

        $this->info(
            ($dryRun ? '[DRY RUN] ' : '').
            "Deactivating free accounts whose window ended on or before {$cutoff->toDateString()}..."
        );

        $deactivated = 0;
        $keptForCards = 0;

        User::query()
            ->where('subscription_plan', 'free')
            ->where('subscription_active', true)
            ->whereNotNull('subscription_end_date')
            ->whereDate('subscription_end_date', '<=', $cutoff->toDateString())
            ->chunkById(200, function ($users) use ($dryRun, $trialDays, &$deactivated, &$keptForCards) {
                foreach ($users as $user) {
                    /** @var User $user */
                    // A user who bought an NFC card keeps their entitlement regardless
                    // of the plan label on the user row.
                    if ($this->entitlements->hasEligibleCard($user)) {
                        $keptForCards++;
                        continue;
                    }

                    if ($dryRun) {
                        $this->line("Would deactivate user {$user->id} (free window ended {$user->subscription_end_date})");
                        $deactivated++;
                        continue;
                    }

                    $user->update([
                        'subscription_active' => false,
                        'deactivated_at' => now(),
                    ]);

                    try {
                        $this->notifications->create($user, 'free_plan_deactivated', [
                            'trial_days' => $trialDays,
                            'action_url' => '/Homepage/pricing',
                            'action_text' => 'Get an NFC card',
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('Failed to notify user of free-plan deactivation', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    $deactivated++;
                }
            });

        $this->table(['Result', 'Count'], [
            ['Deactivated', $deactivated],
            ['Kept (has NFC card)', $keptForCards],
        ]);

        Log::info('free-plans:deactivate-expired completed', [
            'dry_run' => $dryRun,
            'deactivated' => $deactivated,
            'kept_for_cards' => $keptForCards,
        ]);

        return Command::SUCCESS;
    }
}
