<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfileDesignOptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            // ========== THEMES ==========
            [
                'type' => 'theme',
                'option_id' => 'minimal',
                'name' => 'Minimal',
                'description' => 'Clean white background with subtle contrast — perfect for professional use.',
                'config' => [
                    'backgroundColor' => '#FFFFFF',
                    'textColor' => '#111827',
                    'accentColor' => '#1F2937',
                    'preview' => 'bg-white',
                ],
                'is_active' => true,
                'is_default' => true,
                'available_plans' => ['basic', 'premium', 'business'],
                'display_order' => 1,
            ],
            [
                'type' => 'theme',
                'option_id' => 'modern',
                'name' => 'Modern Soft',
                'description' => 'Light gray gradient with soft shadows — contemporary and easy on the eyes.',
                'config' => [
                    'backgroundColor' => '#F9FAFB',
                    'textColor' => '#111827',
                    'accentColor' => '#2563EB',
                    'preview' => 'bg-gradient-to-br from-gray-50 to-gray-100',
                ],
                'is_active' => true,
                'is_default' => false,
                'available_plans' => ['basic', 'premium', 'business'],
                'display_order' => 2,
            ],
            [
                'type' => 'theme',
                'option_id' => 'zora-pro',
                'name' => 'Zora Pro',
                'description' => 'Premium Navy + Matte Teal theme with high readability and elegant contrast.',
                'config' => [
                    'backgroundColor' => '#0F2744',
                    'textColor' => '#E8EEF4',
                    'accentColor' => '#5B8A86',
                    'preview' => 'bg-[#0F2744]',
                ],
                'is_active' => true,
                'is_default' => false,
                'available_plans' => ['premium', 'business'],
                'display_order' => 3,
            ],
            [
                'type' => 'theme',
                'option_id' => 'professional',
                'name' => 'Corporate Blue',
                'description' => 'Strong blue gradient — ideal for companies and institutions.',
                'config' => [
                    'backgroundColor' => '#2563EB',
                    'textColor' => '#FFFFFF',
                    'accentColor' => '#1E40AF',
                    'preview' => 'bg-gradient-to-br from-blue-600 to-blue-700',
                ],
                'is_active' => true,
                'is_default' => false,
                'available_plans' => ['premium', 'business'],
                'display_order' => 4,
            ],
            [
                'type' => 'theme',
                'option_id' => 'warm-sand',
                'name' => 'Warm Sand',
                'description' => 'Soft cream and earth tones — warm, inviting, and mature aesthetic.',
                'config' => [
                    'backgroundColor' => '#FBF7F0',
                    'textColor' => '#3F3427',
                    'accentColor' => '#B45309',
                    'preview' => 'bg-[#FBF7F0]',
                ],
                'is_active' => true,
                'is_default' => false,
                'available_plans' => ['basic', 'premium', 'business'],
                'display_order' => 5,
            ],
            [
                'type' => 'theme',
                'option_id' => 'deep-charcoal',
                'name' => 'Deep Charcoal',
                'description' => 'Matte dark gray (not OLED black) — gentle contrast for low-light use.',
                'config' => [
                    'backgroundColor' => '#1E293B',
                    'textColor' => '#E2E8F0',
                    'accentColor' => '#60A5FA',
                    'preview' => 'bg-slate-800',
                ],
                'is_active' => true,
                'is_default' => false,
                'available_plans' => ['premium', 'business'],
                'display_order' => 6,
            ],

            // ========== FONTS ==========
            [
                'type' => 'font',
                'option_id' => 'inter',
                'name' => 'Inter',
                'description' => 'Modern sans-serif — clean, legible, default choice.',
                'config' => [
                    'family' => 'Inter, system-ui, -apple-system, sans-serif',
                    'weights' => [400, 500, 600, 700],
                ],
                'is_active' => true,
                'is_default' => true,
                'available_plans' => ['basic', 'premium', 'business'],
                'display_order' => 1,
            ],
            [
                'type' => 'font',
                'option_id' => 'poppins',
                'name' => 'Poppins',
                'description' => 'Geometric sans-serif — friendly and contemporary.',
                'config' => [
                    'family' => 'Poppins, Inter, system-ui, sans-serif',
                    'weights' => [400, 500, 600, 700],
                ],
                'is_active' => true,
                'is_default' => false,
                'available_plans' => ['basic', 'premium', 'business'],
                'display_order' => 2,
            ],
            [
                'type' => 'font',
                'option_id' => 'roboto',
                'name' => 'Roboto',
                'description' => 'Classic Android font — neutral and universally readable.',
                'config' => [
                    'family' => 'Roboto, Inter, system-ui, sans-serif',
                    'weights' => [400, 500, 700],
                ],
                'is_active' => true,
                'is_default' => false,
                'available_plans' => ['basic', 'premium', 'business'],
                'display_order' => 3,
            ],
            [
                'type' => 'font',
                'option_id' => 'playfair',
                'name' => 'Playfair Display',
                'description' => 'Elegant serif — perfect for luxury, legal, or editorial brands.',
                'config' => [
                    'family' => 'Playfair Display, Georgia, serif',
                    'weights' => [400, 500, 600, 700],
                ],
                'is_active' => true,
                'is_default' => false,
                'available_plans' => ['premium', 'business'],
                'display_order' => 4,
            ],
            [
                'type' => 'font',
                'option_id' => 'lora',
                'name' => 'Lora',
                'description' => 'Highly readable serif — comfortable for long-form text.',
                'config' => [
                    'family' => 'Lora, Georgia, serif',
                    'weights' => [400, 500, 600, 700],
                ],
                'is_active' => true,
                'is_default' => false,
                'available_plans' => ['premium', 'business'],
                'display_order' => 5,
            ],

            // ========== BUTTON STYLES ==========
            [
                'type' => 'button_style',
                'option_id' => 'solid',
                'name' => 'Solid Pill',
                'description' => 'Full-color rounded pill button — high visibility.',
                'config' => [
                    'class' => 'bg-foreground text-background font-semibold rounded-full',
                ],
                'is_active' => true,
                'is_default' => true,
                'available_plans' => ['basic', 'premium', 'business'],
                'display_order' => 1,
            ],
            [
                'type' => 'button_style',
                'option_id' => 'outline',
                'name' => 'Outline Pill',
                'description' => 'Bordered button with transparent background — understated.',
                'config' => [
                    'class' => 'border-2 border-foreground text-foreground font-medium rounded-full',
                ],
                'is_active' => true,
                'is_default' => false,
                'available_plans' => ['basic', 'premium', 'business'],
                'display_order' => 2,
            ],
            [
                'type' => 'button_style',
                'option_id' => 'soft',
                'name' => 'Soft Rounded',
                'description' => 'Muted surface tone with rounded corners — modern and gentle.',
                'config' => [
                    'class' => 'bg-white/10 backdrop-blur text-foreground font-medium rounded-2xl',
                ],
                'is_active' => true,
                'is_default' => false,
                'available_plans' => ['premium', 'business'],
                'display_order' => 3,
            ],
            [
                'type' => 'button_style',
                'option_id' => 'shadow',
                'name' => 'Elevation Shadow',
                'description' => 'White surface with soft drop shadow — elevated card style.',
                'config' => [
                    'class' => 'bg-white text-gray-900 font-semibold rounded-2xl shadow-lg',
                ],
                'is_active' => true,
                'is_default' => false,
                'available_plans' => ['premium', 'business'],
                'display_order' => 4,
            ],
            [
                'type' => 'button_style',
                'option_id' => 'square',
                'name' => 'Sharp Square',
                'description' => 'No-radius sharp corners — minimalist brutalist style.',
                'config' => [
                    'class' => 'bg-foreground text-background font-semibold rounded-none',
                ],
                'is_active' => true,
                'is_default' => false,
                'available_plans' => ['premium', 'business'],
                'display_order' => 5,
            ],

            // ========== PROFILE STYLES (Layout Variant) ==========
            [
                'type' => 'profile_style',
                'option_id' => 'classic',
                'name' => 'Classic Center',
                'description' => 'Centered avatar with vertical stack — traditional business card layout.',
                'config' => [
                    'avatarPosition' => 'center',
                    'layout' => 'vertical',
                ],
                'is_active' => true,
                'is_default' => true,
                'available_plans' => ['basic', 'premium', 'business'],
                'display_order' => 1,
            ],
            [
                'type' => 'profile_style',
                'option_id' => 'side-avatar',
                'name' => 'Side Avatar',
                'description' => 'Left-aligned avatar with right content — modern compact layout.',
                'config' => [
                    'avatarPosition' => 'left',
                    'layout' => 'horizontal',
                ],
                'is_active' => true,
                'is_default' => false,
                'available_plans' => ['premium', 'business'],
                'display_order' => 2,
            ],
            [
                'type' => 'profile_style',
                'option_id' => 'cover-photo',
                'name' => 'Cover + Profile',
                'description' => 'Hero cover banner at top with profile below — magazine style.',
                'config' => [
                    'avatarPosition' => 'center',
                    'layout' => 'cover',
                    'requireCoverImage' => true,
                ],
                'is_active' => true,
                'is_default' => false,
                'available_plans' => ['premium', 'business'],
                'display_order' => 3,
            ],

            // ========== FEATURE TOGGLE: Layout Designer ==========
            [
                'type' => 'feature_toggle',
                'option_id' => 'layout_designer',
                'name' => 'Layout Designer',
                'description' => 'Allow users to customize the order and visibility of sections on their landing page.',
                'config' => [
                    'icon' => 'heroicons:squares-2x2',
                    'category' => 'customization',
                ],
                'is_active' => true,
                'is_default' => false,
                'available_plans' => ['premium', 'business'],
                'display_order' => 100,
            ],
        ];

        foreach ($options as $option) {
            DB::table('profile_design_options')->updateOrInsert(
                ['type' => $option['type'], 'option_id' => $option['option_id']],
                array_merge($option, [
                    'config' => json_encode($option['config']),
                    'available_plans' => json_encode($option['available_plans']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✅ Profile Design Options seeded: ' . count($options) . ' entries (themes, fonts, buttons, layouts).');
    }
}
