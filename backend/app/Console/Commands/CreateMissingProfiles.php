<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\NfcCard;
use App\Models\LandingPage;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateMissingProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'landingpages:create-missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create landing pages for NFC cards that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all NFC cards without landing pages
        $nfcCards = NfcCard::whereDoesntHave('landingPage')->with('user')->get();

        if ($nfcCards->isEmpty()) {
            $this->info('All NFC cards already have landing pages.');
            return 0;
        }

        $this->info("Found {$nfcCards->count()} NFC cards without landing pages.");

        $bar = $this->output->createProgressBar($nfcCards->count());
        $bar->start();

        foreach ($nfcCards as $nfcCard) {
            $user = $nfcCard->user;
            
            LandingPage::create([
                'nfc_card_id' => $nfcCard->id,
                'name' => $user->full_name ?? $user->email,
                'email' => $user->email,
                'title' => $user->job_title,
                'company_name' => $user->company,
                'is_active' => true,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info('Successfully created landing pages for all NFC cards.');

        return 0;
    }
}
