<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProfileBuilderField;

/**
 * Blog Section Seeder
 * 
 * Example: Add Blog Section and related fields
 */
class BlogSectionSeeder extends Seeder
{
    public function run(): void
    {
        // BLOG TAB - Blog section fields

        ProfileBuilderField::create([
            'tab' => 'blog',
            'field_key' => 'blog_enabled',
            'field_type' => 'toggle',
            'label' => 'Enable Blog Section',
            'placeholder' => null,
            'help_text' => 'Show/hide blog section on your profile',
            'is_required' => false,
            'is_visible' => true,
            'validation_rules' => json_encode([]),
            'available_plans' => json_encode(['business']),
            'display_order' => 1,
            'config' => json_encode([
                'defaultValue' => true,
                'onLabel' => 'Enabled',
                'offLabel' => 'Disabled',
            ]),
        ]);

        ProfileBuilderField::create([
            'tab' => 'blog',
            'field_key' => 'blog_posts',
            'field_type' => 'repeater',
            'label' => 'Blog Posts',
            'placeholder' => null,
            'help_text' => 'Create and manage your blog posts',
            'is_required' => false,
            'is_visible' => true,
            'validation_rules' => json_encode([]),
            'available_plans' => json_encode(['business']),
            'display_order' => 2,
            'config' => json_encode([
                'max_items' => 20,
                'addButtonText' => 'Add Blog Post',
                'sub_fields' => [
                    [
                        'key' => 'title',
                        'label' => 'Post Title',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'My First Blog Post',
                    ],
                    [
                        'key' => 'slug',
                        'label' => 'URL Slug',
                        'type' => 'text',
                        'placeholder' => 'auto-generated-from-title',
                        'help_text' => 'Leave empty for auto-generation',
                    ],
                    [
                        'key' => 'coverImage',
                        'label' => 'Cover Image',
                        'type' => 'image',
                        'required' => true,
                        'maxSize' => 5242880, // 5MB
                    ],
                    [
                        'key' => 'category',
                        'label' => 'Category',
                        'type' => 'select',
                        'options' => ['Technology', 'Business', 'Lifestyle', 'News', 'Tutorial', 'Opinion'],
                    ],
                    [
                        'key' => 'tags',
                        'label' => 'Tags',
                        'type' => 'text',
                        'placeholder' => 'tag1, tag2, tag3',
                        'help_text' => 'Comma-separated tags',
                    ],
                    [
                        'key' => 'publishedDate',
                        'label' => 'Published Date',
                        'type' => 'date',
                        'required' => true,
                    ],
                    [
                        'key' => 'content',
                        'label' => 'Content',
                        'type' => 'richtext',
                        'required' => true,
                        'toolbar' => ['bold', 'italic', 'underline', 'link', 'bulletList', 'orderedList', 'heading'],
                    ],
                    [
                        'key' => 'excerpt',
                        'label' => 'Excerpt',
                        'type' => 'textarea',
                        'rows' => 3,
                        'maxLength' => 300,
                        'placeholder' => 'Brief summary of the post...',
                    ],
                    [
                        'key' => 'featured',
                        'label' => 'Featured Post',
                        'type' => 'checkbox',
                    ],
                    [
                        'key' => 'published',
                        'label' => 'Published',
                        'type' => 'toggle',
                        'defaultValue' => true,
                    ],
                ],
            ]),
        ]);
    }
}
