<?php
/**
 * Payment Security Functions
 * 
 * Handles vcode generation, skey verification, input sanitization,
 * validation, and anti-duplicate checks for Fiuu payments.
 */

// Prevent direct access
if (!defined('FIUU_INIT')) {
    die('Direct access not permitted');
}

/**
 * Generate vcode for payment request
 * Formula: MD5(amount + merchant_id + order_id + verify_key)
 */
function generateVcode($amount, $orderId) {
    // Ensure amount is formatted with 2 decimal places
    $formattedAmount = number_format($amount, 2, '.', '');
    
    // Concatenate in correct order
    $string = $formattedAmount . FIUU_MERCHANT_ID . $orderId . FIUU_VERIFY_KEY;
    
    // Generate MD5 hash
    $vcode = md5($string);
    
    logDebug("Vcode generated for order $orderId: amount=$formattedAmount, string=$string, vcode=$vcode");
    
    return $vcode;
}

/**
 * Verify skey from Fiuu webhook response
 * Formula: MD5(amount + merchant_id + order_id + verify_key)
 */
function verifySkey($amount, $orderId, $receivedSkey) {
    // Ensure amount is formatted with 2 decimal places
    $formattedAmount = number_format(floatval($amount), 2, '.', '');
    
    // Generate expected skey
    $string = $formattedAmount . FIUU_MERCHANT_ID . $orderId . FIUU_VERIFY_KEY;
    $expectedSkey = md5($string);
    
    $isValid = ($expectedSkey === $receivedSkey);
    
    if (!$isValid) {
        logWarning("Skey verification FAILED for order $orderId", [
            'expected' => $expectedSkey,
            'received' => $receivedSkey,
            'amount' => $formattedAmount,
            'order_id' => $orderId,
            'string' => $string,
        ]);
    } else {
        logDebug("Skey verified successfully for order $orderId");
    }
    
    return $isValid;
}

/**
 * Sanitize input to prevent XSS
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 */
function validateEmail($email) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Malaysian format)
 */
function validatePhone($phone) {
    // Remove spaces, dashes, and country code
    $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
    $phone = preg_replace('/^\+?60/', '', $phone);
    
    // Check if it's 9-11 digits starting with 1, 3, 4, 6, 7, 8, or 9
    return preg_match('/^[13-9]\d{7,10}$/', $phone);
}

/**
 * Validate amount (must be positive number)
 */
function validateAmount($amount) {
    $amount = floatval($amount);
    return ($amount > 0 && $amount < 1000000);
}

/**
 * Validate order ID format
 */
function validateOrderId($orderId) {
    // Check if matches expected format
    return preg_match('/^' . ORDER_PREFIX . '[A-Z0-9]{' . ORDER_ID_LENGTH . '}$/', $orderId);
}

/**
 * Anti-duplicate webhook check
 * Prevents same webhook from being processed multiple times
 */
function checkDuplicateWebhook($orderId, $tranId, $status) {
    if (!ENABLE_DUPLICATE_CHECK) {
        return false; // Duplicate check disabled
    }
    
    // Check if transaction already exists in database with same status
    $isDuplicate = isTransactionProcessed($orderId, $tranId, $status);
    
    if ($isDuplicate) {
        logWarning("Duplicate webhook detected", [
            'order_id' => $orderId,
            'tran_id' => $tranId,
            'status' => $status,
        ]);
    }
    
    return $isDuplicate;
}

/**
 * Verify webhook IP address (if whitelist enabled)
 */
function verifyWebhookIP() {
    if (!ENABLE_IP_WHITELIST) {
        return true; // IP verification disabled
    }
    
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
    
    foreach (FIUU_ALLOWED_IPS as $allowedIP) {
        // Check if IP matches or is in CIDR range
        if (ipInRange($clientIP, $allowedIP)) {
            logDebug("IP verified: $clientIP");
            return true;
        }
    }
    
    logWarning("Unauthorized IP attempted webhook access", [
        'ip' => $clientIP,
        'allowed_ips' => FIUU_ALLOWED_IPS,
    ]);
    
    return false;
}

/**
 * Check if IP is in CIDR range
 */
function ipInRange($ip, $range) {
    if (strpos($range, '/') === false) {
        // No CIDR, direct comparison
        return $ip === $range;
    }
    
    list($subnet, $mask) = explode('/', $range);
    
    $ip_long = ip2long($ip);
    $subnet_long = ip2long($subnet);
    $mask_long = -1 << (32 - $mask);
    
    return ($ip_long & $mask_long) === ($subnet_long & $mask_long);
}

/**
 * Generate secure token for CSRF protection
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Validate Fiuu status code
 */
function isValidFiuuStatus($status) {
    return in_array($status, ['00', '11', '22', '33']);
}

/**
 * Validate payment channel
 */
function isValidPaymentChannel($channel) {
    return array_key_exists($channel, FIUU_PAYMENT_CHANNELS);
}

/**
 * Sanitize and validate all POST data from webhook
 */
function validateWebhookData($data) {
    $errors = [];
    
    // Required fields
    $required = ['amount', 'orderid', 'status', 'skey'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $errors[] = "Missing required field: $field";
        }
    }
    
    if (!empty($errors)) {
        return ['valid' => false, 'errors' => $errors];
    }
    
    // Validate amount
    if (!validateAmount($data['amount'])) {
        $errors[] = "Invalid amount: " . $data['amount'];
    }
    
    // Validate status code
    if (!isValidFiuuStatus($data['status'])) {
        $errors[] = "Invalid status code: " . $data['status'];
    }
    
    // Verify skey
    if (!verifySkey($data['amount'], $data['orderid'], $data['skey'])) {
        $errors[] = "Invalid skey signature";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
    ];
}

/**
 * Log functions with different severity levels
 */
function logDebug($message, $context = []) {
    if (LOG_LEVEL === 'DEBUG' && LOG_ENABLED) {
        writeLog('DEBUG', $message, $context);
    }
}

function logInfo($message, $context = []) {
    if (in_array(LOG_LEVEL, ['DEBUG', 'INFO']) && LOG_ENABLED) {
        writeLog('INFO', $message, $context);
    }
}

function logWarning($message, $context = []) {
    if (in_array(LOG_LEVEL, ['DEBUG', 'INFO', 'WARNING']) && LOG_ENABLED) {
        writeLog('WARNING', $message, $context);
    }
}

function logError($message, $context = []) {
    if (LOG_ENABLED) {
        writeLog('ERROR', $message, $context);
    }
}

/**
 * Write log to file
 */
function writeLog($level, $message, $context = []) {
    $logFile = getLogFilePath();
    
    // Create log directory if it doesn't exist
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Format log entry
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
    $logEntry = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
    
    // Write to file
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Clean old log files
 */
function cleanupLogFiles() {
    $logDir = LOG_DIR;
    
    if (!is_dir($logDir)) {
        return;
    }
    
    $files = glob($logDir . LOG_FILE_PREFIX . '*.log');
    $cutoffDate = strtotime('-' . LOG_RETENTION_DAYS . ' days');
    
    foreach ($files as $file) {
        if (filemtime($file) < $cutoffDate) {
            unlink($file);
            logInfo("Deleted old log file: " . basename($file));
        }
    }
}
