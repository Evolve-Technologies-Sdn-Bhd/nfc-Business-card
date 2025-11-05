<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateMissingProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profiles:create-missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create profiles for users who don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereDoesntHave('profile')->get();

        if ($users->isEmpty()) {
            $this->info('All users already have profiles.');
            return 0;
        }

        $this->info("Found {$users->count()} users without profiles.");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $user->profile()->create([
                'name' => $user->full_name ?? $user->email,
                'email' => $user->email,
                'slug' => Str::slug(($user->full_name ?? $user->email) . '-' . $user->id),
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info('Successfully created profiles for all users.');

        return 0;
    }
}
