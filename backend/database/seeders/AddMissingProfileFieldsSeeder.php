<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddMissingProfileFieldsSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            // ==================== PROFILE TAB - MULTIMEDIA ====================
            
            // Introduction Video
            [
                'tab' => 'profile',
                'field_key' => 'introVideo',
                'field_type' => 'url',
                'label' => 'Introduction Video',
                'placeholder' => 'YouTube or Vimeo URL',
                'help_text' => 'Add a video introduction (supports YouTube, Vimeo)',
                'is_required' => false,
                'is_visible' => true,
                'validation_rules' => json_encode(['url']),
                'available_plans' => json_encode(['premium', 'business']),
                'display_order' => 95,
                'config' => json_encode([
                    'supported_platforms' => ['youtube', 'vimeo']
                ]),
            ],

            // Contact Form Toggle
            [
                'tab' => 'profile',
                'field_key' => 'contactFormEnabled',
                'field_type' => 'toggle',
                'label' => 'Enable Contact Form',
                'placeholder' => null,
                'help_text' => 'Allow visitors to send you direct messages through your profile',
                'is_required' => false,
                'is_visible' => true,
                'validation_rules' => json_encode([]),
                'available_plans' => json_encode(['premium', 'business']),
                'display_order' => 140,
                'config' => json_encode([
                    'default' => true
                ]),
            ],

            // Contact Form Email
            [
                'tab' => 'profile',
                'field_key' => 'contactFormEmail',
                'field_type' => 'email',
                'label' => 'Contact Form Email',
                'placeholder' => 'Where to receive messages',
                'help_text' => 'Email address to receive contact form submissions',
                'is_required' => false,
                'is_visible' => true,
                'validation_rules' => json_encode(['email']),
                'available_plans' => json_encode(['premium', 'business']),
                'display_order' => 141,
                'config' => json_encode([]),
            ],

            // ==================== SERVICES TAB - PORTFOLIO ====================
            
            // Photo Gallery / Portfolio
            [
                'tab' => 'services',
                'field_key' => 'gallery',
                'field_type' => 'image_gallery',
                'label' => 'Photo Gallery / Portfolio',
                'placeholder' => null,
                'help_text' => 'Upload up to 12 images to showcase your work',
                'is_required' => false,
                'is_visible' => true,
                'validation_rules' => json_encode([]),
                'available_plans' => json_encode(['premium', 'business']),
                'display_order' => 320,
                'config' => json_encode([
                    'max_images' => 12,
                    'allowed_types' => ['jpg', 'jpeg', 'png', 'webp'],
                    'max_file_size' => 5 // MB
                ]),
            ],

            // ==================== LINKS TAB - BOOKING & PAYMENT ====================
            
            // Appointment Booking Link
            [
                'tab' => 'links',
                'field_key' => 'appointmentLink',
                'field_type' => 'url',
                'label' => 'Appointment Booking Link',
                'placeholder' => 'e.g., https://calendly.com/yourname',
                'help_text' => 'Link to your booking calendar (Calendly, Google Calendar, etc.)',
                'is_required' => false,
                'is_visible' => true,
                'validation_rules' => json_encode(['url']),
                'available_plans' => json_encode(['premium', 'business']),
                'display_order' => 400,
                'config' => json_encode([]),
            ],

            // Payment Button Text
            [
                'tab' => 'links',
                'field_key' => 'paymentButtonText',
                'field_type' => 'text',
                'label' => 'Payment Button Text',
                'placeholder' => 'e.g., Buy Now, Order Here',
                'help_text' => 'Text to display on the payment button',
                'is_required' => false,
                'is_visible' => true,
                'validation_rules' => json_encode([]),
                'available_plans' => json_encode(['business']),
                'display_order' => 410,
                'config' => json_encode([]),
            ],

            // Payment Button URL
            [
                'tab' => 'links',
                'field_key' => 'paymentButtonUrl',
                'field_type' => 'url',
                'label' => 'Payment Link',
                'placeholder' => 'e.g., https://payment.com/yourstore',
                'help_text' => 'Link to your payment gateway or online store',
                'is_required' => false,
                'is_visible' => true,
                'validation_rules' => json_encode(['url']),
                'available_plans' => json_encode(['business']),
                'display_order' => 411,
                'config' => json_encode([]),
            ],

            // ==================== LINKS TAB - SOCIAL MEDIA (如果需要额外配置) ====================
            
            // Social Media Links (如果当前 Links 功能需要在后端配置)
            [
                'tab' => 'links',
                'field_key' => 'socialLinks',
                'field_type' => 'repeater',
                'label' => 'Social Media Links',
                'placeholder' => null,
                'help_text' => 'Add your social media profiles',
                'is_required' => false,
                'is_visible' => true,
                'validation_rules' => json_encode([]),
                'available_plans' => json_encode(['basic', 'premium', 'business']),
                'display_order' => 420,
                'config' => json_encode([
                    'max_items' => 10,
                    'sub_fields' => [
                        [
                            'key' => 'platform',
                            'label' => 'Platform',
                            'type' => 'select',
                            'options' => [
                                'facebook' => 'Facebook',
                                'instagram' => 'Instagram',
                                'linkedin' => 'LinkedIn',
                                'twitter' => 'Twitter / X',
                                'tiktok' => 'TikTok',
                                'youtube' => 'YouTube',
                                'whatsapp' => 'WhatsApp',
                                'telegram' => 'Telegram',
                                'github' => 'GitHub',
                                'custom' => 'Custom Link'
                            ]
                        ],
                        [
                            'key' => 'url',
                            'label' => 'URL',
                            'type' => 'url',
                            'placeholder' => 'https://...'
                        ]
                    ]
                ]),
            ],
        ];

        foreach ($fields as $field) {
            DB::table('profile_builder_fields')->updateOrInsert(
                ['field_key' => $field['field_key']],
                $field
            );
        }

        $this->command->info('✅ Missing profile fields added successfully!');
        $this->command->info('📊 Added fields:');
        $this->command->info('   - Introduction Video (profile)');
        $this->command->info('   - Contact Form Toggle + Email (profile)');
        $this->command->info('   - Photo Gallery (services)');
        $this->command->info('   - Appointment Booking Link (links)');
        $this->command->info('   - Payment Button (links)');
        $this->command->info('   - Social Media Links Config (links)');
    }
}
