<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\NfcCard;
use App\Models\LandingPage;
use App\Models\SocialLink;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MockUserSeeder extends Seeder
{
    public function run(): void
    {
        $mockFiles = [
            'basic_user_mock.json',
            'premium_user_mock.json',
            'business_user_mock.json'
        ];

        foreach ($mockFiles as $file) {
            $path = database_path("seeders/mock_data/{$file}");

            if (!File::exists($path)) {
                $this->command->error("Mock file not found: {$file}");
                continue;
            }

            $json = File::get($path);
            $data = json_decode($json, true);

            if (!$data) {
                $this->command->error("Invalid JSON in file: {$file}");
                continue;
            }

            $this->createMockUser($data);
        }

        $this->command->info('Mock users seeded successfully!');
    }

    private function createMockUser($data)
    {
        DB::beginTransaction();
        try {
            // 1. Create User
            $names = explode(' ', $data['card_owner'], 2);
            $firstName = $names[0];
            $lastName = $names[1] ?? '';

            $user = User::firstOrCreate(
                ['email' => $data['profile']['emailAddress']],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'password' => Hash::make('password'),
                    'subscription_plan' => $data['subscription_plan'],
                    'subscription_active' => true,
                    'is_admin' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Update plan if user exists but plan is different
            if ($user->subscription_plan !== $data['subscription_plan']) {
                $user->update(['subscription_plan' => $data['subscription_plan']]);
            }

            // 2. Create NFC Card
            $nfcCard = NfcCard::firstOrCreate(
                ['nfc_card_id' => $data['nfc_card_id']],
                [
                    'user_id' => $user->id,
                    'card_owner' => $data['card_owner'],
                    'status' => 'active',
                    'subscription_plan' => $data['subscription_plan'],
                    'purchase_date' => now()->subMonths(1),
                    'contact_number' => $data['profile']['contactNumber'],
                    'billing_address' => $data['profile']['address'] ?? 'Default Address',
                    'shipping_address' => $data['profile']['address'] ?? 'Default Address',
                    'payment_method' => 'Credit Card',
                    'purchase_amount' => 0.00,
                ]
            );

            // 3. Create Landing Page
            // Check if exists first to avoid duplicate errors on unique keys
            $landingPage = LandingPage::where('nfc_card_id', $nfcCard->id)->first();

            if ($landingPage) {
                // Update existing
                $landingPage->update($this->mapLandingPageData($data));

                // Clear existing related data to simple re-seed
                // In a real app we might update, but for mock data reset is cleaner
                // Note: SocialLink model might need to be imported if we use a separate table
                // For now, assuming social_links is a JSON column on landing_page based on schema
            } else {
                // Create new
                $lpData = $this->mapLandingPageData($data);
                $lpData['nfc_card_id'] = $nfcCard->id;
                $landingPage = LandingPage::create($lpData);
            }

            $this->command->info("Processed user: {$user->email} ({$data['subscription_plan']})");
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error processing {$data['card_owner']}: " . $e->getMessage());
        }
    }

    private function mapLandingPageData($data)
    {
        $p = $data['profile'] ?? [];
        $c = $data['company'] ?? [];
        $s = $data['services'] ?? [];
        $l = $data['links'] ?? [];
        $port = $data['portfolio'] ?? [];
        $b = $data['blog'] ?? [];
        $d = $data['design'] ?? [];
        $f = $data['features'] ?? [];

        return [
            // Basic Info (Profile)
            'name' => $p['name'] ?? null,
            'title' => $p['position'] ?? null,
            'qualification' => $p['qualification'] ?? null,
            'bio' => $p['bio'] ?? null, // Rich text usually
            'phone' => $p['contactNumber'] ?? null,
            'email' => $p['emailAddress'] ?? null,
            'website' => $p['website'] ?? null,
            'address' => $p['address'] ?? null,
            'profile_image_path' => $p['profilePicture'] ?? null,
            'cover_banner_path' => $p['coverBanner'] ?? null,
            'pronouns' => $p['pronouns'] ?? null,
            'tagline' => $p['tagline'] ?? null,

            // Company Info
            'company_name' => $c['companyName'] ?? null,
            'company_logo_text' => $c['companyLogoText'] ?? null,
            'company_logo_path' => $c['companyLogo'] ?? null,
            'company_registration_no' => $c['companyRegistrationNo'] ?? null,
            'company_department' => $c['companyDepartment'] ?? null,
            'company_description' => $c['companyDescription'] ?? null, // Rich text
            'company_video' => $c['companyVideo'] ?? null,
            'industry' => $c['industry'] ?? null,
            'established_year' => $c['establishedYear'] ?? null,
            'employee_count' => $c['employeeCount'] ?? null,
            'address_name' => $c['addressName'] ?? null,
            'address_street' => $c['addressStreet'] ?? null,
            'address_area' => $c['addressArea'] ?? null,
            'address_city_state' => $c['addressCityState'] ?? null,
            'address_country' => $c['addressCountry'] ?? null,
            'postal_code' => $c['postalCode'] ?? null,
            'address_map_url' => $c['mapUrl'] ?? null,
            'coordinates' => $c['coordinates'] ?? null,
            'phone_label' => $c['phoneLabel'] ?? null,
            'phone_number' => $c['phoneNumber'] ?? null,
            'company_whatsapp' => $c['companyWhatsapp'] ?? null,

            // JSON Columns (Arrays)
            'education' => $p['education'] ?? [],
            'certifications' => $p['certifications'] ?? [],
            'stats' => $p['profileStats'] ?? [],
            'working_hours' => $c['workingHours'] ?? [],
            'awards' => $c['awards'] ?? [],
            'team_members' => $c['teamMembers'] ?? [],
            'expertise' => $c['expertise'] ?? [],

            // Services (JSON)
            'services' => $s ?? [],

            // Links (JSON)
            'social_links' => $l['socialLinks'] ?? [],
            'appointment_link' => $l['appointmentLink'] ?? null,
            'payment_button_text' => $l['paymentButtonText'] ?? null,
            'payment_button_url' => $l['paymentButtonUrl'] ?? null,
            'whatsapp_number' => $l['whatsapp'] ?? null,

            // Portfolio
            'portfolio_title' => $port['portfolioTitle'] ?? null,
            'portfolio_description' => $port['portfolioDescription'] ?? null,
            'portfolio_category' => $port['portfolioCategory'] ?? null,
            'portfolio_cover_image' => $port['portfolioCoverImage'] ?? null,
            'portfolio_gallery' => $port['portfolioGallery'] ?? [],
            'projects' => $port['projects'] ?? [],
            'project_url' => $port['projectUrl'] ?? null,
            'date_completed' => $port['dateCompleted'] ?? null,
            'client_name' => $port['clientName'] ?? null,
            'portfolio_location' => $port['location'] ?? null,
            'skills_used' => $port['skillsUsed'] ?? [],
            'pdf_download' => $port['pdfDownload'] ?? null,
            'portfolio_tags' => $port['portfolioTags'] ?? [],

            // Blog
            'blog_enabled' => isset($b['blogPosts']) ? true : false,
            'blog_posts' => $b['blogPosts'] ?? [],
            'blog_gallery' => $b['blogGallery'] ?? [],

            // Design Configuration
            'design_config' => $d ?? [],
            'profile_style' => $d['profileStyle'] ?? 'classic',
            'theme' => $d['theme'] ?? 'minimal',
            'background_color' => $d['backgroundColor'] ?? '#FFFFFF',
            'text_color' => $d['textColor'] ?? '#000000',
            'font' => $d['font'] ?? 'inter',
            'button_style' => $d['buttonStyle'] ?? 'solid',
            'color_scheme' => $d['colorScheme'] ?? null,
            'layout' => $d['layout'] ?? null,
            'show_watermark' => $d['showWatermark'] ?? true,

            // Features Toggle & Order
            'features' => $f ?? [],

            'is_active' => true,
        ];
    }
}
