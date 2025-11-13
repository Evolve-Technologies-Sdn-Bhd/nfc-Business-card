<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChatbotQuestion;

class ChatbotQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            [
                'question' => 'What is an NFC business card?',
                'answer' => 'An NFC business card is a smart digital business card that uses Near Field Communication (NFC) technology to share your contact information instantly. Simply tap your card against a smartphone, and your digital profile opens automatically - no app required!',
                'keywords' => ['nfc', 'what is nfc', 'business card', 'digital card', 'smart card', 'technology'],
                'priority' => 100,
                'is_active' => true,
            ],
            [
                'question' => 'How does NFC technology work?',
                'answer' => 'NFC (Near Field Communication) is a wireless technology that allows two devices to communicate when they\'re close together (within a few centimeters). When someone taps your NFC card with their smartphone, it instantly transfers your contact information and opens your digital profile.',
                'keywords' => ['nfc', 'how does it work', 'technology', 'wireless', 'tap'],
                'priority' => 95,
                'is_active' => true,
            ],
            [
                'question' => 'What are your pricing plans?',
                'answer' => "We offer three plans:\n\n• Basic Plan ($9/month): 1 NFC card, basic profile features\n• Professional Plan ($19/month): 3 NFC cards, advanced analytics, custom branding\n• Business Plan ($49/month): Unlimited cards, team management, priority support\n\nAll plans include a 14-day free trial!",
                'keywords' => ['pricing', 'cost', 'plans', 'price', 'how much', 'subscription', 'payment'],
                'priority' => 90,
                'is_active' => true,
            ],
            [
                'question' => 'Do I need an app to use NFC cards?',
                'answer' => 'No app required! NFC cards work with any NFC-enabled smartphone (most modern smartphones have NFC). When someone taps your card, their phone automatically opens your digital profile in their web browser.',
                'keywords' => ['app', 'application', 'download', 'install', 'phone', 'requirements'],
                'priority' => 85,
                'is_active' => true,
            ],
            [
                'question' => 'How do I customize my digital profile?',
                'answer' => 'Log in to your dashboard, go to "My Profile", and you can customize:\n\n• Profile photo and banner image\n• Contact information (phone, email, address)\n• Social media links\n• Portfolio or work samples\n• Custom colors and themes\n• Bio and description\n\nChanges are saved instantly and reflected when someone taps your card!',
                'keywords' => ['customize', 'edit', 'profile', 'personalize', 'change', 'update', 'design'],
                'priority' => 80,
                'is_active' => true,
            ],
            [
                'question' => 'What phones are compatible with NFC cards?',
                'answer' => 'NFC cards work with most modern smartphones:\n\n• iPhone 7 and newer (iOS 11+)\n• Most Android phones from 2015 onwards\n• Samsung Galaxy series\n• Google Pixel series\n\nBasically, if a phone supports Apple Pay or Google Pay, it has NFC and will work with our cards!',
                'keywords' => ['compatible', 'compatibility', 'phone', 'iphone', 'android', 'samsung', 'device', 'work with'],
                'priority' => 75,
                'is_active' => true,
            ],
            [
                'question' => 'How long does shipping take?',
                'answer' => 'Standard shipping takes 5-7 business days within the US. Express shipping (2-3 business days) is available for an additional fee. International shipping typically takes 10-15 business days. You\'ll receive a tracking number once your order ships!',
                'keywords' => ['shipping', 'delivery', 'how long', 'tracking', 'international', 'express'],
                'priority' => 70,
                'is_active' => true,
            ],
            [
                'question' => 'Can I cancel my subscription anytime?',
                'answer' => 'Yes! You can cancel your subscription at any time from your account settings. There are no cancellation fees or long-term contracts. If you cancel, you\'ll continue to have access until the end of your current billing period.',
                'keywords' => ['cancel', 'cancellation', 'unsubscribe', 'stop', 'refund', 'subscription'],
                'priority' => 65,
                'is_active' => true,
            ],
            [
                'question' => 'Is my data secure?',
                'answer' => 'Absolutely! We take security seriously:\n\n• All data is encrypted in transit and at rest\n• We use industry-standard SSL/TLS encryption\n• Your payment information is processed securely through Stripe\n• We never share your personal data with third parties\n• You have full control over what information is displayed on your profile',
                'keywords' => ['security', 'secure', 'safe', 'privacy', 'data', 'encryption', 'protection'],
                'priority' => 60,
                'is_active' => true,
            ],
            [
                'question' => 'Can I track who views my profile?',
                'answer' => 'Yes! Our Professional and Business plans include analytics that show:\n\n• Number of profile views\n• When your card was tapped\n• Which links were clicked\n• Geographic data (city/country)\n\nNote: Analytics are anonymous - you can see stats but not personal information about viewers.',
                'keywords' => ['analytics', 'tracking', 'views', 'statistics', 'data', 'monitor'],
                'priority' => 55,
                'is_active' => true,
            ],
        ];

        foreach ($questions as $question) {
            ChatbotQuestion::create($question);
        }
    }
}
