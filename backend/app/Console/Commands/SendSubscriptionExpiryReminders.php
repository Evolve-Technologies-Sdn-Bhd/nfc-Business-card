<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Send push/email reminders at 7 days, 3 days, and 1 day BEFORE next_billing_date.
 * Also sends TRIAL ending reminders for users with upcoming trial_ends_at.
 *
 * Idempotent: each reminder category (7d/3d/1d/trial-7d/trial-3d) is tracked via
 * subscription.metadata["reminders"] object so we never double-send.
 */
class SendSubscriptionExpiryReminders extends Command
{
    protected $signature = 'subscriptions:send-expiry-reminders';
    protected $description = 'Send 7d / 3d / 1d expiry / trial ending reminders to active subscriptions.';

    public function handle(): int
    {
        $this->info('Sending renewal & trial reminders...');

        $active = Subscription::query()
            ->with(['user'])
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNotNull('next_billing_date')
                  ->orWhereNotNull('trial_ends_at');
            })
            ->get();

        $sentRenewal = 0;
        $sentTrial = 0;

        foreach ($active as $sub) {
            try {
                /** @var User|null $user */
                $user = $sub->user;
                if (!$user instanceof User) {
                    continue;
                }

                $metadata = $sub->metadata ?? [];
                $sentBatches = $metadata['reminders'] ?? [];
                $dirty = false;

                // --------- Renewal reminders (against next_billing_date) ------------
                if ($sub->next_billing_date) {
                    $daysUntil = max(0, (int) now()->startOfDay()->diffInDays($sub->next_billing_date->startOfDay(), false));
                    foreach ([7, 3, 1] as $window) {
                        $key = "renewal_{$window}d";
                        if ($daysUntil === $window && !($sentBatches[$key] ?? false)) {
                            $this->sendRenewal($user, $sub, $window);
                            $sentBatches[$key] = now()->toIso8601String();
                            $sentRenewal++;
                            $dirty = true;
                        }
                    }
                }

                // -------- Trial ending reminders (trial_ends_at) ---------------------
                if ($sub->trial_ends_at && $sub->trial_ends_at->isFuture()) {
                    $daysUntil = max(0, (int) now()->startOfDay()->diffInDays($sub->trial_ends_at->startOfDay(), false));
                    foreach ([7, 3, 1] as $window) {
                        $key = "trial_{$window}d";
                        if ($daysUntil === $window && !($sentBatches[$key] ?? false)) {
                            $this->sendTrial($user, $sub, $window);
                            $sentBatches[$key] = now()->toIso8601String();
                            $sentTrial++;
                            $dirty = true;
                        }
                    }
                }

                if ($dirty) {
                    $metadata['reminders'] = $sentBatches;
                    $sub->metadata = $metadata;
                    $sub->save();
                }
            } catch (\Throwable $e) {
                Log::warning('Expiry reminder failed for sub '.$sub->id, ['e' => $e->getMessage()]);
            }
        }

        $this->table(['Category', 'Sent'], [
            ['Renewal (7d / 3d / 1d)', $sentRenewal],
            ['Trial ending (7d / 3d / 1d)', $sentTrial],
        ]);

        Log::info('subscriptions:send-expiry-reminders completed', [
            'renewal_sent' => $sentRenewal,
            'trial_sent' => $sentTrial,
            'active' => $active->count(),
        ]);

        return Command::SUCCESS;
    }

    private function sendRenewal(User $user, Subscription $sub, int $days): void
    {
        $title = 'Upcoming Subscription Payment';
        if ($days === 1) {
            $msg = "Your {$sub->plan_name} subscription will be charged today. Please ensure your payment method is active.";
        } else {
            $msg = "Your {$sub->plan_name} subscription will be charged in {$days} days. Amount: {$sub->amount} {$sub->currency}.";
        }
        Notification::create([
            'user_id' => $user->id,
            'type' => 'info',
            'title' => $title,
            'message' => $msg,
            'data' => [
                'reminder_type' => 'renewal',
                'days' => $days,
                'plan' => $sub->plan_name,
                'amount' => $sub->amount,
                'currency' => $sub->currency,
                'next_billing_date' => $sub->next_billing_date?->toDateString(),
            ],
            'priority' => $days === 1 ? 'urgent' : 'normal',
        ]);
    }

    private function sendTrial(User $user, Subscription $sub, int $days): void
    {
        if ($days === 1) {
            $title = 'Trial Period Ends Today';
            $msg = "Your {$sub->plan_name} trial period ends today. Please upgrade to a paid plan to continue enjoying all premium features.";
        } else {
            $title = 'Trial Period Ending Soon';
            $msg = "Your {$sub->plan_name} trial period will end in {$days} days. Upgrade to a paid plan before it expires.";
        }
        Notification::create([
            'user_id' => $user->id,
            'type' => 'warning',
            'title' => $title,
            'message' => $msg,
            'data' => [
                'reminder_type' => 'trial',
                'days' => $days,
                'plan' => $sub->plan_name,
                'trial_ends_at' => $sub->trial_ends_at?->toDateString(),
            ],
            'priority' => $days === 1 ? 'urgent' : 'normal',
        ]);
    }
}
