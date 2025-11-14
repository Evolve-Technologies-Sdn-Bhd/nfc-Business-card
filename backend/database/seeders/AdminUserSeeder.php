<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@nfcgo.com',
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
        ]);

        // Create NFC Card for admin
        $adminCard = \App\Models\NfcCard::create([
            'user_id' => $admin->id,
            'card_owner' => $admin->full_name,
            'billing_address' => 'NFCGo HQ, Business District',
            'contact_number' => '+1234567890',
            'purchase_date' => now(),
            'subscription_plan' => 'business',
            'status' => 'active',
        ]);

        // Create Landing Page for admin
        \App\Models\LandingPage::create([
            'nfc_card_id' => $adminCard->id,
            'name' => 'Admin User',
            'title' => 'System Administrator',
            'company_name' => 'NFCGo',
            'email' => 'admin@nfcgo.com',
            'is_active' => true,
        ]);

        // Create regular admin user
        $regularAdmin = User::create([
            'first_name' => 'Regular',
            'last_name' => 'Admin',
            'email' => 'admin2@nfcgo.com',
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
        ]);

        // Create NFC Card for regular admin
        $regularAdminCard = \App\Models\NfcCard::create([
            'user_id' => $regularAdmin->id,
            'card_owner' => $regularAdmin->full_name,
            'billing_address' => 'NFCGo HQ, Business District',
            'contact_number' => '+1234567890',
            'purchase_date' => now(),
            'subscription_plan' => 'premium',
            'status' => 'active',
        ]);

        // Create Landing Page for regular admin
        \App\Models\LandingPage::create([
            'nfc_card_id' => $regularAdminCard->id,
            'name' => 'Regular Admin',
            'title' => 'Administrator',
            'company_name' => 'NFCGo',
            'email' => 'admin2@nfcgo.com',
            'is_active' => true,
        ]);

        $this->command->info('Admin users created successfully!');
        $this->command->info('Super Admin: admin@nfcgo.com / admin123');
        $this->command->info('Regular Admin: admin2@nfcgo.com / admin123');
    }
}
