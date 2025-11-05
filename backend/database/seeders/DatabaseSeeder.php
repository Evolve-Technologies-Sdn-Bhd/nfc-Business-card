<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use App\Models\SocialLink;
use App\Models\NfcTag;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user (profile will be auto-created by User model)
        $user = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'company' => 'Tech Corp',
            'job_title' => 'Software Engineer',
            'plan' => 'premium',
        ]);

        // Update the auto-created profile with more details
        $profile = $user->profile;
        $profile->update([
            'slug' => 'john-doe',
            'bio' => 'Passionate software engineer with 5+ years of experience in web development.',
            'phone' => '+1234567890',
            'website' => 'https://johndoe.dev',
            'location' => 'San Francisco, CA',
            'theme' => 'modern',
            'background_color' => '#1a1a1a',
            'text_color' => '#ffffff',
            'font' => 'inter',
            'button_style' => 'solid',
            'show_watermark' => true,
        ]);

        // Create social links
        $socialLinks = [
            [
                'platform' => 'linkedin',
                'title' => 'LinkedIn',
                'url' => 'https://linkedin.com/in/johndoe',
                'order' => 1,
            ],
            [
                'platform' => 'github',
                'title' => 'GitHub',
                'url' => 'https://github.com/johndoe',
                'order' => 2,
            ],
            [
                'platform' => 'twitter',
                'title' => 'Twitter',
                'url' => 'https://twitter.com/johndoe',
                'order' => 3,
            ],
            [
                'platform' => 'portfolio',
                'title' => 'Portfolio',
                'url' => 'https://johndoe.dev',
                'order' => 4,
            ],
        ];

        foreach ($socialLinks as $linkData) {
            SocialLink::create(array_merge($linkData, ['profile_id' => $profile->id]));
        }

        // Create NFC tag
        NfcTag::create([
            'user_id' => $user->id,
            'nfc_id' => 'test-nfc-123',
            'name' => 'Business Card',
            'status' => 'active',
            'tap_count' => 0,
        ]);

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
        $this->command->info('Profile URL: /p/john-doe');
    }
}
