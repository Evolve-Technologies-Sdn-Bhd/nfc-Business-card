<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CardTemplate;
use Illuminate\Support\Str;

class CardTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Classic Monochrome',
                'description' => 'Timeless black-and-white design with large typography. Best for legal, finance, and corporate professionals.',
                'category' => 'professional',
                'plan_types' => ['basic', 'premium', 'business'],
                'color_scheme' => [
                    'primary' => '#111111',
                    'secondary' => '#FFFFFF',
                    'accent' => '#888888',
                ],
                'thumbnail_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=minimal%20monochrome%20nfc%20business%20card%20mockup%20front%20view%20clean%20professional%20white%20background&image_size=square',
                'front_image_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=elegant%20black%20and%20white%20nfc%20business%20card%20front%20side%20with%20name%20title%20and%20minimal%20logo%20mockup&image_size=landscape_16_9',
                'back_image_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=nfc%20business%20card%20back%20side%20nfc%20chip%20icon%20and%20qr%20code%20clean%20layout%20mockup&image_size=landscape_16_9',
                'is_active' => true,
                'is_hidden' => false,
                'sort_order' => 1,
                'processing_status' => 'completed',
            ],
            [
                'name' => 'Navy Signature',
                'description' => 'Deep navy background with matte teal accent. Premium executive look inspired by Zora Pro palette.',
                'category' => 'premium',
                'plan_types' => ['premium', 'business'],
                'color_scheme' => [
                    'primary' => '#0F2744',
                    'secondary' => '#E8EEF4',
                    'accent' => '#5B8A86',
                ],
                'thumbnail_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=premium%20navy%20blue%20and%20teal%20nfc%20business%20card%20mockup%20elegant%20minimal%20design&image_size=square',
                'front_image_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=navy%20blue%20nfc%20business%20card%20front%20with%20teal%20accent%20typography%20luxury%20mockup&image_size=landscape_16_9',
                'back_image_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=nfc%20business%20card%20back%20navy%20background%20teal%20nfc%20symbol%20qr%20code%20premium%20mockup&image_size=landscape_16_9',
                'is_active' => true,
                'is_hidden' => false,
                'sort_order' => 2,
                'processing_status' => 'completed',
            ],
            [
                'name' => 'Warm Sand Minimal',
                'description' => 'Soft cream background with warm accent tone. Approachable, artistic, and mature-friendly aesthetic.',
                'category' => 'creative',
                'plan_types' => ['basic', 'premium', 'business'],
                'color_scheme' => [
                    'primary' => '#FBF7F0',
                    'secondary' => '#3F3427',
                    'accent' => '#B45309',
                ],
                'thumbnail_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=warm%20cream%20sand%20colored%20nfc%20business%20card%20minimal%20design%20mockup%20soft%20tones&image_size=square',
                'front_image_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=warm%20cream%20nfc%20business%20card%20front%20with%20amber%20accent%20font%20design%20mockup&image_size=landscape_16_9',
                'back_image_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=nfc%20business%20card%20back%20warm%20cream%20sand%20nfc%20logo%20qr%20code%20minimal%20mockup&image_size=landscape_16_9',
                'is_active' => true,
                'is_hidden' => false,
                'sort_order' => 3,
                'processing_status' => 'completed',
            ],
            [
                'name' => 'Charcoal Tech',
                'description' => 'Matte dark gray surface with electric blue details. Suited for tech, engineering, and developer profiles.',
                'category' => 'tech',
                'plan_types' => ['premium', 'business'],
                'color_scheme' => [
                    'primary' => '#1E293B',
                    'secondary' => '#E2E8F0',
                    'accent' => '#60A5FA',
                ],
                'thumbnail_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=dark%20charcoal%20nfc%20business%20card%20with%20electric%20blue%20accent%20tech%20aesthetic%20mockup&image_size=square',
                'front_image_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=charcoal%20gray%20nfc%20business%20card%20front%20side%20with%20blue%20neon%20accent%20developer%20design%20mockup&image_size=landscape_16_9',
                'back_image_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=nfc%20business%20card%20back%20dark%20charcoal%20blue%20nfc%20chip%20illustration%20qr%20code%20mockup&image_size=landscape_16_9',
                'is_active' => true,
                'is_hidden' => false,
                'sort_order' => 4,
                'processing_status' => 'completed',
            ],
            [
                'name' => 'Emerald Executive',
                'description' => 'Deep emerald green with gold accent line. For entrepreneurs, consulting, and leadership roles.',
                'category' => 'premium',
                'plan_types' => ['business'],
                'color_scheme' => [
                    'primary' => '#064E3B',
                    'secondary' => '#ECFDF5',
                    'accent' => '#D4AF37',
                ],
                'thumbnail_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=deep%20emerald%20green%20and%20gold%20nfc%20business%20card%20luxury%20executive%20mockup&image_size=square',
                'front_image_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=emerald%20green%20nfc%20business%20card%20front%20with%20gold%20thin%20line%20accent%20luxury%20mockup&image_size=landscape_16_9',
                'back_image_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=nfc%20business%20card%20back%20emerald%20green%20gold%20nfc%20emblem%20qr%20code%20mockup&image_size=landscape_16_9',
                'is_active' => true,
                'is_hidden' => false,
                'sort_order' => 5,
                'processing_status' => 'completed',
            ],
            [
                'name' => 'Gradient Graduation',
                'description' => 'Soft violet-to-rose gradient. Best for graduates, creatives, influencers, and personal brands.',
                'category' => 'creative',
                'plan_types' => ['basic', 'premium'],
                'color_scheme' => [
                    'primary' => '#7C3AED',
                    'secondary' => '#FFF1F2',
                    'accent' => '#F472B6',
                ],
                'thumbnail_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=violet%20to%20pink%20gradient%20nfc%20business%20card%20modern%20creative%20design%20mockup&image_size=square',
                'front_image_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=violet%20rose%20gradient%20nfc%20business%20card%20front%20with%20white%20typeface%20creative%20mockup&image_size=landscape_16_9',
                'back_image_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=nfc%20business%20card%20back%20gradient%20purple%20pink%20nfc%20icon%20qr%20code%20mockup&image_size=landscape_16_9',
                'is_active' => true,
                'is_hidden' => false,
                'sort_order' => 6,
                'processing_status' => 'completed',
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($templates as $tpl) {
            $existing = CardTemplate::where('name', $tpl['name'])->first();

            if ($existing) {
                $existing->update($tpl);
                $updated++;
            } else {
                CardTemplate::create(array_merge($tpl, [
                    'slug' => Str::slug($tpl['name']) . '-' . Str::random(6),
                ]));
                $created++;
            }
        }

        $this->command->info("✅ Card Templates seeded: {$created} created, {$updated} updated (total " . count($templates) . ").");
    }
}
