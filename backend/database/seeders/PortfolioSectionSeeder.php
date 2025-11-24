<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProfileBuilderField;

/**
 * Portfolio Section Seeder
 * 
 * Example: Add Portfolio Section and related fields
 */
class PortfolioSectionSeeder extends Seeder
{
    public function run(): void
    {
        // PORTFOLIO TAB - Portfolio section fields

        ProfileBuilderField::create([
            'tab' => 'portfolio',
            'field_key' => 'portfolio_title',
            'field_type' => 'text',
            'label' => 'Portfolio Title',
            'placeholder' => 'My Work',
            'help_text' => 'Main title for your portfolio section',
            'is_required' => false,
            'is_visible' => true,
            'validation_rules' => json_encode(['max' => 100]),
            'available_plans' => json_encode(['premium', 'business']),
            'display_order' => 1,
            'config' => json_encode([]),
        ]);

        ProfileBuilderField::create([
            'tab' => 'portfolio',
            'field_key' => 'portfolio_description',
            'field_type' => 'richtext',
            'label' => 'Portfolio Description',
            'placeholder' => 'Describe your portfolio...',
            'help_text' => 'Brief introduction to your work',
            'is_required' => false,
            'is_visible' => true,
            'validation_rules' => json_encode(['max' => 1000]),
            'available_plans' => json_encode(['premium', 'business']),
            'display_order' => 2,
            'config' => json_encode([
                'toolbar' => ['bold', 'italic', 'underline', 'link', 'bulletList'],
                'maxLength' => 1000,
            ]),
        ]);

        ProfileBuilderField::create([
            'tab' => 'portfolio',
            'field_key' => 'projects',
            'field_type' => 'repeater',
            'label' => 'Projects',
            'placeholder' => null,
            'help_text' => 'Add your projects and work samples',
            'is_required' => false,
            'is_visible' => true,
            'validation_rules' => json_encode([]),
            'available_plans' => json_encode(['premium', 'business']),
            'display_order' => 3,
            'config' => json_encode([
                'max_items' => 12,
                'addButtonText' => 'Add Project',
                'sub_fields' => [
                    [
                        'key' => 'title',
                        'label' => 'Project Title',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'E-commerce Platform',
                    ],
                    [
                        'key' => 'category',
                        'label' => 'Category',
                        'type' => 'select',
                        'options' => ['Web Design', 'Mobile App', 'Branding', 'Photography', 'Video', 'Other'],
                    ],
                    [
                        'key' => 'description',
                        'label' => 'Description',
                        'type' => 'richtext',
                        'rows' => 4,
                    ],
                    [
                        'key' => 'coverImage',
                        'label' => 'Cover Image',
                        'type' => 'image',
                        'maxSize' => 5242880, // 5MB
                    ],
                    [
                        'key' => 'gallery',
                        'label' => 'Project Gallery',
                        'type' => 'gallery',
                        'maxItems' => 10,
                        'allowImages' => true,
                        'allowVideos' => false,
                    ],
                    [
                        'key' => 'projectUrl',
                        'label' => 'Project URL',
                        'type' => 'url',
                        'placeholder' => 'https://project.com',
                    ],
                    [
                        'key' => 'dateCompleted',
                        'label' => 'Completion Date',
                        'type' => 'date',
                    ],
                    [
                        'key' => 'clientName',
                        'label' => 'Client Name',
                        'type' => 'text',
                        'placeholder' => 'Company ABC',
                    ],
                    [
                        'key' => 'tags',
                        'label' => 'Tags',
                        'type' => 'text',
                        'placeholder' => 'React, Node.js, MongoDB',
                        'help_text' => 'Comma-separated tags',
                    ],
                ],
            ]),
        ]);
    }
}
