<?php
/**
 * Notification URL - Server-to-Server Webhook (PRIMARY)
 * 
 * This is the MOST IMPORTANT webhook endpoint!
 * This performs ALL database updates for payment status.
 * 
 * Fiuu sends TWO notifications:
 * 1. Immediately after payment completion
 * 2. Delayed notification (backup/retry mechanism)
 * 
 * CRITICAL: Must return "CBTOKEN:MPSTATOK" to acknowledge receipt
 */

define('FIUU_INIT', true);
require_once __DIR__ . '/../config/fiuu-config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/payment-security.php';

// Disable output buffering for immediate response
if (ob_get_level()) {
    ob_end_clean();
}

// Get parameters from Fiuu (support both POST and GET)
$amount = $_POST['amount'] ?? $_GET['amount'] ?? '';
$orderId = $_POST['orderid'] ?? $_GET['orderid'] ?? '';
$tranID = $_POST['tranID'] ?? $_GET['tranID'] ?? '';
$status = $_POST['status'] ?? $_GET['status'] ?? '';
$domain = $_POST['domain'] ?? $_GET['domain'] ?? '';
$currency = $_POST['currency'] ?? $_GET['currency'] ?? '';
$appcode = $_POST['appcode'] ?? $_GET['appcode'] ?? '';
$paydate = $_POST['paydate'] ?? $_GET['paydate'] ?? '';
$channel = $_POST['channel'] ?? $_GET['channel'] ?? '';
$skey = $_POST['skey'] ?? $_GET['skey'] ?? '';
$errorCode = $_POST['error_code'] ?? $_GET['error_code'] ?? '';
$errorDesc = $_POST['error_desc'] ?? $_GET['error_desc'] ?? '';
$nbcb = $_POST['nbcb'] ?? $_GET['nbcb'] ?? ''; // Number of callback (0 = first, 1+ = retry)

// Log the incoming webhook
logInfo('Notification URL - Webhook received', [
    'order_id' => $orderId,
    'status' => $status,
    'amount' => $amount,
    'tran_id' => $tranID,
    'channel' => $channel,
    'nbcb' => $nbcb,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'all_params' => array_merge($_POST, $_GET),
]);

// Validate required parameters
if (empty($orderId) || empty($status) || empty($skey) || empty($amount)) {
    logError('Notification URL - Missing required parameters', [
        'order_id' => $orderId,
        'status' => $status,
        'has_skey' => !empty($skey),
        'has_amount' => !empty($amount),
    ]);
    http_response_code(400);
    echo "CBTOKEN:MPSUNK"; // Unknown status
    exit;
}

// Verify IP (optional but recommended in production)
if (ENABLE_IP_WHITELIST) {
    if (!verifyWebhookIP($_SERVER['REMOTE_ADDR'])) {
        logError('Notification URL - IP not whitelisted', [
            'ip' => $_SERVER['REMOTE_ADDR'],
            'order_id' => $orderId,
        ]);
        http_response_code(403);
        echo "CBTOKEN:MPSUNK";
        exit;
    }
}

// CRITICAL: Verify signature
if (!verifySkey($amount, $orderId, $skey)) {
    logError('Notification URL - Signature verification FAILED', [
        'order_id' => $orderId,
        'expected_amount' => $amount,
        'received_skey' => $skey,
        'ip' => $_SERVER['REMOTE_ADDR'],
    ]);
    http_response_code(403);
    echo "CBTOKEN:MPSUNK";
    exit;
}

logInfo('Notification URL - Signature verified successfully', ['order_id' => $orderId]);

// Check for duplicate webhook (anti-replay protection)
if (checkDuplicateWebhook($orderId, $tranID, $status)) {
    logWarning('Notification URL - Duplicate webhook detected (already processed)', [
        'order_id' => $orderId,
        'tran_id' => $tranID,
        'status' => $status,
        'nbcb' => $nbcb,
    ]);
    // Still return success to acknowledge
    echo "CBTOKEN:MPSTATOK";
    exit;
}

