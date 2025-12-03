<?php
/**
 * Email Configuration Diagnostic Test
 * 
 * Run this to test if email is actually being sent.
 * Usage: php test_email.php your-test-email@gmail.com
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

// Get email from command line argument
$testEmail = $argv[1] ?? null;

if (!$testEmail) {
    echo "❌ Usage: php test_email.php your-email@gmail.com\n";
    exit(1);
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  Email Configuration Diagnostic Test                       ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Step 1: Check configuration
echo "📋 STEP 1: Mail Configuration\n";
echo "─────────────────────────────────────────────────────────────\n";
echo "Default Mailer: " . config('mail.default') . "\n";
echo "Driver: " . config('mail.mailers.' . config('mail.default') . '.transport') . "\n";

if (config('mail.default') === 'smtp') {
    echo "SMTP Host: " . config('mail.mailers.smtp.host') . "\n";
    echo "SMTP Port: " . config('mail.mailers.smtp.port') . "\n";
    echo "SMTP Username: " . (config('mail.mailers.smtp.username') ?: '❌ NOT SET') . "\n";
    echo "SMTP Password: " . (config('mail.mailers.smtp.password') ? '✅ SET' : '❌ NOT SET') . "\n";
    echo "SMTP Encryption: " . (config('mail.mailers.smtp.encryption') ?: 'none') . "\n";
}

echo "From Address: " . config('mail.from.address') . "\n";
echo "From Name: " . config('mail.from.name') . "\n";
echo "\n";

// Step 2: Check if using placeholder credentials
echo "🔍 STEP 2: Credential Validation\n";
echo "─────────────────────────────────────────────────────────────\n";
$username = config('mail.mailers.smtp.username');
if (str_contains($username ?? '', 'your_mailtrap_username_here')) {
    echo "❌ WARNING: Using placeholder SMTP credentials!\n";
    echo "   You must replace MAIL_USERNAME and MAIL_PASSWORD in .env\n";
    echo "   with real credentials from:\n";
    echo "   - Mailtrap.io (for testing)\n";
    echo "   - Gmail App Password (for production)\n";
    echo "   - SendGrid API Key\n";
    echo "   - Any other SMTP service\n";
    echo "\n";
    exit(1);
}
echo "✅ SMTP credentials appear to be configured\n";
echo "\n";

// Step 3: Test email sending
echo "📧 STEP 3: Sending Test Email\n";
echo "─────────────────────────────────────────────────────────────\n";
echo "Recipient: {$testEmail}\n";
echo "Sending email...\n";

try {
    $sent = false;
    $error = null;
    
    Mail::raw('This is a test email from your NFC Business Card app. If you received this, email sending is working correctly!', function ($message) use ($testEmail, &$sent) {
        $message->to($testEmail)
                ->subject('🧪 Test Email - NFC Business Card App');
        $sent = true;
    });

    if ($sent) {
        echo "✅ Email sent successfully!\n";
        echo "\n";
        echo "📬 Next Steps:\n";
        echo "─────────────────────────────────────────────────────────────\n";
        echo "1. Check {$testEmail} inbox\n";
        echo "2. Check spam/promotions folder\n";
        echo "3. If using Gmail:\n";
        echo "   - Make sure you're using an App Password (not your regular password)\n";
        echo "   - Enable 2FA and create an App Password at:\n";
        echo "     https://myaccount.google.com/apppasswords\n";
        echo "4. Wait a few minutes - delivery can be delayed\n";
        echo "\n";
        
        // Check if using log driver (common issue)
        if (config('mail.default') === 'log') {
            echo "⚠️  WARNING: You're using the 'log' mail driver!\n";
            echo "   Emails are being written to storage/logs/laravel.log\n";
            echo "   instead of being sent to real email addresses.\n";
            echo "   Update .env: MAIL_MAILER=smtp\n";
            echo "\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Failed to send email!\n";
    echo "\n";
    echo "Error Details:\n";
    echo "─────────────────────────────────────────────────────────────\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n";
    echo "Common Fixes:\n";
    echo "─────────────────────────────────────────────────────────────\n";
    echo "1. Invalid credentials - check MAIL_USERNAME and MAIL_PASSWORD\n";
    echo "2. Wrong SMTP host/port - verify with your email provider\n";
    echo "3. Gmail requires App Password (not regular password)\n";
    echo "4. Firewall blocking SMTP port\n";
    echo "5. Rate limiting from email provider\n";
    echo "\n";
    
    exit(1);
}

echo "🎉 Test Complete!\n";
echo "\n";
