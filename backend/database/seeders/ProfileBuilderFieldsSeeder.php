<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfileBuilderFieldsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing fields
        DB::table('profile_builder_fields')->truncate();
        
        $this->seedProfileFields();
        $this->seedCompanyFields();
        $this->seedServicesFields();
        $this->seedLinksFields();
        $this->seedPortfolioFields();
        $this->seedBlogFields();
        
        $this->command->info('✅ All profile builder fields seeded successfully!');
    }

    private function seedProfileFields()
    {
        $fields = [
            ['tab' => 'profile', 'field_key' => 'profilePicture', 'field_type' => 'image', 'label' => 'Profile Photo', 'display_order' => 10, 'available_plans' => ['basic', 'premium', 'business']],
            ['tab' => 'profile', 'field_key' => 'coverBanner', 'field_type' => 'image', 'label' => 'Top Cover Banner', 'display_order' => 15, 'available_plans' => ['premium', 'business']],
            ['tab' => 'profile', 'field_key' => 'name', 'field_type' => 'text', 'label' => 'Full Name', 'is_required' => true, 'display_order' => 20, 'available_plans' => ['basic', 'premium', 'business']],
            ['tab' => 'profile', 'field_key' => 'position', 'field_type' => 'text', 'label' => 'Job Title / Role', 'display_order' => 30, 'available_plans' => ['basic', 'premium', 'business']],
            ['tab' => 'profile', 'field_key' => 'pronouns', 'field_type' => 'select', 'label' => 'Pronouns (He/She/They)', 'display_order' => 35, 'available_plans' => ['premium', 'business']],
            ['tab' => 'profile', 'field_key' => 'qualification', 'field_type' => 'text', 'label' => 'Professional Qualification', 'display_order' => 38, 'available_plans' => ['premium', 'business']],
            ['tab' => 'profile', 'field_key' => 'bio', 'field_type' => 'richtext', 'label' => 'Personal Biography', 'display_order' => 40, 'available_plans' => ['basic', 'premium', 'business']],
            ['tab' => 'profile', 'field_key' => 'tagline', 'field_type' => 'text', 'label' => 'Short Tagline', 'display_order' => 45, 'available_plans' => ['premium', 'business']],
            ['tab' => 'profile', 'field_key' => 'contactNumber', 'field_type' => 'tel', 'label' => 'Phone Number', 'display_order' => 50, 'available_plans' => ['basic', 'premium', 'business']],
            ['tab' => 'profile', 'field_key' => 'emailAddress', 'field_type' => 'email', 'label' => 'Email', 'display_order' => 60, 'available_plans' => ['basic', 'premium', 'business']],
            ['tab' => 'profile', 'field_key' => 'website', 'field_type' => 'url', 'label' => 'Personal Website', 'display_order' => 70, 'available_plans' => ['basic', 'premium', 'business']],
            ['tab' => 'profile', 'field_key' => 'address', 'field_type' => 'textarea', 'label' => 'Address', 'display_order' => 80, 'available_plans' => ['basic', 'premium', 'business']],
            ['tab' => 'profile', 'field_key' => 'education', 'field_type' => 'repeater', 'label' => 'Education Background', 'display_order' => 85, 'available_plans' => ['premium', 'business']],
            ['tab' => 'profile', 'field_key' => 'certifications', 'field_type' => 'repeater', 'label' => 'Licenses & Certifications', 'display_order' => 88, 'available_plans' => ['premium', 'business']],
            ['tab' => 'profile', 'field_key' => 'profileStats', 'field_type' => 'repeater', 'label' => 'Profile Statistics', 'display_order' => 90, 'available_plans' => ['premium', 'business']],
            ['tab' => 'profile', 'field_key' => 'contactFormEnabled', 'field_type' => 'toggle', 'label' => 'Contact Form', 'display_order' => 92, 'available_plans' => ['premium', 'business']],
            ['tab' => 'profile', 'field_key' => 'vcardEnabled', 'field_type' => 'toggle', 'label' => 'Downloadable vCard', 'display_order' => 94, 'available_plans' => ['basic', 'premium', 'business']],
            ['tab' => 'profile', 'field_key' => 'qrCodeEnabled', 'field_type' => 'toggle', 'label' => 'QR Code Sharing', 'display_order' => 96, 'available_plans' => ['basic', 'premium', 'business']],
        ];
        $this->insertFields($fields);
    }

    private function seedCompanyFields()
    {
        $fields = [
            ['tab' => 'company', 'field_key' => 'companyLogo', 'field_type' => 'image', 'label' => 'Company Logo', 'display_order' => 100, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'companyLogoText', 'field_type' => 'text', 'label' => 'Logo Text Version', 'display_order' => 105, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'companyName', 'field_type' => 'text', 'label' => 'Company Name', 'display_order' => 110, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'companyRegNo', 'field_type' => 'text', 'label' => 'Registration Number', 'display_order' => 115, 'available_plans' => ['business']],
            ['tab' => 'company', 'field_key' => 'companyDescription', 'field_type' => 'richtext', 'label' => 'Company Introduction', 'display_order' => 120, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'companyVideo', 'field_type' => 'video', 'label' => 'Company Video', 'display_order' => 125, 'available_plans' => ['business']],
            ['tab' => 'company', 'field_key' => 'industry', 'field_type' => 'select', 'label' => 'Industry Type', 'display_order' => 130, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'establishedYear', 'field_type' => 'number', 'label' => 'Year Founded', 'display_order' => 135, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'employeeCount', 'field_type' => 'number', 'label' => 'Number of Employees', 'display_order' => 140, 'available_plans' => ['business']],
            ['tab' => 'company', 'field_key' => 'expertise', 'field_type' => 'repeater', 'label' => 'Expertise / Skills', 'display_order' => 145, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'addressName', 'field_type' => 'text', 'label' => 'Building Name', 'display_order' => 150, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'addressStreet', 'field_type' => 'text', 'label' => 'Street Address', 'display_order' => 155, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'addressArea', 'field_type' => 'text', 'label' => 'Area / District', 'display_order' => 160, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'addressCityState', 'field_type' => 'text', 'label' => 'City & State', 'display_order' => 165, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'addressCountry', 'field_type' => 'text', 'label' => 'Country', 'display_order' => 170, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'postalCode', 'field_type' => 'text', 'label' => 'Postal Code', 'display_order' => 175, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'mapUrl', 'field_type' => 'url', 'label' => 'Google Maps URL', 'display_order' => 180, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'coordinates', 'field_type' => 'text', 'label' => 'Map Coordinates', 'display_order' => 185, 'available_plans' => ['business']],
            ['tab' => 'company', 'field_key' => 'phoneLabel', 'field_type' => 'text', 'label' => 'Phone Label', 'display_order' => 190, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'phoneNumber', 'field_type' => 'tel', 'label' => 'Phone Number', 'display_order' => 195, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'companyWhatsapp', 'field_type' => 'tel', 'label' => 'WhatsApp', 'display_order' => 200, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'workingHours', 'field_type' => 'repeater', 'label' => 'Operating Hours', 'display_order' => 210, 'available_plans' => ['premium', 'business']],
            ['tab' => 'company', 'field_key' => 'awards', 'field_type' => 'repeater', 'label' => 'Awards & Achievements', 'display_order' => 220, 'available_plans' => ['business']],
            ['tab' => 'company', 'field_key' => 'teamMembers', 'field_type' => 'repeater', 'label' => 'Team Members', 'display_order' => 230, 'available_plans' => ['business']],
        ];
        $this->insertFields($fields);
    }

    private function seedServicesFields()
    {
        $fields = [
            ['tab' => 'services', 'field_key' => 'serviceName', 'field_type' => 'text', 'label' => 'Service Name', 'display_order' => 300, 'available_plans' => ['premium', 'business']],
            ['tab' => 'services', 'field_key' => 'serviceCategory', 'field_type' => 'select', 'label' => 'Category / Type', 'display_order' => 305, 'available_plans' => ['premium', 'business']],
            ['tab' => 'services', 'field_key' => 'serviceImage', 'field_type' => 'image', 'label' => 'Service Image', 'display_order' => 310, 'available_plans' => ['premium', 'business']],
            ['tab' => 'services', 'field_key' => 'serviceVideo', 'field_type' => 'url', 'label' => 'Promo Video', 'display_order' => 315, 'available_plans' => ['business']],
            ['tab' => 'services', 'field_key' => 'serviceDescription', 'field_type' => 'richtext', 'label' => 'Full Service Description', 'display_order' => 320, 'available_plans' => ['premium', 'business']],
            ['tab' => 'services', 'field_key' => 'serviceFeatures', 'field_type' => 'repeater', 'label' => 'Key Features', 'display_order' => 325, 'available_plans' => ['premium', 'business']],
            ['tab' => 'services', 'field_key' => 'servicePrice', 'field_type' => 'text', 'label' => 'Current Price', 'display_order' => 330, 'available_plans' => ['premium', 'business']],
            ['tab' => 'services', 'field_key' => 'serviceOldPrice', 'field_type' => 'text', 'label' => 'Previous Price', 'display_order' => 335, 'available_plans' => ['premium', 'business']],
            ['tab' => 'services', 'field_key' => 'serviceDuration', 'field_type' => 'text', 'label' => 'Duration', 'display_order' => 340, 'available_plans' => ['premium', 'business']],
            ['tab' => 'services', 'field_key' => 'serviceTags', 'field_type' => 'repeater', 'label' => 'Keywords / Tags', 'display_order' => 345, 'available_plans' => ['premium', 'business']],
            ['tab' => 'services', 'field_key' => 'serviceBrochure', 'field_type' => 'file', 'label' => 'Service Brochure PDF', 'display_order' => 350, 'available_plans' => ['business']],
            ['tab' => 'services', 'field_key' => 'bookingEnabled', 'field_type' => 'toggle', 'label' => 'Enable Booking Button', 'display_order' => 355, 'available_plans' => ['business']],
            ['tab' => 'services', 'field_key' => 'bookingUrl', 'field_type' => 'url', 'label' => 'External Booking Link', 'display_order' => 360, 'available_plans' => ['business']],
        ];
        $this->insertFields($fields);
    }

    private function seedLinksFields()
    {
        $fields = [
            ['tab' => 'links', 'field_key' => 'phone', 'field_type' => 'tel', 'label' => 'Contact Number', 'display_order' => 400, 'available_plans' => ['basic', 'premium', 'business']],
            ['tab' => 'links', 'field_key' => 'whatsapp', 'field_type' => 'tel', 'label' => 'WhatsApp', 'display_order' => 405, 'available_plans' => ['basic', 'premium', 'business']],
            ['tab' => 'links', 'field_key' => 'socialLinks', 'field_type' => 'repeater', 'label' => 'Social Media Links', 'display_order' => 410, 'available_plans' => ['basic', 'premium', 'business']],
            ['tab' => 'links', 'field_key' => 'linkTitle', 'field_type' => 'text', 'label' => 'Link Title', 'display_order' => 420, 'available_plans' => ['basic', 'premium', 'business']],
            ['tab' => 'links', 'field_key' => 'linkUrl', 'field_type' => 'url', 'label' => 'URL', 'display_order' => 425, 'available_plans' => ['basic', 'premium', 'business']],
            ['tab' => 'links', 'field_key' => 'linkIcon', 'field_type' => 'icon', 'label' => 'Icon Selection', 'display_order' => 430, 'available_plans' => ['premium', 'business']],
            ['tab' => 'links', 'field_key' => 'linkType', 'field_type' => 'select', 'label' => 'Link Behavior', 'display_order' => 435, 'available_plans' => ['premium', 'business']],
            ['tab' => 'links', 'field_key' => 'openNewTab', 'field_type' => 'toggle', 'label' => 'Open in New Tab', 'display_order' => 440, 'available_plans' => ['basic', 'premium', 'business']],
            ['tab' => 'links', 'field_key' => 'utmParameters', 'field_type' => 'text', 'label' => 'Marketing Tracking', 'display_order' => 445, 'available_plans' => ['business']],
            ['tab' => 'links', 'field_key' => 'clickTracking', 'field_type' => 'toggle', 'label' => 'Enable Click Analytics', 'display_order' => 450, 'available_plans' => ['premium', 'business']],
        ];
        $this->insertFields($fields);
    }

    private function seedPortfolioFields()
    {
        $fields = [
            ['tab' => 'portfolio', 'field_key' => 'portfolioTitle', 'field_type' => 'text', 'label' => 'Project Title', 'display_order' => 500, 'available_plans' => ['premium', 'business']],
            ['tab' => 'portfolio', 'field_key' => 'portfolioDescription', 'field_type' => 'richtext', 'label' => 'Project Details', 'display_order' => 505, 'available_plans' => ['premium', 'business']],
            ['tab' => 'portfolio', 'field_key' => 'portfolioCategory', 'field_type' => 'select', 'label' => 'Portfolio Category', 'display_order' => 510, 'available_plans' => ['premium', 'business']],
            ['tab' => 'portfolio', 'field_key' => 'portfolioTags', 'field_type' => 'repeater', 'label' => 'Tags', 'display_order' => 515, 'available_plans' => ['premium', 'business']],
            ['tab' => 'portfolio', 'field_key' => 'portfolioCoverImage', 'field_type' => 'image', 'label' => 'Cover Image', 'display_order' => 520, 'available_plans' => ['premium', 'business']],
            ['tab' => 'portfolio', 'field_key' => 'portfolioGallery', 'field_type' => 'gallery', 'label' => 'Project Gallery', 'display_order' => 525, 'available_plans' => ['premium', 'business']],
            ['tab' => 'portfolio', 'field_key' => 'projectUrl', 'field_type' => 'url', 'label' => 'External Project Link', 'display_order' => 530, 'available_plans' => ['premium', 'business']],
            ['tab' => 'portfolio', 'field_key' => 'dateCompleted', 'field_type' => 'date', 'label' => 'Completion Date', 'display_order' => 535, 'available_plans' => ['premium', 'business']],
            ['tab' => 'portfolio', 'field_key' => 'clientName', 'field_type' => 'text', 'label' => 'Client Name', 'display_order' => 540, 'available_plans' => ['premium', 'business']],
            ['tab' => 'portfolio', 'field_key' => 'location', 'field_type' => 'text', 'label' => 'Project Location', 'display_order' => 545, 'available_plans' => ['premium', 'business']],
            ['tab' => 'portfolio', 'field_key' => 'skillsUsed', 'field_type' => 'repeater', 'label' => 'Tools / Skills Used', 'display_order' => 550, 'available_plans' => ['premium', 'business']],
            ['tab' => 'portfolio', 'field_key' => 'pdfDownload', 'field_type' => 'file', 'label' => 'PDF for Download', 'display_order' => 555, 'available_plans' => ['business']],
        ];
        $this->insertFields($fields);
    }

    private function seedBlogFields()
    {
        $fields = [
            ['tab' => 'blog', 'field_key' => 'blogTitle', 'field_type' => 'text', 'label' => 'Blog Post Title', 'display_order' => 600, 'available_plans' => ['business']],
            ['tab' => 'blog', 'field_key' => 'blogSlug', 'field_type' => 'text', 'label' => 'URL Slug', 'display_order' => 605, 'available_plans' => ['business']],
            ['tab' => 'blog', 'field_key' => 'blogCoverImage', 'field_type' => 'image', 'label' => 'Cover Banner', 'display_order' => 610, 'available_plans' => ['business']],
            ['tab' => 'blog', 'field_key' => 'blogCategory', 'field_type' => 'select', 'label' => 'Blog Category', 'display_order' => 615, 'available_plans' => ['business']],
            ['tab' => 'blog', 'field_key' => 'blogTags', 'field_type' => 'repeater', 'label' => 'Tags', 'display_order' => 620, 'available_plans' => ['business']],
            ['tab' => 'blog', 'field_key' => 'authorName', 'field_type' => 'text', 'label' => 'Author Name', 'display_order' => 625, 'available_plans' => ['business']],
            ['tab' => 'blog', 'field_key' => 'publishedDate', 'field_type' => 'date', 'label' => 'Publication Date', 'display_order' => 630, 'available_plans' => ['business']],
            ['tab' => 'blog', 'field_key' => 'readingTime', 'field_type' => 'text', 'label' => 'Reading Time', 'display_order' => 635, 'available_plans' => ['business']],
            ['tab' => 'blog', 'field_key' => 'blogContent', 'field_type' => 'richtext', 'label' => 'Blog Content', 'display_order' => 640, 'available_plans' => ['business']],
            ['tab' => 'blog', 'field_key' => 'blogGallery', 'field_type' => 'gallery', 'label' => 'Media Gallery', 'display_order' => 645, 'available_plans' => ['business']],
            ['tab' => 'blog', 'field_key' => 'externalLink', 'field_type' => 'url', 'label' => 'External Article Link', 'display_order' => 650, 'available_plans' => ['business']],
            ['tab' => 'blog', 'field_key' => 'relatedPosts', 'field_type' => 'select', 'label' => 'Related Posts', 'display_order' => 655, 'available_plans' => ['business']],
        ];
        $this->insertFields($fields);
    }

    private function insertFields(array $fields)
    {
        foreach ($fields as $field) {
            DB::table('profile_builder_fields')->insert([
                'tab' => $field['tab'],
                'field_key' => $field['field_key'],
                'field_type' => $field['field_type'],
                'label' => $field['label'],
                'placeholder' => $field['placeholder'] ?? null,
                'help_text' => $field['help_text'] ?? null,
                'is_required' => $field['is_required'] ?? false,
                'is_visible' => $field['is_visible'] ?? true,
                'validation_rules' => json_encode($field['validation_rules'] ?? []),
                'available_plans' => json_encode($field['available_plans'] ?? ['basic', 'premium', 'business']),
                'display_order' => $field['display_order'],
                'config' => json_encode($field['config'] ?? []),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
