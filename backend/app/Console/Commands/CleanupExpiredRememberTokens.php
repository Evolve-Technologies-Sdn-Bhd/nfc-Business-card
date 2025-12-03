<?php

namespace App\Console\Commands;

use App\Models\RememberToken;
use Illuminate\Console\Command;

class CleanupExpiredRememberTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remember-tokens:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired remember me tokens from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning up expired remember me tokens...');

        $deletedCount = RememberToken::deleteExpired();

        $this->info("Deleted {$deletedCount} expired remember me token(s).");

        return Command::SUCCESS;
    }
}
