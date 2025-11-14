<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\NfcTag;
use App\Models\NfcCard;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user (or find existing)
        $user = User::firstOrCreate(
            ['email' => 'john@example.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'password' => Hash::make('password'),
                'company' => 'Tech Corp',
                'job_title' => 'Software Engineer',
                'subscription_plan' => 'free',
                'subscription_active' => true,
            ]
        );

        // Create NFC Card for test user
        $nfcCard = NfcCard::create([
            'user_id' => $user->id,
            'card_owner' => $user->full_name,
            'billing_address' => '123 Tech Street, San Francisco, CA 94105',
            'contact_number' => '+1234567890',
            'purchase_date' => now(),
            'subscription_plan' => 'free',
            'status' => 'active',
        ]);

        // Create Landing Page for the NFC Card
        \App\Models\LandingPage::create([
            'nfc_card_id' => $nfcCard->id,
            'name' => $user->full_name,
            'title' => $user->job_title,
            'company_name' => $user->company,
            'email' => $user->email,
            'phone' => '+1234567890',
            'bio' => 'Passionate software engineer with 5+ years of experience.',
            'is_active' => true,
        ]);

        // Create NFC tag (or find existing)
        NfcTag::firstOrCreate(
            ['nfc_id' => 'test-nfc-123'],
            [
                'user_id' => $user->id,
                'name' => 'Business Card',
                'status' => 'active',
                'tap_count' => 0,
            ]
        );

        // Run NFC Card seeder for development
        $this->call([
            NfcCardSeeder::class,
        ]);

        // Create admin users
        $this->call([
            AdminUserSeeder::class,
        ]);

        $this->command->info('Sample data created successfully!');
        $this->command->info('Test user: john@example.com / password');
    }
}
