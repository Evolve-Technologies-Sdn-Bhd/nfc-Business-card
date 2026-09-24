<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\NfcCard;
use App\Models\LandingPage;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $createdCount = 0;
        $skippedCount = 0;

        // ========== SUPER ADMIN ==========
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@nfcgo.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => Hash::make('admin123'),
                'company' => 'NFCGo',
                'job_title' => 'System Administrator',
                'subscription_plan' => 'business',
                'subscription_start_date' => now(),
                'subscription_end_date' => now()->addYear(),
                'subscription_active' => true,
                'is_admin' => true,
                'admin_role' => 'super_admin',
                'admin_permissions' => ['user_management', 'nfc_management', 'analytics', 'system_admin'],
            ]
        );

        if ($superAdmin->wasRecentlyCreated) {
            $createdCount++;
            $this->createAdminCardAndLanding($superAdmin);
        } else {
            $skippedCount++;
        }

        // ========== REGULAR ADMIN ==========
        $regularAdmin = User::firstOrCreate(
            ['email' => 'admin2@nfcgo.com'],
            [
                'first_name' => 'Regular',
                'last_name' => 'Admin',
                'password' => Hash::make('admin123'),
                'company' => 'NFCGo',
                'job_title' => 'Administrator',
                'subscription_plan' => 'premium',
                'subscription_start_date' => now(),
                'subscription_end_date' => now()->addYear(),
                'subscription_active' => true,
                'is_admin' => true,
                'admin_role' => 'admin',
                'admin_permissions' => ['user_management', 'nfc_management'],
            ]
        );

        if ($regularAdmin->wasRecentlyCreated) {
            $createdCount++;
            $this->createAdminCardAndLanding($regularAdmin);
        } else {
            $skippedCount++;
        }

        $this->command->info("✅ Admin users: {$createdCount} created, {$skippedCount} already exists (skipped).");
        $this->command->info('   Super Admin: admin@nfcgo.com / admin123');
        $this->command->info('   Regular Admin: admin2@nfcgo.com / admin123');
    }

    private function createAdminCardAndLanding(User $user): void
    {
        $existingCard = NfcCard::where('user_id', $user->id)->first();

        if (! $existingCard) {
            $card = NfcCard::create([
                'user_id' => $user->id,
                'card_owner' => $user->full_name,
                'billing_address' => 'NFCGo HQ, Business District',
                'contact_number' => '+1234567890',
                'purchase_date' => now(),
                'subscription_plan' => $user->subscription_plan,
                'status' => 'active',
            ]);

            LandingPage::firstOrCreate(
                ['nfc_card_id' => $card->id],
                [
                    'name' => $user->full_name,
                    'title' => $user->job_title,
                    'company_name' => $user->company,
                    'email' => $user->email,
                    'is_active' => true,
                ]
            );
        }
    }
}
