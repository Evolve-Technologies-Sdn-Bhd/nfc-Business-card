<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureToggleSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            [
                'type' => 'feature_toggle',
                'option_id' => 'contact_form',
                'name' => 'Contact Form',
                'description' => 'Enable contact form for direct messages',
                'config' => json_encode([]),
                'is_active' => true,
                'is_default' => false,
                'available_plans' => json_encode(['premium', 'business']),
                'display_order' => 10,
            ],
            [
                'type' => 'feature_toggle',
                'option_id' => 'vcard_download',
                'name' => 'Downloadable vCard',
                'description' => 'Allow visitors to download your contact as vCard',
                'config' => json_encode([]),
                'is_active' => true,
                'is_default' => true,
                'available_plans' => json_encode(['basic', 'premium', 'business']),
                'display_order' => 20,
            ],
            [
                'type' => 'feature_toggle',
                'option_id' => 'qr_code',
                'name' => 'QR Code Sharing',
                'description' => 'Show QR code for easy profile sharing',
                'config' => json_encode([]),
                'is_active' => true,
                'is_default' => true,
                'available_plans' => json_encode(['basic', 'premium', 'business']),
                'display_order' => 30,
            ],
            [
                'type' => 'feature_toggle',
                'option_id' => 'click_tracking',
                'name' => 'Click Analytics',
                'description' => 'Track link clicks and visitor analytics',
                'config' => json_encode([]),
                'is_active' => true,
                'is_default' => false,
                'available_plans' => json_encode(['premium', 'business']),
                'display_order' => 40,
            ],
            [
                'type' => 'feature_toggle',
                'option_id' => 'remove_branding',
                'name' => 'Remove Branding',
                'description' => 'Remove watermark and platform branding',
                'config' => json_encode([]),
                'is_active' => true,
                'is_default' => false,
                'available_plans' => json_encode(['premium', 'business']),
                'display_order' => 50,
            ],
            [
                'type' => 'feature_toggle',
                'option_id' => 'booking_integration',
                'name' => 'Booking Integration',
                'description' => 'Enable appointment booking feature',
                'config' => json_encode([]),
                'is_active' => true,
                'is_default' => false,
                'available_plans' => json_encode(['business']),
                'display_order' => 60,
            ],
        ];

        foreach ($features as $feature) {
            DB::table('profile_design_options')->updateOrInsert(
                ['option_id' => $feature['option_id'], 'type' => 'feature_toggle'],
                array_merge($feature, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✅ Feature toggles seeded: ' . count($features) . ' features');
    }
}
