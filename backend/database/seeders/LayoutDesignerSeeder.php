<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ProfileBuilderSection;
use App\Models\ProfileBuilderField;

class LayoutDesignerSeeder extends Seeder
{
    /**
     * Seed the application's database with sample sections and fields for testing.
     */
    public function run(): void
    {
        // Check if sections already exist
        if (ProfileBuilderSection::count() > 0) {
            $this->command->info('Sections already exist. Skipping...');
            return;
        }

        $this->command->info('Seeding profile builder sections and fields...');

        // Profile Section
        $profileSection = ProfileBuilderSection::create([
            'section_key' => 'profile',
            'section_name' => 'Profile',
            'category' => 'general',
            'icon' => 'heroicons:user-circle',
            'description' => 'Personal information and profile details',
            'available_plans' => ['free', 'basic', 'premium', 'business', 'enterprise'],
            'display_order' => 1,
            'is_active' => true,
        ]);

        $profileFields = [
            ['field_key' => 'profilePicture', 'label' => 'Profile Picture', 'field_type' => 'image', 'order' => 1],
            ['field_key' => 'name', 'label' => 'Full Name', 'field_type' => 'text', 'order' => 2],
            ['field_key' => 'position', 'label' => 'Position', 'field_type' => 'text', 'order' => 3],
            ['field_key' => 'qualification', 'label' => 'Qualification', 'field_type' => 'text', 'order' => 4],
            ['field_key' => 'pronouns', 'label' => 'Pronouns', 'field_type' => 'text', 'order' => 5],
            ['field_key' => 'tagline', 'label' => 'Tagline', 'field_type' => 'text', 'order' => 6],
        ];

        foreach ($profileFields as $field) {
            ProfileBuilderField::create([
                'section_id' => $profileSection->id,
                'field_key' => $field['field_key'],
                'label' => $field['label'],
                'field_type' => $field['field_type'],
                'display_order' => $field['order'],
                'is_required' => false,
                'is_active' => true,
            ]);
        }

        // Company Section
        $companySection = ProfileBuilderSection::create([
            'section_key' => 'company',
            'section_name' => 'Company',
            'category' => 'general',
            'icon' => 'heroicons:building-office',
            'description' => 'Company information',
            'available_plans' => ['basic', 'premium', 'business', 'enterprise'],
            'display_order' => 2,
            'is_active' => true,
        ]);

        $companyFields = [
            ['field_key' => 'companyLogo', 'label' => 'Company Logo', 'field_type' => 'image', 'order' => 1],
            ['field_key' => 'companyName', 'label' => 'Company Name', 'field_type' => 'text', 'order' => 2],
            ['field_key' => 'companyRegistrationNo', 'label' => 'Registration No', 'field_type' => 'text', 'order' => 3],
            ['field_key' => 'companyDepartment', 'label' => 'Department', 'field_type' => 'text', 'order' => 4],
        ];

        foreach ($companyFields as $field) {
            ProfileBuilderField::create([
                'section_id' => $companySection->id,
                'field_key' => $field['field_key'],
                'label' => $field['label'],
                'field_type' => $field['field_type'],
                'display_order' => $field['order'],
                'is_required' => false,
                'is_active' => true,
            ]);
        }

        // Services Section
        $servicesSection = ProfileBuilderSection::create([
            'section_key' => 'services',
            'section_name' => 'Services',
            'category' => 'general',
            'icon' => 'heroicons:rocket-launch',
            'description' => 'Services and offerings',
            'available_plans' => ['premium', 'business', 'enterprise'],
            'display_order' => 3,
            'is_active' => true,
        ]);

        ProfileBuilderField::create([
            'section_id' => $servicesSection->id,
            'field_key' => 'services',
            'label' => 'Services List',
            'field_type' => 'repeater',
            'display_order' => 1,
            'is_required' => false,
            'is_active' => true,
        ]);

        // Contact Section
        $contactSection = ProfileBuilderSection::create([
            'section_key' => 'contact',
            'section_name' => 'Contact',
            'category' => 'general',
            'icon' => 'heroicons:phone',
            'description' => 'Contact information',
            'available_plans' => ['free', 'basic', 'premium', 'business', 'enterprise'],
            'display_order' => 4,
            'is_active' => true,
        ]);

        $contactFields = [
            ['field_key' => 'phoneNumber', 'label' => 'Phone Number', 'field_type' => 'tel', 'order' => 1],
            ['field_key' => 'emailAddress', 'label' => 'Email', 'field_type' => 'email', 'order' => 2],
            ['field_key' => 'whatsappNumber', 'label' => 'WhatsApp', 'field_type' => 'tel', 'order' => 3],
            ['field_key' => 'websiteUrl', 'label' => 'Website', 'field_type' => 'url', 'order' => 4],
        ];

        foreach ($contactFields as $field) {
            ProfileBuilderField::create([
                'section_id' => $contactSection->id,
                'field_key' => $field['field_key'],
                'label' => $field['label'],
                'field_type' => $field['field_type'],
                'display_order' => $field['order'],
                'is_required' => false,
                'is_active' => true,
            ]);
        }

        // Links Section
        $linksSection = ProfileBuilderSection::create([
            'section_key' => 'links',
            'section_name' => 'Links',
            'category' => 'general',
            'icon' => 'heroicons:link',
            'description' => 'Social media and other links',
            'available_plans' => ['basic', 'premium', 'business', 'enterprise'],
            'display_order' => 5,
            'is_active' => true,
        ]);

        ProfileBuilderField::create([
            'section_id' => $linksSection->id,
            'field_key' => 'socialLinks',
            'label' => 'Social Links',
            'field_type' => 'repeater',
            'display_order' => 1,
            'is_required' => false,
            'is_active' => true,
        ]);

        $this->command->info('✅ Profile builder sections and fields seeded successfully!');
    }
}
