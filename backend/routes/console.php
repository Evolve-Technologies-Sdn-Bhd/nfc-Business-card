<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| This project runs Laravel 12, where the scheduler is defined here (and in
| bootstrap/app.php) instead of the legacy app/Console/Kernel.php, which the
| framework no longer reads. These definitions are the single source of truth
| for scheduled work.
|
*/

// -----------------------------------------------------------
// BILLING & SUBSCRIPTIONS
// -----------------------------------------------------------
// Hourly: repair/backfill users.subscription_* columns from canonical subscriptions table
// (interim job — can be removed 30-60 days after the SSoT migration goes live)
Schedule::command('subscriptions:sync-user-columns')->hourlyAt(17);

// Daily 00:05 — automatic subscription renewals for rows whose next_billing_date == today
Schedule::command('subscriptions:process-renewals')->dailyAt('00:05');

// Daily 09:30 — email reminder 7d / 3d / 1d before renewal or trial expiry
Schedule::command('subscriptions:send-expiry-reminders')->dailyAt('09:30');

// Every 6 hours — retry failed subscription charges (dunning pipeline)
Schedule::command('dunning:process-retries')->cron('20 */6 * * *');

// Daily 00:30 — deactivate free accounts whose limited window ended without
// an NFC card purchase (soft: the account is marked inactive, not blocked)
Schedule::command('free-plans:deactivate-expired')->dailyAt('00:30');

// -----------------------------------------------------------
// MAINTENANCE & DATA RETENTION
// -----------------------------------------------------------
// Daily 02:00 — clean up expired password reset tokens
Schedule::call(function () {
    $deleted = \App\Models\PasswordReset::cleanupExpired();
    \Log::info("Cleaned up {$deleted} expired password reset tokens");
})->dailyAt('02:00');

// Daily 02:15 — clean up "remember me" tokens past their TTL
Schedule::command('remember-tokens:cleanup')->dailyAt('02:15');

// Daily 02:45 — purge guest-checkout placeholder accounts that were abandoned
// before any order/transaction was recorded (they only hold an email address)
Schedule::command('guest-checkouts:cleanup --hours=48')->dailyAt('02:45');

// 1st of every month 03:00 — archive analytics >6 months old (archive + delete live)
Schedule::command('analytics:cleanup-old --months=6')->monthlyOn(1, '03:00');

// Weekly Sunday 03:30 — permanently purge soft-deleted rows past 90-day retention
Schedule::command('cleanup:soft-deleted --days=90')->weeklyOn(0, '03:30');
