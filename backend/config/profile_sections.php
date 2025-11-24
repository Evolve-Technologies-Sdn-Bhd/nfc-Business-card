<?php

/**
 * Profile Builder Sections Configuration
 * 
 * Defines all available sections and their metadata
 * The 'tab' field in profile_builder_fields will use these keys as identifiers
 */

return [
    'sections' => [
        // PROFILE SECTION
        'profile' => [
            'name' => 'Profile',
            'icon' => 'heroicons:user',
            'category' => 'general',
            'description' => 'Personal information and bio',
            'display_order' => 1,
            'available_plans' => ['basic', 'premium', 'business'],
        ],

        // COMPANY SECTION
        'company' => [
            'name' => 'Company & Team',
            'icon' => 'heroicons:building-office',
            'category' => 'general',
            'description' => 'Company information and team members',
            'display_order' => 2,
            'available_plans' => ['premium', 'business'],
        ],

        // SERVICES SECTION
        'services' => [
            'name' => 'Services',
            'icon' => 'heroicons:rocket-launch',
            'category' => 'general',
            'description' => 'Services and products you offer',
            'display_order' => 3,
            'available_plans' => ['premium', 'business'],
        ],

        // LINKS SECTION
        'links' => [
            'name' => 'Social Media & Links',
            'icon' => 'heroicons:link',
            'category' => 'general',
            'description' => 'Social media profiles and custom links',
            'display_order' => 4,
            'available_plans' => ['basic', 'premium', 'business'],
        ],

        // PORTFOLIO SECTION
        'portfolio' => [
            'name' => 'Portfolio',
            'icon' => 'heroicons:briefcase',
            'category' => 'general',
            'description' => 'Showcase your projects and work samples',
            'display_order' => 5,
            'available_plans' => ['premium', 'business'],
        ],

        // BLOG SECTION
        'blog' => [
            'name' => 'Blog',
            'icon' => 'heroicons:newspaper',
            'category' => 'general',
            'description' => 'Blog posts and articles',
            'display_order' => 6,
            'available_plans' => ['business'],
        ],

        // DESIGN SECTION
        'design' => [
            'name' => 'Design',
            'icon' => 'heroicons:paint-brush',
            'category' => 'design',
            'description' => 'Customize colors, themes, and layouts',
            'display_order' => 10,
            'available_plans' => ['basic', 'premium', 'business'],
        ],
    ],

    /**
     * Available field types
     */
    'field_types' => [
        'text' => 'Text Input',
        'email' => 'Email',
        'tel' => 'Phone Number',
        'url' => 'URL',
        'textarea' => 'Text Area',
        'richtext' => 'Rich Text Editor',
        'number' => 'Number',
        'date' => 'Date Picker',
        'select' => 'Dropdown Select',
        'image' => 'Image Upload',
        'video' => 'Video Upload/URL',
        'file' => 'File Upload',
        'gallery' => 'Image/Video Gallery',
        'repeater' => 'Repeatable Fields',
        'toggle' => 'Toggle Switch',
        'checkbox' => 'Checkbox',
        'icon' => 'Icon Picker',
        'color' => 'Color Picker',
    ],
];
