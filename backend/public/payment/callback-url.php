<?php
/**
 * Callback URL - Delayed Payment Notification
 * 
 * This endpoint handles delayed payment status updates.
 * Used for cash payment channels (7-Eleven, etc.) where payment
 * is completed after initial transaction creation.
 * 
 * Similar to notification-url.php but for delayed scenarios.
 */

define('FIUU_INIT', true);
require_once __DIR__ . '/../config/fiuu-config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/payment-security.php';

// Disable output buffering
if (ob_get_level()) {
    ob_end_clean();
}

// Get parameters from Fiuu
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

// Log the callback
logInfo('Callback URL - Delayed notification received', [
    'order_id' => $orderId,
    'status' => $status,
    'amount' => $amount,
    'tran_id' => $tranID,
    'channel' => $channel,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
]);

// Validate required parameters
if (empty($orderId) || empty($status) || empty($skey) || empty($amount)) {
    logError('Callback URL - Missing required parameters', [
        'order_id' => $orderId,
        'status' => $status,
    ]);
    http_response_code(400);
    echo "CBTOKEN:MPSUNK";
    exit;
}

// Verify signature
if (!verifySkey($amount, $orderId, $skey)) {
    logError('Callback URL - Signature verification FAILED', [
        'order_id' => $orderId,
        'ip' => $_SERVER['REMOTE_ADDR'],
    ]);
    http_response_code(403);
    echo "CBTOKEN:MPSUNK";
    exit;
}

logInfo('Callback URL - Signature verified', ['order_id' => $orderId]);

// Check for duplicate
if (checkDuplicateWebhook($orderId, $tranID, $status)) {
    logWarning('Callback URL - Duplicate callback (already processed)', [
        'order_id' => $orderId,
        'tran_id' => $tranID,
    ]);
    echo "CBTOKEN:MPSTATOK";
    exit;
}

try {
    $db = getDbConnection();
    $db->beginTransaction();
    
    // Get existing order
    $order = getOrder($orderId);
    
    if (!$order) {
        logError('Callback URL - Order not found', ['order_id' => $orderId]);
        $db->rollBack();
        echo "CBTOKEN:MPSUNK";
        exit;
    }
    
    // Map status
    $systemStatus = mapCallbackStatus($status);
    
    logInfo('Callback URL - Updating order status', [
        'order_id' => $orderId,
        'old_status' => $order['status'],
        'new_status' => $systemStatus,
        'channel' => $channel,
    ]);
    
    // Update order
    $updateSuccess = updateOrderStatus($orderId, $systemStatus, [
        'fiuu_tran_id' => $tranID,
        'fiuu_appcode' => $appcode,
        'fiuu_channel' => $channel,
        'fiuu_paydate' => $paydate,
        'fiuu_status_code' => $status,
        'payment_completed_at' => ($systemStatus === 'completed') ? date('Y-m-d H:i:s') : null,
        'error_code' => $errorCode,
        'error_message' => $errorDesc,
        'callback_received_at' => date('Y-m-d H:i:s'),
    ]);
    
    if (!$updateSuccess) {
        logError('Callback URL - Failed to update order', ['order_id' => $orderId]);
        $db->rollBack();
        echo "CBTOKEN:MPSUNK";
        exit;
    }
    
    // Insert transaction record
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
        'nbcb' => 'callback',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'raw_response' => json_encode(array_merge($_POST, $_GET)),
    ];
    
    insertTransaction($transactionData);
    
    // Log activity
    logPaymentActivity($orderId, 'callback_received', [
        'status' => $systemStatus,
        'tran_id' => $tranID,
        'channel' => $channel,
    ]);
    
    // Commit
    $db->commit();
    
    // Acknowledge
    echo "CBTOKEN:MPSTATOK";
    
    logInfo('Callback URL - Callback processed successfully', [
        'order_id' => $orderId,
        'system_status' => $systemStatus,
    ]);
    
    exit;
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    logError('Callback URL - Exception occurred', [
        'order_id' => $orderId,
        'error' => $e->getMessage(),
    ]);
    
    http_response_code(500);
    echo "CBTOKEN:MPSUNK";
    exit;
}

/**
 * Map Fiuu status codes to system status
 */
function mapCallbackStatus($fiuuStatus) {
    $statusMap = [
        '00' => 'completed',
        '11' => 'failed',
        '22' => 'pending',
        '33' => 'processing',
    ];
    
    return $statusMap[$fiuuStatus] ?? 'failed';
}
