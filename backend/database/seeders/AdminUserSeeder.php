<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

        // Update the auto-created profile for admin
        $admin->profile->update([
            'slug' => 'admin',
            'name' => 'Admin User',
            'title' => 'System Administrator',
            'company' => 'NFCGo',
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

        // Update the auto-created profile for regular admin
        $regularAdmin->profile->update([
            'slug' => 'regular-admin',
            'name' => 'Regular Admin',
            'title' => 'Administrator',
            'company' => 'NFCGo',
            'email' => 'admin2@nfcgo.com',
            'is_active' => true,
        ]);

        $this->command->info('Admin users created successfully!');
        $this->command->info('Super Admin: admin@nfcgo.com / admin123');
        $this->command->info('Regular Admin: admin2@nfcgo.com / admin123');
    }
}
