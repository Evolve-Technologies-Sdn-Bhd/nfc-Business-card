<?php
/**
 * Return URL - Customer-Facing Redirect Page
 * 
 * This endpoint displays payment result to the customer.
 * IMPORTANT: This does NOT update the database!
 * Database updates happen in notification-url.php
 */

define('FIUU_INIT', true);
require_once __DIR__ . '/../config/fiuu-config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/payment-security.php';

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

// Log the return request
logInfo('Return URL - Customer redirected back', [
    'order_id' => $orderId,
    'status' => $status,
    'amount' => $amount,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
]);

// Verify signature
$signatureValid = false;
if ($skey && $amount && $orderId) {
    $signatureValid = verifySkey($amount, $orderId, $skey);
}

// Get order details from database
$order = getOrder($orderId);

// Map Fiuu status to display status
$statusText = 'Unknown';
$statusClass = 'unknown';
$statusIcon = '?';

switch ($status) {
    case '00':
        $statusText = 'Success';
        $statusClass = 'success';
        $statusIcon = '✓';
        break;
    case '11':
        $statusText = 'Failed';
        $statusClass = 'failed';
        $statusIcon = '✗';
        break;
    case '22':
        $statusText = 'Pending';
        $statusClass = 'pending';
        $statusIcon = '⏱';
        break;
    case '33':
        $statusText = 'Processing';
        $statusClass = 'processing';
        $statusIcon = '🔄';
        break;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment <?php echo $statusText; ?> - NFC Business Card</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        .icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
        }
        .success { background: #d4edda; color: #28a745; }
        .failed { background: #f8d7da; color: #dc3545; }
        .pending { background: #fff3cd; color: #ffc107; }
        .processing { background: #d1ecf1; color: #0c5460; }
        .unknown { background: #e2e3e5; color: #6c757d; }
        
        h1 {
            font-size: 32px;
            margin-bottom: 16px;
            color: #333;
        }
        .subtitle {
            color: #6c757d;
            margin-bottom: 24px;
            font-size: 16px;
            line-height: 1.6;
        }
        .amount {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
            margin: 24px 0;
        }
        .details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 24px;
            margin: 32px 0;
            text-align: left;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child { border-bottom: none; }
        .label {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
        }
        .value {
            color: #333;
            font-weight: 600;
            font-size: 14px;
            text-align: right;
        }
        .button {
            display: inline-block;
            padding: 16px 40px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin-top: 24px;
            transition: all 0.3s;
        }
        .button:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .error-box {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
            padding: 16px;
            border-radius: 6px;
            margin: 24px 0;
            text-align: left;
        }
        .success-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
            padding: 16px;
            border-radius: 6px;
            margin: 24px 0;
            text-align: left;
        }
        .pending-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
            padding: 16px;
            border-radius: 6px;
            margin: 24px 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon <?php echo $statusClass; ?>">
            <?php echo $statusIcon; ?>
        </div>
        
        <h1>Payment <?php echo $statusText; ?></h1>
        
        <?php if ($status === '00'): ?>
            <p class="subtitle">Your payment has been processed successfully! Thank you for your purchase.</p>
            <div class="success-box">
                <strong>✓ Payment Confirmed</strong><br>
                Your account will be updated shortly. You will receive a confirmation email.
            </div>
        <?php elseif ($status === '22'): ?>
            <p class="subtitle">Your payment is being processed.</p>
            <div class="pending-box">
                <strong>⏱ Payment Pending</strong><br>
                <?php if (strpos($channel, 'cash') !== false): ?>
                    Please complete your payment at the selected payment location within 72 hours.
                <?php else: ?>
                    You will receive confirmation once your payment is completed.
                <?php endif; ?>
            </div>
        <?php elseif ($status === '11'): ?>
            <p class="subtitle">We couldn't process your payment.</p>
            <?php if ($errorDesc): ?>
                <div class="error-box">
                    <strong>Error:</strong> <?php echo htmlspecialchars($errorDesc); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="amount"><?php echo strtoupper($currency ?: 'MYR'); ?> <?php echo number_format($amount, 2); ?></div>
        
        <div class="details">
            <div class="detail-row">
                <span class="label">Order ID</span>
                <span class="value"><?php echo htmlspecialchars($orderId); ?></span>
            </div>
            <?php if ($tranID): ?>
                <div class="detail-row">
                    <span class="label">Transaction ID</span>
                    <span class="value"><?php echo htmlspecialchars($tranID); ?></span>
                </div>
            <?php endif; ?>
            <div class="detail-row">
                <span class="label">Date & Time</span>
                <span class="value"><?php echo date('M d, Y h:i A'); ?></span>
            </div>
            <?php if ($channel): ?>
                <div class="detail-row">
                    <span class="label">Payment Method</span>
                    <span class="value"><?php echo htmlspecialchars($channel); ?></span>
                </div>
            <?php endif; ?>
            <div class="detail-row">
                <span class="label">Status</span>
                <span class="value" style="color: <?php 
                    echo $status === '00' ? '#28a745' : 
                         ($status === '22' ? '#ffc107' : 
                         ($status === '11' ? '#dc3545' : '#6c757d')); 
                ?>">
                    <?php echo $statusText; ?>
                </span>
            </div>
            <?php if ($order): ?>
                <div class="detail-row">
                    <span class="label">Plan</span>
                    <span class="value"><?php echo htmlspecialchars($order['plan_name']); ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!$signatureValid && $skey): ?>
            <div class="error-box">
                <strong>⚠ Warning:</strong> Payment signature could not be verified. Please contact support if you were charged.
            </div>
        <?php endif; ?>
        
        <a href="<?php echo DASHBOARD_URL; ?>" class="button">
            Return to Dashboard
        </a>
        
        <?php if ($status === '11'): ?>
            <br><br>
            <a href="<?php echo PRICING_URL; ?>" style="color: #667eea; text-decoration: none;">
                ← Try Again
            </a>
        <?php endif; ?>
    </div>
</body>
</html>
