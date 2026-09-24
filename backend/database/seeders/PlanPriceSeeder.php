<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlanPrice;

class PlanPriceSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'plan_type' => 'basic',
                'price' => 99.00,
                'currency' => 'MYR',
                'description' => 'Basic NFC Card Plan — suitable for individuals starting with digital networking.',
                'features' => [
                    'One NFC physical card',
                    'Basic profile customization',
                    'Contact information sharing',
                    'Unlimited taps & shares',
                    'QR code sharing',
                    '3 months free subscription',
                ],
                'is_active' => true,
            ],
            [
                'plan_type' => 'premium',
                'price' => 199.00,
                'currency' => 'MYR',
                'description' => 'Premium NFC Card Plan — for professionals who need advanced customization.',
                'features' => [
                    'One premium NFC card',
                    'Advanced profile customization',
                    'Social media integration',
                    'Portfolio & services section',
                    'Advanced analytics dashboard',
                    'vCard download',
                    'Click tracking',
                    '6 months free subscription',
                    'Priority support',
                ],
                'is_active' => true,
            ],
            [
                'plan_type' => 'business',
                'price' => 299.00,
                'currency' => 'MYR',
                'description' => 'Business NFC Card Plan — for teams, organizations, and bulk ordering.',
                'features' => [
                    'Multiple NFC cards (minimum 5)',
                    'Employee / team management',
                    'Bulk ordering & discounts',
                    'Blog section',
                    'Advanced business analytics',
                    'Booking integration',
                    'Remove platform branding',
                    '12 months free subscription',
                    'Dedicated account manager',
                    'Custom design service',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            PlanPrice::updateOrCreate(
                ['plan_type' => $plan['plan_type']],
                $plan
            );
        }

        $this->command->info('✅ Plan prices seeded: ' . count($plans) . ' plans (Basic, Premium, Business).');
    }
}
