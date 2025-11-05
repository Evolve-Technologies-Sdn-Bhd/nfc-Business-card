<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\NfcCard;
use App\Models\NfcTag;
use App\Models\Analytics;
use Carbon\Carbon;

class NfcCardSeeder extends Seeder
{
    public function run()
    {
        // Get or create a test user
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'password' => bcrypt('password'),
                'subscription_plan' => 'pro',
                'subscription_active' => true,
                'subscription_start_date' => Carbon::now()->subMonths(2),
                'subscription_end_date' => Carbon::now()->addMonths(10),
            ]
        );

        // Create sample NFC cards
        $nfcCards = [
            [
                'card_owner' => 'John Doe',
                'billing_address' => '123 Main St, New York, NY 10001',
                'contact_number' => '+1-555-0123',
                'purchase_date' => Carbon::now()->subMonths(2),
                'subscription_plan' => 'pro',
                'purchase_amount' => 49.00,
                'payment_method' => 'Credit Card',
                'shipping_address' => '123 Main St, New York, NY 10001',
                'tracking_number' => 'TRK123456789',
                'shipped_date' => Carbon::now()->subMonths(2)->addDays(3),
                'delivered_date' => Carbon::now()->subMonths(2)->addDays(7),
                'status' => 'active',
                'notes' => 'First business card order'
            ],
            [
                'card_owner' => 'John Doe',
                'billing_address' => '123 Main St, New York, NY 10001',
                'contact_number' => '+1-555-0123',
                'purchase_date' => Carbon::now()->subMonth(),
                'subscription_plan' => 'enterprise',
                'purchase_amount' => 99.00,
                'payment_method' => 'Credit Card',
                'shipping_address' => '456 Business Ave, New York, NY 10002',
                'tracking_number' => 'TRK987654321',
                'shipped_date' => Carbon::now()->subMonth()->addDays(2),
                'delivered_date' => Carbon::now()->subMonth()->addDays(5),
                'status' => 'active',
                'notes' => 'Enterprise card for business meetings'
            ]
        ];

        foreach ($nfcCards as $index => $cardData) {
            $nfcCard = NfcCard::create([
                'user_id' => $user->id,
                'nfc_card_id' => 'NFC-' . strtoupper(substr(md5($index . time()), 0, 12)),
                ...$cardData
            ]);

            // Create associated NFC tag
            $nfcTag = NfcTag::create([
                'user_id' => $user->id,
                'nfc_id' => 'NFC' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                'nfc_card_id' => $nfcCard->nfc_card_id,
                'name' => $cardData['card_owner'] . "'s Card",
                'status' => $cardData['status'],
                'tap_count' => rand(10, 150),
                'last_tapped_at' => Carbon::now()->subDays(rand(1, 30))
            ]);

            // Create sample analytics data
            for ($i = 0; $i < rand(5, 20); $i++) {
                Analytics::create([
                    'trackable_type' => 'App\Models\NfcTag',
                    'trackable_id' => $nfcTag->id,
                    'action' => 'nfc_tap',
                    'ip_address' => '192.168.1.' . rand(1, 255),
                    'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15',
                    'device_type' => ['mobile', 'desktop', 'tablet'][rand(0, 2)],
                    'browser' => ['Chrome', 'Safari', 'Firefox'][rand(0, 2)],
                    'platform' => ['iOS', 'Android', 'macOS', 'Windows'][rand(0, 3)],
                    'created_at' => Carbon::now()->subDays(rand(1, 60))
                ]);
            }
        }

        // Create a free user for comparison
        $freeUser = User::firstOrCreate(
            ['email' => 'free@example.com'],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'password' => bcrypt('password'),
                'subscription_plan' => 'free',
                'subscription_active' => false,
            ]
        );

        $this->command->info('NFC Card sample data created successfully!');
        $this->command->info('Pro user: test@example.com / password');
        $this->command->info('Free user: free@example.com / password');
    }
} 