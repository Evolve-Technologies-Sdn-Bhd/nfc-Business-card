<?php

namespace App\Observers;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Write-through cache observer: whenever a Subscription row changes,
 * propagate the authorative values back to users.subscription_* legacy columns.
 * This keeps existing queries/lookups (and older code paths) accurate while
 * the subscriptions table remains the single source of truth.
 */
class SubscriptionObserver
{
    /**
     * Handle events that mutate Subscription status — fire sync.
     */
    public function saved(Subscription $subscription): void
    {
        $this->syncToUserColumns($subscription);
    }

    public function deleted(Subscription $subscription): void
    {
        $this->syncToUserColumns($subscription);
    }

    public function restored(Subscription $subscription): void
    {
        $this->syncToUserColumns($subscription);
    }

    /**
     * Recompute user subscription_* columns from the full subscriptions dataset
     * of the owning user — atomically and idempotently safe.
     */
    private function syncToUserColumns(Subscription $subscription): void
    {
        $userId = $subscription->user_id;
        if (!$userId) {
            return;
        }

        try {
            /** @var User|null $user */
            $user = User::with('subscriptions')->find($userId);
            if (!$user) {
                return;
            }

            $active = $user->subscriptions->firstWhere('status', 'active');
            $mostRecent = $user->subscriptions->sortByDesc('created_at')->first();

            // Plan name — prefer active (paid ongoing) otherwise most recent historical
            $plan = 'free';
            if ($active && !empty($active->plan_type)) {
                $plan = strtolower($active->plan_type);
            } elseif ($mostRecent && !empty($mostRecent->plan_type)) {
                $plan = strtolower($mostRecent->plan_type);
            }

            $startDate = null;
            $endDate = null;
            $isActive = false;

            if ($active) {
                $isActive = true;
                $startDate = $active->current_period_start?->toDateString()
                    ?? $active->created_at?->toDateString();
                $endDate = $active->next_billing_date?->toDateString()
                    ?? $active->current_period_end?->toDateString();
            } elseif ($mostRecent) {
                $startDate = $mostRecent->current_period_start?->toDateString()
                    ?? $mostRecent->created_at?->toDateString();
                $endDate = $mostRecent->current_period_end?->toDateString()
                    ?? $mostRecent->cancelled_at?->toDateString();
                $isActive = false;
            }

            // Raw update without mutator (avoid infinite recursion since
            // accessors also read from relations which could loop)
            $payload = [
                'subscription_plan' => $plan,
                'subscription_active' => $isActive,
            ];
            if ($startDate) {
                $payload['subscription_start_date'] = $startDate;
            }
            if ($endDate) {
                $payload['subscription_end_date'] = $endDate;
            }

            User::withoutTimestamps(static function () use ($userId, $payload) {
                User::where('id', $userId)->update($payload);
            });
        } catch (\Throwable $e) {
            Log::warning('SubscriptionObserver syncToUserColumns failed', [
                'subscription_id' => $subscription->id ?? null,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
