#!/usr/bin/env php
<?php
/**
 * Fiuu Webhook Endpoint Test Script
 * 
 * This script tests that your Fiuu webhook endpoints are accessible
 * and responding correctly.
 * 
 * Usage:
 *   php test_fiuu_webhooks.php
 */

echo "\n🔍 Fiuu Webhook Endpoint Test\n";
echo "===============================\n\n";

// Load environment configuration
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    echo "❌ .env file not found. Please create it first.\n";
    exit(1);
}

// Parse .env file
$env = parse_ini_file($envFile);
$appUrl = $env['APP_URL'] ?? 'http://localhost:8000';

echo "Testing endpoints at: $appUrl\n\n";

// Define endpoints to test
$endpoints = [
    'Return URL' => $appUrl . '/payment/return-url.php',
    'Notification URL' => $appUrl . '/payment/notification-url.php',
    'Callback URL' => $appUrl . '/payment/callback-url.php',
];

$allPassed = true;

foreach ($endpoints as $name => $url) {
    echo "Testing $name...\n";
    echo "  URL: $url\n";
    
    // Check if URL is accessible
    $headers = @get_headers($url);
    
    if ($headers === false) {
        echo "  ❌ FAILED: Cannot connect to endpoint\n";
        echo "     Make sure your server is running and accessible\n\n";
        $allPassed = false;
        continue;
    }
    
    // Extract status code
    preg_match('/HTTP\/\d\.\d\s+(\d+)/', $headers[0], $matches);
    $statusCode = $matches[1] ?? 'unknown';
    
    if ($statusCode == '200') {
        echo "  ✅ PASSED: Endpoint is accessible (HTTP $statusCode)\n";
    } elseif ($statusCode == '500') {
        echo "  ⚠️  WARNING: Server error (HTTP $statusCode)\n";
        echo "     Endpoint exists but may have configuration issues\n";
        $allPassed = false;
    } else {
        echo "  ❌ FAILED: Unexpected response (HTTP $statusCode)\n";
        $allPassed = false;
    }
    
    echo "\n";
}

// Summary
echo "===============================\n";
if ($allPassed) {
    echo "✅ All webhook endpoints are accessible!\n\n";
    echo "Next steps:\n";
    echo "1. Add your Fiuu credentials to .env\n";
    echo "2. Register these URLs in Fiuu Merchant Dashboard:\n";
    foreach ($endpoints as $name => $url) {
        echo "   - $name: $url\n";
    }
    echo "\n3. Test with a real payment in sandbox mode\n";
} else {
    echo "❌ Some endpoints failed accessibility check\n\n";
    echo "Please verify:\n";
    echo "1. Web server is running\n";
    echo "2. PHP is configured to process .php files\n";
    echo "3. File permissions are correct\n";
    echo "4. Laravel application is properly configured\n";
}

echo "\n";
