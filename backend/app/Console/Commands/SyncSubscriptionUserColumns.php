<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * INTERIM REPAIR job (run hourly until v2 migration is complete).
 *
 * Problem: subscription_plan/subscription_active exist on both `users` table AND the new
 * canonical `subscriptions` table. Some legacy code paths update users directly
 * instead of creating a Subscription row. This reconciles them:
 *
 *   Rule A (writes go to subscriptions table first — our SSoT):
 *     For each user, look up their ACTIVE subscription row and write-through the
 *     users.subscription_* columns. This is the "subscriptions → users" direction.
 *
 *   Rule B (recovery for legacy users who do NOT yet have a row in subscriptions):
 *     If users.subscription_plan is paid (basic/premium/business) AND
 *     users.subscription_active = true AND no active subscription row exists,
 *     CREATE an equivalent subscriptions record based on the user row — so the new
 *     SSoT has parity with legacy data.
 *
 * This job becomes redundant after 30-60 days once all users are covered; it is
 * intentionally safe to run multiple times / every hour.
 */
class SyncSubscriptionUserColumns extends Command
{
    protected $signature = 'subscriptions:sync-user-columns {--dry-run : No writes, report only}';
    protected $description = 'Hourly repair job: keep users.subscription_* columns synced FROM the subscriptions SSoT and backfill missing rows.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->info(($dryRun ? '[DRY RUN] ' : '').'Running user ↔ subscriptions reconciliation...');

        $usersSynced = 0;
        $subscriptionsCreated = 0;

        User::query()
            ->with(['subscriptions'])
            ->chunkById(500, function ($chunk) use ($dryRun, &$usersSynced, &$subscriptionsCreated) {
                foreach ($chunk as $user) {
                    try {
                        $active = $user->subscriptions->firstWhere('status', 'active');
                        $legacyActive = (bool) $user->getRawOriginal('subscription_active');
                        $legacyPlan = strtolower((string) ($user->getRawOriginal('subscription_plan') ?? 'free'));

                        // ----- Rule B: Create Subscription row when legacy says "paid" but SSoT is empty
                        if (!$active && $legacyActive && in_array($legacyPlan, ['basic', 'premium', 'business'], true)) {
                            $this->info("Creating missing subscription for user {$user->id} (plan={$legacyPlan})");
                            if (!$dryRun) {
                                DB::transaction(function () use ($user, $legacyPlan, &$subscriptionsCreated) {
                                    $sub = Subscription::create([
                                        'user_id' => $user->id,
                                        'plan_name' => ucfirst($legacyPlan),
                                        'plan_type' => $legacyPlan,
                                        'provider' => 'legacy_backfill',
                                        'amount' => $this->defaultAmountForPlan($legacyPlan),
                                        'currency' => 'MYR',
                                        'interval' => 'monthly',
                                        'status' => 'active',
                                        'current_period_start' => $user->getRawOriginal('subscription_start_date') ?? now()->subMonth(),
                                        'current_period_end' => $user->getRawOriginal('subscription_end_date') ?? now()->addMonth(),
                                        'next_billing_date' => $user->getRawOriginal('subscription_end_date') ?? now()->addMonth(),
                                        'trial_ends_at' => null,
                                        'metadata' => ['migrated_from' => 'users_table_backfill', 'user_id' => $user->id],
                                    ]);
                                    SubscriptionObserver::saved($sub); // force write-through
                                    $subscriptionsCreated++;
                                });
                            } else {
                                $subscriptionsCreated++;
                            }
                        }

                        // ----- Rule A: write-through users columns (mirrors observer logic)
                        $changed = $this->mirrorFromSubscriptions($user, $dryRun);
                        if ($changed) {
                            $usersSynced++;
                        }
                    } catch (\Throwable $e) {
                        Log::warning('sync-user-columns failed for user '.$user->id, ['e' => $e->getMessage()]);
                    }
                }
            });

        $this->table(['Metric', 'Count'], [
            ['User rows synced (users.subscription_* → via observer)', $usersSynced],
            ['Missing subscriptions created from legacy user rows', $subscriptionsCreated],
        ]);
        Log::info('subscriptions:sync-user-columns done', [
            'synced' => $usersSynced,
            'created' => $subscriptionsCreated,
            'dry_run' => $dryRun,
        ]);
        return Command::SUCCESS;
    }

    /**
     * Reflect active subscription onto users columns (without using accessors).
     * Returns true if the user row needed updating.
     */
    private function mirrorFromSubscriptions(User $user, bool $dryRun): bool
    {
        $subs = $user->subscriptions;
        $active = $subs->firstWhere('status', 'active');
        $mostRecent = $subs->sortByDesc('created_at')->first() ?? null;

        $plan = 'free';
        if ($active && !empty($active->plan_type)) {
            $plan = strtolower($active->plan_type);
        } elseif ($mostRecent && !empty($mostRecent->plan_type)) {
            $plan = strtolower($mostRecent->plan_type);
        }

        $isActive = $active ? true : false;
        $start = $active?->current_period_start ?? $mostRecent?->current_period_start ?? null;
        $end = $active?->next_billing_date ?? $active?->current_period_end ?? $mostRecent?->current_period_end ?? null;

        $newPayload = [
            'subscription_plan' => $plan,
            'subscription_active' => $isActive,
            'subscription_start_date' => $start?->toDateString() ?: null,
            'subscription_end_date' => $end?->toDateString() ?: null,
        ];

        $original = [
            'subscription_plan' => strtolower($user->getRawOriginal('subscription_plan') ?? 'free'),
            'subscription_active' => (bool) ($user->getRawOriginal('subscription_active') ?? 0),
            'subscription_start_date' => $user->getRawOriginal('subscription_start_date'),
            'subscription_end_date' => $user->getRawOriginal('subscription_end_date'),
        ];

        if (json_encode($newPayload, JSON_THROW_ON_ERROR) === json_encode($original, JSON_THROW_ON_ERROR)) {
            return false;
        }

        if (!$dryRun) {
            User::withoutTimestamps(function () use ($user, $newPayload) {
                $user->newQuery()->where('id', $user->id)->update($newPayload);
            });
        }

        return true;
    }

    /**
     * Baseline default amount for each plan (used only for legacy-backfilled subscriptions
     * where the real payment amount is no longer available).
     */
    private function defaultAmountForPlan(string $plan): float
    {
        return match ($plan) {
            'basic' => 19.00,
            'premium' => 49.00,
            'business' => 199.00,
            default => 0.00,
        };
    }
}
