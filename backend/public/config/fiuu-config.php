<?php
/**
 * Fiuu Payment Gateway Configuration
 * 
 * CRITICAL: Keep this file secure! Never commit to public repositories.
 * Store ONLY configuration variables here.
 */

// Prevent direct access
if (!defined('FIUU_INIT')) {
    die('Direct access not permitted');
}

// ============================================
// FIUU MERCHANT CREDENTIALS
// ============================================
define('FIUU_MERCHANT_ID', 'SB_evolvetechnology');
define('FIUU_VERIFY_KEY', '3d94ceb644a497522601465da218ec6b');
define('FIUU_SECRET_KEY', '6f0c6cd63f23fad128b594b36bb9252c');

// ============================================
// ENVIRONMENT SETTINGS
// ============================================
define('FIUU_SANDBOX_MODE', true); // Set to false for production
define('FIUU_CURRENCY', 'MYR');
define('FIUU_COUNTRY', 'MY');

// ============================================
// FIUU API ENDPOINTS
// ============================================
if (FIUU_SANDBOX_MODE) {
    define('FIUU_PAYMENT_URL', 'https://sandbox.merchant.razer.com/RMS/API/chkout/index.php');
    define('FIUU_QUERY_URL', 'https://sandbox.merchant.razer.com/RMS/API/query/q_by_txn_id.php');
    define('FIUU_REFUND_URL', 'https://sandbox.merchant.razer.com/RMS/API/refund/index.php');
} else {
    define('FIUU_PAYMENT_URL', 'https://payment.ipay88.com.my/epayment/entry.asp');
    define('FIUU_QUERY_URL', 'https://www.onlinepayment.com.my/MOLPay/API/query/q_by_txn_id.php');
    define('FIUU_REFUND_URL', 'https://www.onlinepayment.com.my/MOLPay/API/refund/index.php');
}

// ============================================
// WEBHOOK URLs (Update with your domain)
// ============================================
define('BASE_URL', 'http://localhost:8000');
define('FIUU_RETURN_URL', BASE_URL . '/payment/return-url.php');
define('FIUU_NOTIFICATION_URL', BASE_URL . '/payment/notification-url.php');
define('FIUU_CALLBACK_URL', BASE_URL . '/payment/callback-url.php');
define('FIUU_CANCEL_URL', BASE_URL . '/payment/cancel.php');

// ============================================
// DATABASE CONFIGURATION
// ============================================
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'nfc_business_card');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ============================================
// TIMEZONE & LOCALE
// ============================================
define('TIMEZONE', 'Asia/Kuala_Lumpur');
date_default_timezone_set(TIMEZONE);

// ============================================
// LOGGING CONFIGURATION
// ============================================
define('LOG_DIR', __DIR__ . '/../logs/');
define('LOG_ENABLED', true);
define('LOG_LEVEL', 'DEBUG'); // DEBUG, INFO, WARNING, ERROR
define('LOG_FILE_PREFIX', 'fiuu_');
define('LOG_DATE_FORMAT', 'Y-m-d');
define('LOG_RETENTION_DAYS', 30); // Keep logs for 30 days

// ============================================
// SECURITY SETTINGS
// ============================================
define('ENABLE_IP_WHITELIST', false); // Set to true to enable IP filtering
define('FIUU_ALLOWED_IPS', [
    '103.60.177.0/24',    // Fiuu IP range (example)
    '127.0.0.1',          // Localhost for testing
]);

define('ENABLE_DUPLICATE_CHECK', true); // Prevent duplicate webhook processing
define('DUPLICATE_WINDOW_SECONDS', 300); // 5 minutes

// ============================================
// PAYMENT SETTINGS
// ============================================
define('ORDER_PREFIX', 'NFC-');
define('ORDER_ID_LENGTH', 16);
define('DEFAULT_PAYMENT_DESCRIPTION', 'NFC Business Card Payment');

// Payment channels available
define('FIUU_PAYMENT_CHANNELS', [
    'credit' => 'Credit/Debit Card',
    'fpx' => 'FPX Online Banking',
    'fpx_b2b' => 'FPX B2B',
    'tng' => 'Touch n Go eWallet',
    'grabpay' => 'GrabPay',
    'boost' => 'Boost',
    'shopeepay' => 'ShopeePay',
    'maybank_qr' => 'Maybank QRPay',
]);

// ============================================
// EMAIL CONFIGURATION (for notifications)
// ============================================
define('SEND_EMAIL_NOTIFICATIONS', true);
define('ADMIN_EMAIL', 'admin@nfcbusinesscard.com');
define('FROM_EMAIL', 'noreply@nfcbusinesscard.com');
define('FROM_NAME', 'NFC Business Card');

// ============================================
// FRONTEND URLS
// ============================================
define('FRONTEND_URL', 'http://localhost:3000');
define('DASHBOARD_URL', FRONTEND_URL . '/dashboard');
define('PRICING_URL', BASE_URL . '/pricing.php');

// ============================================
// ERROR HANDLING
// ============================================
define('DISPLAY_ERRORS', FIUU_SANDBOX_MODE); // Only show errors in sandbox
define('ERROR_LOG_FILE', LOG_DIR . 'errors.log');

if (DISPLAY_ERRORS) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
ini_set('log_errors', '1');
ini_set('error_log', ERROR_LOG_FILE);

// ============================================
// SESSION CONFIGURATION
// ============================================
define('SESSION_NAME', 'FIUU_PAYMENT_SESSION');
define('SESSION_LIFETIME', 3600); // 1 hour

// ============================================
// FIUU STATUS CODES
// ============================================
define('FIUU_STATUS_CODES', [
    '00' => 'Success',
    '11' => 'Failed',
    '22' => 'Pending',
    '33' => 'Processing',
]);

// Map Fiuu status to internal status
function mapFiuuStatus($fiuuCode) {
    $statusMap = [
        '00' => 'paid',
        '11' => 'failed',
        '22' => 'pending',
        '33' => 'processing',
    ];
    return $statusMap[$fiuuCode] ?? 'unknown';
}

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Get current timestamp in MySQL format
 */
function getCurrentTimestamp() {
    return date('Y-m-d H:i:s');
}

/**
 * Generate unique order ID
 */
function generateOrderId() {
    return ORDER_PREFIX . strtoupper(bin2hex(random_bytes(ORDER_ID_LENGTH / 2)));
}

/**
 * Check if in sandbox mode
 */
function isSandbox() {
    return FIUU_SANDBOX_MODE;
}

/**
 * Get log file path for today
 */
function getLogFilePath() {
    $date = date(LOG_DATE_FORMAT);
    return LOG_DIR . LOG_FILE_PREFIX . $date . '.log';
}
