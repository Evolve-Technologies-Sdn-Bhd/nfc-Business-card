<?php

namespace Database\Seeders;

use App\Models\ProfileBuilderField;
use Illuminate\Database\Seeder;

class UpdateFieldGroupsSeeder extends Seeder
{
    public function run(): void
    {
        // Profile tab groups
        $profileGroups = [
            // Basic Information
            ['keys' => ['name', 'position', 'pronouns', 'qualification', 'tagline'], 'group' => 'Basic Information', 'icon' => 'heroicons:user-circle', 'order' => 1],
            // About Me
            ['keys' => ['bio'], 'group' => 'About Me', 'icon' => 'heroicons:document-text', 'order' => 2],
            // Contact
            ['keys' => ['contactNumber', 'emailAddress', 'website', 'address'], 'group' => 'Contact', 'icon' => 'heroicons:phone', 'order' => 3],
            // Background
            ['keys' => ['education', 'certifications', 'profileStats'], 'group' => 'Background', 'icon' => 'heroicons:academic-cap', 'order' => 4],
        ];

        // Company tab groups
        $companyGroups = [
            ['keys' => ['companyLogoText', 'companyName', 'companyRegNo'], 'group' => 'Company Identity', 'icon' => 'heroicons:identification', 'order' => 1],
            ['keys' => ['companyDescription', 'companyVideo', 'industry', 'establishedYear', 'employeeCount'], 'group' => 'About Company', 'icon' => 'heroicons:information-circle', 'order' => 2],
            ['keys' => ['expertise'], 'group' => 'Expertise & Skills', 'icon' => 'heroicons:star', 'order' => 3],
            ['keys' => ['addressName', 'addressStreet', 'addressArea', 'addressCityState', 'addressCountry', 'postalCode', 'mapUrl', 'coordinates'], 'group' => 'Location & Address', 'icon' => 'heroicons:map-pin', 'order' => 4],
            ['keys' => ['phoneLabel', 'phoneNumber', 'companyWhatsapp'], 'group' => 'Contact Numbers', 'icon' => 'heroicons:phone', 'order' => 5],
            ['keys' => ['workingHours'], 'group' => 'Operating Hours', 'icon' => 'heroicons:clock', 'order' => 6],
            ['keys' => ['awards'], 'group' => 'Awards & Achievements', 'icon' => 'heroicons:trophy', 'order' => 7],
            ['keys' => ['teamMembers'], 'group' => 'Team Members', 'icon' => 'heroicons:user-group', 'order' => 8],
        ];

        // Services tab groups
        $servicesGroups = [
            ['keys' => ['serviceName', 'serviceCategory', 'serviceDuration'], 'group' => 'Service Information', 'icon' => 'heroicons:rocket-launch', 'order' => 1],
            ['keys' => ['serviceImage', 'serviceVideo', 'serviceBrochure'], 'group' => 'Media & Files', 'icon' => 'heroicons:photo', 'order' => 2],
            ['keys' => ['serviceDescription', 'serviceFeatures', 'serviceTags'], 'group' => 'Description & Features', 'icon' => 'heroicons:document-text', 'order' => 3],
            ['keys' => ['servicePrice', 'serviceOldPrice', 'bookingUrl'], 'group' => 'Pricing & Booking', 'icon' => 'heroicons:currency-dollar', 'order' => 4],
        ];

        // Links tab groups
        $linksGroups = [
            ['keys' => ['phone', 'whatsapp'], 'group' => 'Contact Links', 'icon' => 'heroicons:phone', 'order' => 1],
            ['keys' => ['socialLinks'], 'group' => 'Social Media', 'icon' => 'heroicons:share', 'order' => 2],
            ['keys' => ['linkTitle', 'linkUrl', 'linkIcon', 'linkType'], 'group' => 'Custom Links', 'icon' => 'heroicons:link', 'order' => 3],
            ['keys' => ['utmParameters'], 'group' => 'Marketing & Analytics', 'icon' => 'heroicons:chart-bar', 'order' => 4],
        ];

        // Portfolio tab groups
        $portfolioGroups = [
            ['keys' => ['portfolioTitle', 'portfolioDescription', 'portfolioCategory', 'portfolioTags'], 'group' => 'Project Information', 'icon' => 'heroicons:document-text', 'order' => 1],
            ['keys' => ['portfolioCoverImage', 'portfolioGallery', 'pdfDownload'], 'group' => 'Project Media', 'icon' => 'heroicons:photo', 'order' => 2],
            ['keys' => ['projectUrl', 'dateCompleted', 'clientName', 'location', 'skillsUsed'], 'group' => 'Project Details', 'icon' => 'heroicons:clipboard-document-list', 'order' => 3],
        ];

        // Blog tab groups
        $blogGroups = [
            ['keys' => ['blogTitle', 'blogSlug', 'blogCategory', 'blogTags'], 'group' => 'Post Information', 'icon' => 'heroicons:pencil-square', 'order' => 1],
            ['keys' => ['blogCoverImage', 'blogGallery'], 'group' => 'Blog Media', 'icon' => 'heroicons:photo', 'order' => 2],
            ['keys' => ['authorName', 'publishedDate', 'readingTime'], 'group' => 'Post Meta', 'icon' => 'heroicons:calendar', 'order' => 3],
            ['keys' => ['blogContent', 'externalLink', 'relatedPosts'], 'group' => 'Content', 'icon' => 'heroicons:document-text', 'order' => 4],
        ];

        // Apply all groups
        $allGroups = array_merge($profileGroups, $companyGroups, $servicesGroups, $linksGroups, $portfolioGroups, $blogGroups);

        foreach ($allGroups as $groupData) {
            ProfileBuilderField::whereIn('field_key', $groupData['keys'])
                ->update([
                    'field_group' => $groupData['group'],
                    'field_group_icon' => $groupData['icon'],
                    'group_order' => $groupData['order'],
                ]);
        }

        $this->command->info('Field groups updated successfully!');
    }
}