try {
    $db = getDbConnection();
    $db->beginTransaction();
    
    // Get existing order
    $order = getOrder($orderId);
    
    if (!$order) {
        logError('Notification URL - Order not found', ['order_id' => $orderId]);
        $db->rollBack();
        echo "CBTOKEN:MPSUNK";
        exit;
    }
    
    // Map Fiuu status to our system status
    $systemStatus = mapFiuuStatusCode($status);
    
    logInfo('Notification URL - Processing payment update', [
        'order_id' => $orderId,
        'fiuu_status' => $status,
        'system_status' => $systemStatus,
        'amount' => $amount,
        'tran_id' => $tranID,
    ]);
    
    // Update order status
    $updateSuccess = updateOrderStatus($orderId, $systemStatus, [
        'fiuu_tran_id' => $tranID,
        'fiuu_appcode' => $appcode,
        'fiuu_channel' => $channel,
        'fiuu_paydate' => $paydate,
        'fiuu_status_code' => $status,
        'payment_completed_at' => ($systemStatus === 'completed') ? date('Y-m-d H:i:s') : null,
        'error_code' => $errorCode,
        'error_message' => $errorDesc,
    ]);
    
    if (!$updateSuccess) {
        logError('Notification URL - Failed to update order', ['order_id' => $orderId]);
        $db->rollBack();
        echo "CBTOKEN:MPSUNK";
        exit;
    }
    
    // Insert payment transaction record
    $transactionData = [
        'order_id' => $orderId,
        'fiuu_tran_id' => $tranID,
        'amount' => $amount,
        'currency' => $currency ?: 'MYR',
        'status' => $status,
        'system_status' => $systemStatus,
        'channel' => $channel,
        'appcode' => $appcode,
        'paydate' => $paydate,
        'error_code' => $errorCode,
        'error_message' => $errorDesc,
        'nbcb' => $nbcb,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'raw_response' => json_encode(array_merge($_POST, $_GET)),
    ];
    
    $transactionInserted = insertTransaction($transactionData);
    
    if (!$transactionInserted) {
        logWarning('Notification URL - Transaction insert failed (order updated)', [
            'order_id' => $orderId,
        ]);
    }
    
    // Log payment activity
    logPaymentActivity($orderId, 'webhook_notification', [
        'status' => $systemStatus,
        'tran_id' => $tranID,
        'amount' => $amount,
        'channel' => $channel,
        'nbcb' => $nbcb,
    ]);
    
    // Commit all changes
    $db->commit();
    
    // IMPORTANT: Send success acknowledgment to Fiuu
    echo "CBTOKEN:MPSTATOK";
    
    logInfo('Notification URL - Payment processed successfully', [
        'order_id' => $orderId,
        'system_status' => $systemStatus,
        'tran_id' => $tranID,
        'user_id' => $order['user_id'] ?? null,
    ]);
    
    // Optional: Send email notification to customer (do this AFTER acknowledgment)
    if ($systemStatus === 'completed' && !empty($order['email'])) {
        // TODO: Implement email notification
        // sendPaymentConfirmationEmail($order['email'], $orderId, $amount);
    }
    
    exit;
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    logError('Notification URL - Exception occurred', [
        'order_id' => $orderId,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    
    http_response_code(500);
    echo "CBTOKEN:MPSUNK";
    exit;
}

/**
 * Map Fiuu status codes to system status
 */
function mapFiuuStatusCode($fiuuStatus) {
    $statusMap = [
        '00' => 'completed',      // Success - PAYMENT CONFIRMED
        '11' => 'failed',          // Failed
        '22' => 'pending',         // Pending (cash channels, awaiting payment)
        '33' => 'processing',      // Processing (intermediate state)
    ];
    
    return $statusMap[$fiuuStatus] ?? 'failed';
}
