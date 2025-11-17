<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChatbotQuestion;
use Illuminate\Support\Facades\DB;

class ChatbotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing chatbot data
        DB::table('chatbot_feedback')->truncate();
        DB::table('chatbot_questions')->truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $questions = [
            [
                'question' => 'What is an NFC business card?',
                'answer' => "An NFC business card is a modern digital alternative to traditional paper business cards. It uses Near Field Communication (NFC) technology, allowing you to share your contact information, social media profiles, and portfolio simply by tapping your card to a smartphone.\n\nNo app installation required - it works instantly with any NFC-enabled phone!",
                'keywords' => ['nfc', 'business card', 'digital card', 'what is', 'explain', 'technology'],
                'priority' => 100,
                'is_active' => true,
            ],
            [
                'question' => 'How do I create my digital card?',
                'answer' => "Creating your digital NFC card is easy:\n\n1. Sign up for an account on our platform\n2. Choose a subscription plan (Free, Professional, or Business)\n3. Fill in your contact details and social media links\n4. Customize your digital card design\n5. Order a physical NFC card or use the digital version\n\nYour digital card is instantly ready to share!",
                'keywords' => ['create', 'make', 'setup', 'how to', 'start', 'register', 'sign up'],
                'priority' => 95,
                'is_active' => true,
            ],
            [
                'question' => 'What are your pricing plans?',
                'answer' => "We offer three flexible pricing plans:\n\n📱 FREE Plan: RM0/month\n- 1 digital card\n- Basic customization\n- Unlimited taps/shares\n\n💼 PROFESSIONAL: RM29/month\n- 3 digital cards\n- Advanced customization\n- Analytics dashboard\n- Priority support\n\n🏢 BUSINESS: RM99/month\n- Unlimited cards\n- Team management\n- Advanced analytics\n- Custom branding\n- Dedicated support\n\nAll plans include free updates and 24/7 customer support!",
                'keywords' => ['price', 'pricing', 'cost', 'plan', 'subscription', 'fee', 'how much', 'payment'],
                'priority' => 90,
                'is_active' => true,
            ],
            [
                'question' => 'How do I share my NFC card?',
                'answer' => "There are multiple ways to share your NFC business card:\n\n1. **Tap to Share**: Simply tap your physical NFC card to any smartphone\n2. **QR Code**: Share your unique QR code for non-NFC devices\n3. **Link**: Send your personalized link via email, SMS, or social media\n4. **Digital Wallet**: Add to Apple Wallet or Google Pay\n\nNo special app needed - recipients can view your card instantly in their browser!",
                'keywords' => ['share', 'sharing', 'send', 'tap', 'qr code', 'link', 'how to share'],
                'priority' => 85,
                'is_active' => true,
            ],
            [
                'question' => 'Do I need a physical card?',
                'answer' => "Not necessarily! You can use our service in two ways:\n\n🌐 **Digital Only**: Use just the QR code or link (FREE)\n- Perfect for social media profiles\n- Email signatures\n- Virtual meetings\n\n💳 **Physical + Digital**: Order an NFC-enabled card (from RM50)\n- Professional in-person networking\n- Premium feel\n- Works with any NFC phone\n\nBoth options give you access to the same powerful digital profile!",
                'keywords' => ['physical card', 'nfc card', 'card', 'need card', 'buy card', 'order card'],
                'priority' => 80,
                'is_active' => true,
            ],
            [
                'question' => 'What information can I include on my card?',
                'answer' => "You can include a wide range of information on your digital NFC card:\n\n✅ Contact Details:\n- Name, title, company\n- Phone, email, website\n- Business address\n\n✅ Social Media:\n- LinkedIn, Instagram, Facebook\n- Twitter, TikTok, YouTube\n- WhatsApp, Telegram\n\n✅ Additional Content:\n- Profile photo\n- Company logo\n- Portfolio links\n- Custom buttons\n- Video introduction\n- Documents/PDFs\n\nYou have complete control and can update anytime!",
                'keywords' => ['information', 'include', 'add', 'what can', 'fields', 'content', 'details'],
                'priority' => 75,
                'is_active' => true,
            ],
            [
                'question' => 'Can I update my card information?',
                'answer' => "Yes, absolutely! One of the biggest advantages of NFC business cards is that you can update your information anytime:\n\n✏️ **Instant Updates**:\n- Log in to your dashboard\n- Edit any information\n- Changes reflect immediately\n- No need to reprint cards!\n\n🔄 **Update Frequency**: Unlimited\n⚡ **Update Speed**: Instant (0-2 minutes)\n📱 **Effect**: All shared cards show new info\n\nPerfect for when you change jobs, update your phone number, or add new social media!",
                'keywords' => ['update', 'edit', 'change', 'modify', 'revise', 'can I update'],
                'priority' => 70,
                'is_active' => true,
            ],
            [
                'question' => 'How does NFC technology work?',
                'answer' => "NFC (Near Field Communication) is a secure wireless technology:\n\n🔹 **How It Works**:\n1. Your NFC card contains a tiny chip\n2. When brought close to a phone (within 4cm)\n3. The chip transmits your digital profile link\n4. The phone opens your profile automatically\n\n🔹 **Requirements**:\n- Works with iPhone 7+ and most Android phones\n- No app download needed\n- No batteries required in the card\n- Instant connection (< 1 second)\n\n🔹 **Security**: NFC only works at very close range, making it secure and private.",
                'keywords' => ['nfc', 'technology', 'how it works', 'how does', 'work', 'technical', 'chip'],
                'priority' => 65,
                'is_active' => true,
            ],
            [
                'question' => 'Do I need an app to use NFC cards?',
                'answer' => "No app required! 📱\n\nFor the person **receiving** your card:\n- ✅ No app needed\n- ✅ Works in any web browser\n- ✅ Instant access\n\nFor **you** (card owner):\n- ✅ Manage via web dashboard\n- ✅ Optional mobile app available for quick edits\n- ✅ Works on any device\n\nThis makes sharing super convenient - your contacts don't need to download anything!",
                'keywords' => ['app', 'application', 'download', 'install', 'need app', 'mobile app'],
                'priority' => 60,
                'is_active' => true,
            ],
            [
                'question' => 'How can I track who views my card?',
                'answer' => "Professional and Business plans include powerful analytics:\n\n📊 **Analytics Features**:\n- Total card views/taps\n- Unique visitors\n- Click tracking on links\n- Geographic location (city/country)\n- Device types (iOS/Android)\n- Time and date of interactions\n- Most clicked social media links\n\n📈 **Reports Available**:\n- Daily, weekly, monthly summaries\n- Export to Excel/CSV\n- Real-time dashboard\n\nPerfect for measuring your networking ROI!",
                'keywords' => ['analytics', 'track', 'tracking', 'views', 'statistics', 'stats', 'who viewed', 'insights'],
                'priority' => 55,
                'is_active' => true,
            ],
            [
                'question' => 'Can I have multiple cards?',
                'answer' => "Yes! You can have multiple digital cards depending on your plan:\n\n🆓 **Free Plan**: 1 card\n💼 **Professional**: Up to 3 cards\n🏢 **Business**: Unlimited cards\n\n**Why multiple cards?**\n- Different cards for different contexts (work, personal, side business)\n- Separate cards for different roles\n- Team members can each have their own\n- Different designs for different events\n\nSwitch between cards instantly in your dashboard!",
                'keywords' => ['multiple cards', 'more than one', 'several cards', 'many cards', 'multiple'],
                'priority' => 50,
                'is_active' => true,
            ],
            [
                'question' => 'What if my phone doesn\'t have NFC?',
                'answer' => "No problem! We've got you covered:\n\n📱 **Alternative Sharing Methods**:\n\n1. **QR Code**: \n   - Every card has a unique QR code\n   - Works on ANY smartphone\n   - Just scan and view\n\n2. **Direct Link**:\n   - Share via SMS, email, WhatsApp\n   - Works on any device with internet\n   - Same full profile access\n\n3. **Digital Wallet**:\n   - Add to Apple Wallet / Google Pay\n   - Share from your wallet\n\nAll methods lead to the same beautiful digital profile!",
                'keywords' => ['no nfc', 'without nfc', 'doesn\'t have nfc', 'alternative', 'qr code', 'no chip'],
                'priority' => 45,
                'is_active' => true,
            ],
            [
                'question' => 'Is my data secure?',
                'answer' => "Absolutely! We take security very seriously:\n\n🔒 **Security Measures**:\n- SSL/HTTPS encryption for all data\n- Secure cloud storage (AWS/Azure)\n- Regular security audits\n- GDPR compliant\n- Password protected dashboard\n- 2-Factor authentication available\n- No data sold to third parties\n\n🛡️ **Privacy Controls**:\n- You control what information to display\n- Hide/show fields anytime\n- Delete your account anytime\n- Export your data\n\nYour information is safe with us!",
                'keywords' => ['security', 'secure', 'safe', 'privacy', 'data protection', 'encryption', 'gdpr'],
                'priority' => 40,
                'is_active' => true,
            ],
            [
                'question' => 'How long does shipping take?',
                'answer' => "Physical NFC card delivery times:\n\n🇲🇾 **Malaysia**:\n- Kuala Lumpur: 2-3 business days\n- West Malaysia: 3-5 business days  \n- East Malaysia: 5-7 business days\n\n🌏 **International**:\n- Singapore: 5-7 business days\n- Other countries: 10-14 business days\n\n📦 **Shipping Options**:\n- Standard shipping (included)\n- Express shipping (+RM20)\n- Bulk orders may take longer\n\n🎨 **Custom designs**: Add 2-3 days for printing\n\nTracking number provided for all orders!",
                'keywords' => ['shipping', 'delivery', 'how long', 'when will i receive', 'postage', 'courier'],
                'priority' => 35,
                'is_active' => true,
            ],
            [
                'question' => 'Can I cancel my subscription?',
                'answer' => "Yes, you can cancel anytime with no penalties:\n\n🔄 **Cancellation Process**:\n1. Log in to your dashboard\n2. Go to Subscription Settings\n3. Click \"Cancel Subscription\"\n4. Confirm cancellation\n\n📅 **What Happens**:\n- Access continues until end of billing period\n- No automatic renewal\n- Keep your data for 30 days\n- Can reactivate anytime\n\n💰 **Refund Policy**:\n- No refunds for current month\n- Unused months refunded (annual plans)\n- Physical cards non-refundable\n\n**Need to pause?** Contact us for temporary suspension options!",
                'keywords' => ['cancel', 'cancellation', 'unsubscribe', 'stop subscription', 'refund'],
                'priority' => 30,
                'is_active' => true,
            ],
            [
                'question' => 'How do I contact support?',
                'answer' => "We're here to help! Multiple ways to reach us:\n\n📧 **Email**: support@nfcbusinesscard.com\n- Response time: Within 24 hours\n- For detailed inquiries\n\n💬 **Live Chat**: Available on our website\n- Mon-Fri: 9 AM - 6 PM (GMT+8)\n- Instant responses\n\n📱 **WhatsApp**: +60 12-345-6789\n- Quick questions\n- Technical support\n\n🎫 **Support Ticket**: Via dashboard\n- Track your inquiry\n- Attach screenshots\n\n**Priority Support**: Professional & Business plan members get faster response times!",
                'keywords' => ['contact', 'support', 'help', 'customer service', 'assistance', 'email', 'phone'],
                'priority' => 25,
                'is_active' => true,
            ],
        ];

        foreach ($questions as $question) {
            ChatbotQuestion::create($question);
        }

        $this->command->info('✅ Created ' . count($questions) . ' chatbot questions successfully!');
    }
}
