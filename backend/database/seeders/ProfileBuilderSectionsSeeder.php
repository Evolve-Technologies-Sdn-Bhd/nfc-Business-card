<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProfileBuilderSection;

class ProfileBuilderSectionsSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'key' => 'profile',
                'name' => 'Profile',
                'icon' => 'heroicons:user',
                'category' => 'general',
                'description' => 'Personal information, bio, and contact details',
                'display_order' => 1,
                'available_plans' => ['basic', 'premium', 'business'],
                'has_fields' => true,
                'is_active' => true,
            ],
            [
                'key' => 'company',
                'name' => 'Company & Team',
                'icon' => 'heroicons:building-office',
                'category' => 'general',
                'description' => 'Company information and team members',
                'display_order' => 2,
                'available_plans' => ['premium', 'business'],
                'has_fields' => true,
                'is_active' => true,
            ],
            [
                'key' => 'services',
                'name' => 'Services',
                'icon' => 'heroicons:rocket-launch',
                'category' => 'general',
                'description' => 'Services and products you offer',
                'display_order' => 3,
                'available_plans' => ['premium', 'business'],
                'has_fields' => true,
                'is_active' => true,
            ],
            [
                'key' => 'links',
                'name' => 'Social Media & Links',
                'icon' => 'heroicons:link',
                'category' => 'general',
                'description' => 'Social media profiles and custom links',
                'display_order' => 4,
                'available_plans' => ['basic', 'premium', 'business'],
                'has_fields' => true,
                'is_active' => true,
            ],
            [
                'key' => 'portfolio',
                'name' => 'Portfolio',
                'icon' => 'heroicons:briefcase',
                'category' => 'general',
                'description' => 'Showcase your projects and work samples',
                'display_order' => 5,
                'available_plans' => ['premium', 'business'],
                'has_fields' => true,
                'is_active' => true,
            ],
            [
                'key' => 'blog',
                'name' => 'Blog',
                'icon' => 'heroicons:newspaper',
                'category' => 'general',
                'description' => 'Blog posts and articles',
                'display_order' => 6,
                'available_plans' => ['business'],
                'has_fields' => true,
                'is_active' => true,
            ],
            [
                'key' => 'design',
                'name' => 'Design',
                'icon' => 'heroicons:paint-brush',
                'category' => 'design',
                'description' => 'Customize colors, themes, and layouts',
                'display_order' => 10,
                'available_plans' => ['basic', 'premium', 'business'],
                'has_fields' => false,
                'is_active' => true,
            ],
        ];

        foreach ($sections as $section) {
            ProfileBuilderSection::updateOrCreate(
                ['key' => $section['key']],
                $section
            );
        }

        $this->command->info('✅ Profile Builder Sections seeded: ' . count($sections) . ' sections.');
    }
}
