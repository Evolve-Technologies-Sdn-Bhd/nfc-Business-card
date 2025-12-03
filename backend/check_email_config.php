<?php
/**
 * Quick Email Configuration Status Check
 * Run: php check_email_config.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  Email Configuration Status Check                          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

$driver = config('mail.default');
$status = '✅ OK';
$issues = [];

echo "Current Mail Driver: {$driver}\n";
echo "\n";

// Check 1: Driver type
if ($driver === 'log') {
    $status = '⚠️  WARNING';
    $issues[] = "Using 'log' driver - emails are written to log files, NOT sent to real inboxes";
}

// Check 2: SMTP configuration (if using SMTP)
if ($driver === 'smtp') {
    $host = config('mail.mailers.smtp.host');
    $port = config('mail.mailers.smtp.port');
    $username = config('mail.mailers.smtp.username');
    $password = config('mail.mailers.smtp.password');
    
    echo "SMTP Configuration:\n";
    echo "  Host: {$host}\n";
    echo "  Port: {$port}\n";
    echo "  Username: {$username}\n";
    echo "  Password: " . ($password ? '(set, ' . strlen($password) . ' chars)' : '❌ NOT SET') . "\n";
    echo "\n";
    
    // Check for placeholder values
    if (str_contains($username ?? '', 'your_mailtrap') || str_contains($username ?? '', 'username_here')) {
        $status = '❌ CRITICAL';
        $issues[] = "SMTP username contains placeholder text - email sending will fail";
    }
    
    if (empty($username)) {
        $status = '❌ CRITICAL';
        $issues[] = "SMTP username is empty";
    }
    
    if (empty($password)) {
        $status = '❌ CRITICAL';
        $issues[] = "SMTP password is empty";
    }
    
    if (str_contains($password ?? '', 'your_mailtrap') || str_contains($password ?? '', 'password_here')) {
        $status = '❌ CRITICAL';
        $issues[] = "SMTP password contains placeholder text";
    }
}

// Check 3: From address
$fromAddress = config('mail.from.address');
$fromName = config('mail.from.name');

echo "From Configuration:\n";
echo "  Address: {$fromAddress}\n";
echo "  Name: {$fromName}\n";
echo "\n";

// Final status
echo "════════════════════════════════════════════════════════════\n";
echo "Status: {$status}\n";
echo "════════════════════════════════════════════════════════════\n";
echo "\n";

if (!empty($issues)) {
    echo "Issues Found:\n";
    foreach ($issues as $i => $issue) {
        echo "  " . ($i + 1) . ". {$issue}\n";
    }
    echo "\n";
    echo "📖 See EMAIL_FIX_GUIDE.md for detailed setup instructions\n";
    echo "\n";
    exit(1);
} else {
    echo "✅ Configuration looks good!\n";
    echo "\n";
    echo "Next step: Test actual email sending\n";
    echo "Run: php test_email.php your-email@gmail.com\n";
    echo "\n";
    exit(0);
}
