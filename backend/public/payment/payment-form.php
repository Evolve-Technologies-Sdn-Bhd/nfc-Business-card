<?php
/**
 * Payment Form - Initiates Payment with Fiuu
 * 
 * This file:
 * 1. Creates new order in database
 * 2. Generates vcode for security
 * 3. Redirects customer to Fiuu payment gateway
 */

define('FIUU_INIT', true);
require_once __DIR__ . '/../config/fiuu-config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/payment-security.php';

// Start session
session_name(SESSION_NAME);
session_start();

// Get payment data from POST or SESSION
$userId = $_POST['user_id'] ?? $_SESSION['user_id'] ?? null;
$amount = $_POST['amount'] ?? $_GET['amount'] ?? null;
$planName = $_POST['plan_name'] ?? $_GET['plan'] ?? 'Basic Plan';
$channel = $_POST['channel'] ?? 'credit';
$billName = $_POST['bill_name'] ?? $_SESSION['user_name'] ?? '';
$billEmail = $_POST['bill_email'] ?? $_SESSION['user_email'] ?? '';
$billMobile = $_POST['bill_mobile'] ?? $_SESSION['user_phone'] ?? '';
$billDesc = $_POST['bill_desc'] ?? DEFAULT_PAYMENT_DESCRIPTION;

// Validate required data
if (!$userId || !$amount) {
    die('Error: Missing required payment information. Please go back and try again.');
}

// Validate amount
if (!validateAmount($amount)) {
    die('Error: Invalid payment amount.');
}

// Validate channel
if (!isValidPaymentChannel($channel)) {
    $channel = 'credit'; // Default to credit card
}

try {
    // Create order in database with all required fields
    $orderData = [
        'user_id' => $userId,
        'amount' => $amount,
        'plan_name' => $planName,
        'channel' => $channel,
        'email' => $billEmail,
        'phone' => $billMobile,
        'name' => $billName,
    ];
    
    $orderId = createOrder($orderData);
    
    if (!$orderId) {
        throw new Exception('Failed to create order. Please try again.');
    }
    
    // Log payment initiation
    logPaymentActivity($orderId, 'payment_initiated', [
        'user_id' => $userId,
        'amount' => $amount,
        'plan' => $planName,
        'channel' => $channel,
    ], 'info');
    
    // Format amount (2 decimal places required)
    $formattedAmount = number_format($amount, 2, '.', '');
    
    // Generate vcode for security
    $vcode = generateVcode($formattedAmount, $orderId);
    
    logInfo("Payment form generated for order $orderId", [
        'amount' => $formattedAmount,
        'channel' => $channel,
        'vcode' => $vcode,
    ]);
    
} catch (Exception $e) {
    logError('Payment form error: ' . $e->getMessage());
    die('Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment - NFC Business Card</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            border-radius: 50%;
            background: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
        }
        h1 {
            font-size: 28px;
            margin-bottom: 16px;
            color: #333;
        }
        p {
            color: #6c757d;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        .amount {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
            margin: 20px 0;
        }
        .details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
            text-align: left;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child { border-bottom: none; }
        .label { color: #6c757d; font-size: 14px; }
        .value { color: #333; font-weight: 500; font-size: 14px; }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .info {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 12px;
            margin: 20px 0;
            text-align: left;
            font-size: 14px;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">💳</div>
        <h1>Processing Payment</h1>
        <p>You will be redirected to Fiuu payment gateway</p>
        
        <div class="amount">MYR <?php echo $formattedAmount; ?></div>
        
        <div class="details">
            <div class="detail-row">
                <span class="label">Order ID</span>
                <span class="value"><?php echo htmlspecialchars($orderId); ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Plan</span>
                <span class="value"><?php echo htmlspecialchars($planName); ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Payment Method</span>
                <span class="value"><?php echo htmlspecialchars(FIUU_PAYMENT_CHANNELS[$channel] ?? 'Credit Card'); ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Merchant</span>
                <span class="value">Fiuu (<?php echo isSandbox() ? 'Sandbox' : 'Live'; ?>)</span>
            </div>
        </div>
        
        <div class="info">
            🔒 Your payment is secured by Fiuu payment gateway. You will be redirected automatically.
        </div>
        
        <div class="spinner"></div>
        <p style="margin-top: 20px; font-size: 14px;">Please wait... Redirecting to payment gateway</p>
    </div>
    
    <!-- Hidden form that will auto-submit to Fiuu -->
    <form id="fiuuForm" method="POST" action="<?php echo FIUU_PAYMENT_URL; ?>" style="display:none;">
        <input type="hidden" name="merchant_id" value="<?php echo FIUU_MERCHANT_ID; ?>">
        <input type="hidden" name="amount" value="<?php echo $formattedAmount; ?>">
        <input type="hidden" name="orderid" value="<?php echo $orderId; ?>">
        <input type="hidden" name="bill_name" value="<?php echo htmlspecialchars($billName); ?>">
        <input type="hidden" name="bill_email" value="<?php echo htmlspecialchars($billEmail); ?>">
        <input type="hidden" name="bill_mobile" value="<?php echo htmlspecialchars($billMobile); ?>">
        <input type="hidden" name="bill_desc" value="<?php echo htmlspecialchars($billDesc); ?>">
        <input type="hidden" name="country" value="<?php echo FIUU_COUNTRY; ?>">
        <input type="hidden" name="currency" value="<?php echo FIUU_CURRENCY; ?>">
        <input type="hidden" name="returnurl" value="<?php echo FIUU_RETURN_URL; ?>">
        <input type="hidden" name="callbackurl" value="<?php echo FIUU_CALLBACK_URL; ?>">
        <input type="hidden" name="notifyurl" value="<?php echo FIUU_NOTIFICATION_URL; ?>">
        <input type="hidden" name="channel" value="<?php echo $channel; ?>">
        <input type="hidden" name="vcode" value="<?php echo $vcode; ?>">
    </form>
    
    <script>
        // Auto-submit form after 2 seconds
        setTimeout(function() {
            document.getElementById('fiuuForm').submit();
        }, 2000);
    </script>
</body>
</html>
