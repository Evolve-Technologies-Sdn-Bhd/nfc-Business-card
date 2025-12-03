<?php
/**
 * Payment Cancellation Page
 * 
 * User is redirected here if they cancel payment on Fiuu's page
 */

define('FIUU_INIT', true);
require_once __DIR__ . '/../config/fiuu-config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/payment-security.php';

// Get order ID from query parameter
$orderId = $_GET['orderid'] ?? $_GET['order_id'] ?? '';

// Log cancellation
if ($orderId) {
    logInfo('Payment cancelled by user', [
        'order_id' => $orderId,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ]);
    
    // Update order status to cancelled
    try {
        updateOrderStatus($orderId, 'cancelled', [
            'cancelled_at' => date('Y-m-d H:i:s'),
        ]);
    } catch (Exception $e) {
        logError('Failed to update cancelled order', [
            'order_id' => $orderId,
            'error' => $e->getMessage(),
        ]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled - NFC Business Card</title>
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
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        .icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 24px;
            border-radius: 50%;
            background: #fff3cd;
            color: #856404;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
        }
        h1 {
            font-size: 28px;
            margin-bottom: 16px;
            color: #333;
        }
        p {
            color: #6c757d;
            margin-bottom: 16px;
            line-height: 1.6;
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
        .button-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            margin-left: 12px;
        }
        .button-secondary:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">⚠</div>
        
        <h1>Payment Cancelled</h1>
        <p>Your payment was cancelled. No charges have been made to your account.</p>
        <?php if ($orderId): ?>
            <p style="font-size: 14px; color: #999;">Order ID: <?php echo htmlspecialchars($orderId); ?></p>
        <?php endif; ?>
        
        <div style="margin-top: 32px;">
            <a href="<?php echo PRICING_URL; ?>" class="button">
                Try Again
            </a>
            <a href="<?php echo DASHBOARD_URL; ?>" class="button button-secondary">
                Back to Dashboard
            </a>
        </div>
        
        <p style="margin-top: 24px; font-size: 14px;">
            Need help? <a href="mailto:support@example.com" style="color: #667eea;">Contact Support</a>
        </p>
    </div>
</body>
</html>
