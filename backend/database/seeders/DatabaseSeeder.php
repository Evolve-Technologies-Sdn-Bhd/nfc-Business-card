<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\NfcTag;
use App\Models\NfcCard;
use App\Models\ProfileBuilderSection;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->warn('====================================');
        $this->command->warn('  NFC Business Card - Database Seed');
        $this->command->warn('====================================');

        $env = config('app.env');
        $isProduction = ($env === 'production');

        if ($isProduction) {
            $this->command->warn('⚠️  ENVIRONMENT: PRODUCTION');
            $this->command->warn('   Mock/test data akan DILANGKAUTI.');
        } else {
            $this->command->warn('ℹ️  ENVIRONMENT: LOCAL / STAGING');
            $this->command->warn('   Semua data termasuk mock akan di-seed.');
        }

        $this->command->newLine();

        if (! $isProduction) {
            $this->seedDevelopmentUser();
            $this->call(NfcCardSeeder::class);
            $this->call(MockUserSeeder::class);
        }

        $this->call(AdminUserSeeder::class);
        $this->call(PlanPriceSeeder::class);

        if (ProfileBuilderSection::count() === 0) {
            $this->call(ProfileBuilderSectionsSeeder::class);
        } else {
            $this->command->info('✅ ProfileBuilderSections sudah wujud — skip seeder.');
        }

        $this->call(ProfileBuilderFieldsSeeder::class);
        $this->call(AddMissingProfileFieldsSeeder::class);
        $this->call(UpdateFieldGroupsSeeder::class);
        $this->call(UpdateFieldValidationSeeder::class);
        $this->call(BlogSectionSeeder::class);
        $this->call(PortfolioSectionSeeder::class);
        $this->call(ProfileDesignOptionSeeder::class);
        $this->call(FeatureToggleSeeder::class);
        $this->call(ChatbotSeeder::class);
        $this->call(LegalDocumentSeeder::class);
        $this->call(CardTemplateSeeder::class);

        $this->command->newLine();
        $this->command->info('🎉 Database seeding selesai!');

        if (! $isProduction) {
            $this->command->info('Test user: john@example.com / password');
        }
    }

    private function seedDevelopmentUser(): void
    {
        $this->command->info('🔹 Membangunkan test user dan NFC card sample...');

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

        if ($user->wasRecentlyCreated || NfcCard::where('user_id', $user->id)->count() === 0) {
            $nfcCard = NfcCard::create([
                'user_id' => $user->id,
                'card_owner' => $user->full_name,
                'billing_address' => '123 Tech Street, San Francisco, CA 94105',
                'contact_number' => '+1234567890',
                'purchase_date' => now(),
                'subscription_plan' => 'free',
                'status' => 'active',
            ]);

            \App\Models\LandingPage::firstOrCreate(
                ['nfc_card_id' => $nfcCard->id],
                [
                    'name' => $user->full_name,
                    'title' => $user->job_title,
                    'company_name' => $user->company,
                    'email' => $user->email,
                    'phone' => '+1234567890',
                    'bio' => 'Passionate software engineer with 5+ years of experience.',
                    'is_active' => true,
                ]
            );
        }

        NfcTag::firstOrCreate(
            ['nfc_id' => 'test-nfc-123'],
            [
                'user_id' => $user->id,
                'name' => 'Business Card',
                'status' => 'active',
                'tap_count' => 0,
            ]
        );
    }
}
