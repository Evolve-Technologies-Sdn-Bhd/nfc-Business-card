<?php
/**
 * Admin Tool - Check Payment Status
 * 
 * Query Fiuu API to check real-time payment status for an order
 */

define('FIUU_INIT', true);
require_once __DIR__ . '/../config/fiuu-config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/payment-security.php';

// Simple authentication (you should implement proper admin auth)
session_start();
$isAdmin = $_SESSION['is_admin'] ?? false;

// For testing, you can temporarily allow access
// Remove this in production!
$isAdmin = true; // REMOVE THIS IN PRODUCTION

if (!$isAdmin) {
    http_response_code(403);
    die('Access denied. Admin authentication required.');
}

$result = null;
$error = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['order_id'])) {
    $orderId = sanitizeInput($_POST['order_id']);
    
    try {
        // Query Fiuu API for transaction status
        $apiUrl = FIUU_API_ENDPOINT . '/' . FIUU_MERCHANT_ID . '/' . $orderId;
        
        // Generate signature for status check
        // Signature: MD5(merchant_id + orderid + verify_key)
        $signature = md5(FIUU_MERCHANT_ID . $orderId . FIUU_VERIFY_KEY);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Signature: ' . $signature,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception('CURL Error: ' . $curlError);
        }
        
        if ($httpCode !== 200) {
            throw new Exception('API returned HTTP ' . $httpCode);
        }
        
        $result = json_decode($response, true);
        
        // Also get local database record
        $localOrder = getOrder($orderId);
        $result['local_order'] = $localOrder;
        
        logInfo('Admin check-status query', [
            'order_id' => $orderId,
            'admin_user' => $_SESSION['admin_user'] ?? 'unknown',
        ]);
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        logError('Admin check-status error', [
            'order_id' => $orderId,
            'error' => $error,
        ]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Payment Status - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            padding: 40px 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 32px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 8px;
        }
        .subtitle {
            color: #6c757d;
            margin-bottom: 32px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        button {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #5568d3;
        }
        .result {
            margin-top: 32px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #667eea;
        }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        pre {
            background: #fff;
            padding: 16px;
            border-radius: 4px;
            overflow-x: auto;
            margin-top: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-completed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-failed { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Check Payment Status</h1>
        <p class="subtitle">Admin Tool - Query Fiuu API for order status</p>
        
        <form method="POST">
            <div class="form-group">
                <label for="order_id">Order ID</label>
                <input 
                    type="text" 
                    id="order_id" 
                    name="order_id" 
                    placeholder="Enter order ID (e.g., ORD-1234567890)"
                    value="<?php echo htmlspecialchars($_POST['order_id'] ?? ''); ?>"
                    required
                >
            </div>
            <button type="submit">Check Status</button>
        </form>
        
        <?php if ($error): ?>
            <div class="result error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($result): ?>
            <div class="result">
                <h3>Fiuu API Response</h3>
                <pre><?php echo json_encode($result, JSON_PRETTY_PRINT); ?></pre>
                
                <?php if (isset($result['local_order'])): ?>
                    <h3 style="margin-top: 24px;">Local Database Record</h3>
                    <pre><?php echo json_encode($result['local_order'], JSON_PRETTY_PRINT); ?></pre>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
