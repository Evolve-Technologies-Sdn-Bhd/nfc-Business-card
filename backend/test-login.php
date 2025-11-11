<?php

// Test login credentials
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Available Test Accounts ===\n\n";

echo "ADMIN ACCOUNTS:\n";
$admins = User::where('is_admin', true)->get();
foreach ($admins as $admin) {
    echo "  Email: {$admin->email}\n";
    echo "  Name: {$admin->first_name} {$admin->last_name}\n";
    echo "  Role: {$admin->admin_role}\n";
    echo "  Password: admin123\n\n";
}

echo "REGULAR USERS:\n";
$users = User::where('is_admin', false)->orWhereNull('is_admin')->limit(3)->get();
foreach ($users as $user) {
    echo "  Email: {$user->email}\n";
    echo "  Name: {$user->first_name} {$user->last_name}\n";
    echo "  Password: password (for test users)\n\n";
}

echo "=== Backend API Status ===\n";
echo "API URL: http://localhost:8000/api\n";
echo "Test endpoint: http://localhost:8000/api/test\n";
echo "Login endpoint: http://localhost:8000/api/login\n\n";

echo "=== Frontend URLs ===\n";
echo "Login page: http://localhost:3002/UserAccount/login\n";
echo "Register page: http://localhost:3002/UserAccount/register\n";
