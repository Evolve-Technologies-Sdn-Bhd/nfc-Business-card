<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Purge guest-checkout placeholder accounts that were never used.
 *
 * A placeholder is created the moment a visitor presses Pay (see
 * GuestCheckoutController). Some are abandoned before any payment attempt is
 * recorded at all, leaving rows that can never become real accounts and that
 * keep holding on to an email address.
 *
 * Deliberately narrow: only rows with the placeholder signature AND no order,
 * no transaction and no password are removed. Anything with history is left
 * alone (register() can still claim it if it was never paid for), so this can
 * never destroy an order or a paid account.
 */
class PurgeAbandonedGuestCheckouts extends Command
{
    protected $signature = 'guest-checkouts:cleanup {--hours=48 : Age in hours before a placeholder is considered abandoned}';

    protected $description = 'Delete unused guest-checkout placeholder accounts (no password, no order, no transaction)';

    public function handle()
    {
        $hours = max(1, (int) $this->option('hours'));
        $cutoff = now()->subHours($hours);

        $this->info("Purging guest-checkout placeholders older than {$hours} hour(s)...");

        $users = User::query()
            ->whereNull('password')
            ->whereNull('provider')
            ->where('subscription_active', false)
            ->where('created_at', '<', $cutoff)
            ->whereDoesntHave('transactions')
            ->whereDoesntHave('orders')
            ->get();

        $deleted = 0;

        foreach ($users as $user) {
            // Sanctum tokens are polymorphic with no FK, so clear them first.
            $user->tokens()->delete();
            $user->delete();
            $deleted++;
        }

        $this->info("Deleted {$deleted} abandoned guest-checkout placeholder(s).");

        return Command::SUCCESS;
    }
}
