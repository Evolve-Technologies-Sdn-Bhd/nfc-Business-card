<?php
/**
 * Database Connection and CRUD Functions
 * 
 * Provides secure PDO connection and all database operations
 * for Fiuu payment integration.
 */

// Prevent direct access
if (!defined('FIUU_INIT')) {
    die('Direct access not permitted');
}

/**
 * Get PDO database connection
 */
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            logError('Database connection failed: ' . $e->getMessage());
            die('Database connection error. Please contact support.');
        }
    }
    
    return $pdo;
}

/**
 * Create new order
 */
function createOrder($data) {
    try {
        $pdo = getDbConnection();
        $orderId = generateOrderId();
        
        $stmt = $pdo->prepare("
            INSERT INTO orders (
                order_id, user_id, email, phone, name,
                amount, currency, plan_name, status, created_at
            ) VALUES (
                :order_id, :user_id, :email, :phone, :name,
                :amount, :currency, :plan_name, 'pending', NOW()
            )
        ");
        
        $stmt->execute([
            'order_id' => $orderId,
            'user_id' => $data['user_id'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'name' => $data['name'],
            'amount' => number_format($data['amount'], 2, '.', ''),
            'currency' => $data['currency'] ?? FIUU_CURRENCY,
            'plan_name' => $data['plan_name'],
        ]);
        
        logInfo("Order created: $orderId for {$data['email']}, amount: {$data['amount']}");
        
        return $orderId;
        
    } catch (PDOException $e) {
        logError('Failed to create order: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get order by order ID
 */
function getOrder($orderId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = :order_id LIMIT 1");
        $stmt->execute(['order_id' => $orderId]);
        
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        logError('Failed to get order: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update order status with Fiuu response data
 */
function updateOrderStatus($orderId, $status, $metadata = []) {
    try {
        $pdo = getDbConnection();
        
        $sql = "UPDATE orders SET status = :status, updated_at = NOW()";
        $params = ['order_id' => $orderId, 'status' => $status];
        
        // Update Fiuu transaction fields if provided
        if (!empty($metadata['fiuu_tran_id'])) {
            $sql .= ", fiuu_tran_id = :fiuu_tran_id";
            $params['fiuu_tran_id'] = $metadata['fiuu_tran_id'];
        }
        
        if (!empty($metadata['fiuu_appcode'])) {
            $sql .= ", fiuu_appcode = :fiuu_appcode";
            $params['fiuu_appcode'] = $metadata['fiuu_appcode'];
        }
        
        if (!empty($metadata['fiuu_channel'])) {
            $sql .= ", fiuu_channel = :fiuu_channel";
            $params['fiuu_channel'] = $metadata['fiuu_channel'];
        }
        
        if (!empty($metadata['fiuu_paydate'])) {
            $sql .= ", fiuu_paydate = :fiuu_paydate";
            $params['fiuu_paydate'] = $metadata['fiuu_paydate'];
        }
        
        if (!empty($metadata['fiuu_status_code'])) {
            $sql .= ", fiuu_status_code = :fiuu_status_code";
            $params['fiuu_status_code'] = $metadata['fiuu_status_code'];
        }
        
        if (!empty($metadata['payment_completed_at'])) {
            $sql .= ", payment_completed_at = :payment_completed_at";
            $params['payment_completed_at'] = $metadata['payment_completed_at'];
        }
        
        if (!empty($metadata['callback_received_at'])) {
            $sql .= ", callback_received_at = :callback_received_at";
            $params['callback_received_at'] = $metadata['callback_received_at'];
        }
        
        if (!empty($metadata['cancelled_at'])) {
            $sql .= ", cancelled_at = :cancelled_at";
            $params['cancelled_at'] = $metadata['cancelled_at'];
        }
        
        if (!empty($metadata['error_code'])) {
            $sql .= ", error_code = :error_code";
            $params['error_code'] = $metadata['error_code'];
        }
        
        if (!empty($metadata['error_message'])) {
            $sql .= ", error_message = :error_message";
            $params['error_message'] = $metadata['error_message'];
        }
        
        $sql .= " WHERE order_id = :order_id";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        
        logInfo("Order $orderId status updated to: $status");
        
        return $result;
        
    } catch (PDOException $e) {
        logError('Failed to update order status: ' . $e->getMessage());
        return false;
    }
}

/**
 * Check if transaction already processed (anti-duplicate)
 */
function isTransactionProcessed($orderId, $tranId, $status) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM payment_transactions 
            WHERE order_id = :order_id 
            AND fiuu_tran_id = :tran_id 
            AND status = :status
        ");
        $stmt->execute([
            'order_id' => $orderId,
            'tran_id' => $tranId,
            'status' => $status,
        ]);
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
        
    } catch (PDOException $e) {
        logError('Failed to check duplicate transaction: ' . $e->getMessage());
        return false;
    }
}

/**
 * Insert payment transaction record
 */
function insertTransaction($data) {
    try {
        $pdo = getDbConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO payment_transactions (
                order_id, fiuu_tran_id, amount, currency, status,
                system_status, channel, appcode, paydate,
                error_code, error_message, nbcb, ip_address, raw_response, created_at
            ) VALUES (
                :order_id, :tran_id, :amount, :currency, :status,
                :system_status, :channel, :appcode, :paydate,
                :error_code, :error_message, :nbcb, :ip, :raw_response, NOW()
            )
        ");
        
        $result = $stmt->execute([
            'order_id' => $data['order_id'] ?? '',
            'tran_id' => $data['fiuu_tran_id'] ?? null,
            'amount' => $data['amount'] ?? 0,
            'currency' => $data['currency'] ?? FIUU_CURRENCY,
            'status' => $data['status'] ?? '',
            'system_status' => $data['system_status'] ?? '',
            'channel' => $data['channel'] ?? null,
            'appcode' => $data['appcode'] ?? null,
            'paydate' => $data['paydate'] ?? null,
            'error_code' => $data['error_code'] ?? null,
            'error_message' => $data['error_message'] ?? null,
            'nbcb' => $data['nbcb'] ?? null,
            'ip' => $data['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
            'raw_response' => $data['raw_response'] ?? json_encode($data),
        ]);
        
        logInfo("Transaction record inserted for order: " . ($data['orderid'] ?? 'unknown'));
        
        return $result;
        
    } catch (PDOException $e) {
        logError('Failed to insert transaction: ' . $e->getMessage());
        return false;
    }
}

/**
 * Log payment activity
 */
function logPaymentActivity($orderId, $eventType, $details = [], $level = 'info') {
    try {
        $pdo = getDbConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO payment_logs (
                order_id, event_type, level, message, context, ip_address, created_at
            ) VALUES (
                :order_id, :event_type, :level, :message, :context, :ip, NOW()
            )
        ");
        
        $message = is_string($details) ? $details : ($details['message'] ?? $eventType);
        $context = is_array($details) ? json_encode($details) : null;
        
        $stmt->execute([
            'order_id' => $orderId,
            'event_type' => $eventType,
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ]);
        
        return true;
        
    } catch (PDOException $e) {
        error_log('Failed to log payment activity: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get user by ID (assuming users table exists)
 */
function getUserById($userId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $userId]);
        
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        logError('Failed to get user: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get recent orders for a user
 */
function getUserOrders($userId, $limit = 10) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM orders 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        logError('Failed to get user orders: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get all pending orders (for cleanup/monitoring)
 */
function getPendingOrders($olderThan = 3600) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM orders 
            WHERE status = 'pending' 
            AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(created_at) > :older_than
            ORDER BY created_at DESC
        ");
        $stmt->execute(['older_than' => $olderThan]);
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        logError('Failed to get pending orders: ' . $e->getMessage());
        return [];
    }
}

/**
 * Clean up old logs (run periodically)
 */
function cleanupOldLogs($daysToKeep = 30) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            DELETE FROM payment_logs 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
        ");
        $stmt->execute(['days' => $daysToKeep]);
        
        $deleted = $stmt->rowCount();
        logInfo("Cleaned up $deleted old payment logs");
        
        return $deleted;
        
    } catch (PDOException $e) {
        logError('Failed to cleanup logs: ' . $e->getMessage());
        return 0;
    }
}
