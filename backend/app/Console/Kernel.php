<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // -----------------------------------------------------------
        // BILLING & SUBSCRIPTIONS
        // -----------------------------------------------------------
        // Hourly: repair/backfill users.subscription_* columns from canonical subscriptions table
        // (interim job — can be removed 30-60 days after the SSoT migration goes live)
        $schedule->command('subscriptions:sync-user-columns')->hourlyAt(17);

        // Daily 00:05 — automatic subscription renewals for rows whose next_billing_date == today
        $schedule->command('subscriptions:process-renewals')->dailyAt('00:05');

        // Daily 09:30 — email reminder 7d / 3d / 1d before renewal or trial expiry
        $schedule->command('subscriptions:send-expiry-reminders')->dailyAt('09:30');

        // Every 6 hours — retry failed subscription charges (dunning pipeline)
        $schedule->command('dunning:process-retries')->cron('20 */6 * * *');

        // -----------------------------------------------------------
        // MAINTENANCE & DATA RETENTION
        // -----------------------------------------------------------
        // Daily 02:00 — clean up expired password reset tokens
        $schedule->call(function () {
            $deleted = \App\Models\PasswordReset::cleanupExpired();
            \Log::info("Cleaned up {$deleted} expired password reset tokens");
        })->dailyAt('02:00');

        // Daily 02:15 — clean up "remember me" tokens past their TTL
        $schedule->command('remember-tokens:cleanup')->dailyAt('02:15');

        // 1st of every month 03:00 — archive analytics >6 months old (archive + delete live)
        $schedule->command('analytics:cleanup-old --months=6')->monthlyOn(1, '03:00');

        // Weekly Sunday 03:30 — permanently purge soft-deleted rows past 90-day retention
        $schedule->command('cleanup:soft-deleted --days=90')->weeklyOn(0, '03:30');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
