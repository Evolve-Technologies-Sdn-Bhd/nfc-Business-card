<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChatbotQuestion;

class ChatbotQuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            [
                'question' => 'What services do you offer?',
                'answer' => 'We offer NFC business card solutions including digital business cards, custom NFC card design, contactless sharing, and analytics dashboard. Our services help you make lasting impressions with modern, eco-friendly digital networking tools.',
                'keywords' => ['services', 'offer', 'what do you do', 'products', 'solutions', 'nfc', 'business card'],
                'priority' => 100,
            ],
            [
                'question' => 'What are your business hours?',
                'answer' => 'Our support team is available Monday through Friday, 9:00 AM to 6:00 PM (PST). For urgent inquiries outside business hours, please email us at support@nfcgo.com and we\'ll respond within 24 hours.',
                'keywords' => ['hours', 'time', 'open', 'available', 'when', 'schedule', 'contact time'],
                'priority' => 90,
            ],
            [
                'question' => 'How do I contact you?',
                'answer' => 'You can reach us via:\n- Email: support@nfcgo.com\n- Phone: +1 (555) 123-4567\n- Live Chat: Available on our website during business hours\n- Social Media: @NFCGo on Twitter, Instagram, and LinkedIn',
                'keywords' => ['contact', 'email', 'phone', 'reach', 'support', 'help', 'get in touch'],
                'priority' => 95,
            ],
            [
                'question' => 'What is NFC technology?',
                'answer' => 'NFC (Near Field Communication) is a wireless technology that allows devices to communicate when they\'re close together (usually within 4 cm). Our NFC business cards contain a small chip that shares your contact information instantly when tapped against a smartphone - no app required!',
                'keywords' => ['nfc', 'technology', 'how it works', 'what is', 'near field', 'wireless', 'chip'],
                'priority' => 85,
            ],
            [
                'question' => 'How do NFC business cards work?',
                'answer' => 'Simply tap your NFC business card against any smartphone, and your digital profile opens instantly in their browser. No app needed! Recipients can save your contact info, view your portfolio, connect on social media, and more - all with one tap.',
                'keywords' => ['how', 'work', 'use', 'tap', 'smartphone', 'function', 'operate'],
                'priority' => 88,
            ],
            [
                'question' => 'How much does it cost?',
                'answer' => 'We offer flexible pricing plans:\n- **Free Plan**: Digital profile only\n- **Basic Plan**: $9/month - 1 physical NFC card + digital profile\n- **Premium Plan**: $19/month - 3 physical NFC cards + advanced analytics\n- **Business Plan**: $49/month - 10 cards + team management\n\nAll plans include a 14-day free trial!',
                'keywords' => ['price', 'cost', 'pricing', 'how much', 'plans', 'subscription', 'payment', 'fee'],
                'priority' => 92,
            ],
            [
                'question' => 'Can I get a quote?',
                'answer' => 'Absolutely! For custom enterprise solutions or bulk orders, please contact our sales team at sales@nfcgo.com or call +1 (555) 123-4567. We\'ll create a tailored package that fits your needs and budget.',
                'keywords' => ['quote', 'estimate', 'custom', 'bulk', 'enterprise', 'pricing'],
                'priority' => 80,
            ],
            [
                'question' => 'Do you offer international shipping?',
                'answer' => 'Yes! We ship NFC cards worldwide. Shipping costs and delivery times vary by location:\n- USA: 3-5 business days ($5)\n- Canada: 5-7 business days ($8)\n- International: 7-14 business days ($12-20)\n\nFree shipping on orders over $100!',
                'keywords' => ['shipping', 'delivery', 'international', 'worldwide', 'ship', 'send'],
                'priority' => 75,
            ],
            [
                'question' => 'Can I customize my NFC card design?',
                'answer' => 'Yes! You can fully customize your NFC card design including:\n- Your logo and branding\n- Color schemes\n- Card material (PVC, metal, wood)\n- QR code integration\n- Custom shapes and sizes\n\nOur design tool makes it easy, or our team can create a custom design for you!',
                'keywords' => ['customize', 'design', 'custom', 'personalize', 'logo', 'branding', 'colors'],
                'priority' => 82,
            ],
            [
                'question' => 'Is my data secure?',
                'answer' => 'Absolutely! We take security seriously:\n- All data encrypted with SSL/TLS\n- GDPR and CCPA compliant\n- Secure cloud storage\n- No data sharing with third parties\n- You control what information is shared\n\nYour privacy and security are our top priorities.',
                'keywords' => ['security', 'secure', 'safe', 'privacy', 'data', 'protect', 'encryption'],
                'priority' => 87,
            ],
        ];

        foreach ($questions as $questionData) {
            ChatbotQuestion::create($questionData);
        }

        $this->command->info('Chatbot questions seeded successfully!');
    }
}

