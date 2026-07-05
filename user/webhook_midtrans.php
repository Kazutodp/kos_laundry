<?php
header('Content-Type: application/json');

require_once '../db_connect.php';

// Load Midtrans Configuration
$config_file = '../admin/settings_config.json';
$config = [
    'midtrans_server_key' => ''
];

if (file_exists($config_file)) {
    $loaded_config = json_decode(file_get_contents($config_file), true);
    if ($loaded_config) {
        $config = array_merge($config, $loaded_config);
    }
}

$server_key = $config['midtrans_server_key'];

// Get notification body
$raw_input = file_get_contents('php://input');
$notification = json_decode($raw_input, true);

if (!$notification) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid notification data.']);
    exit();
}

$order_id = $notification['order_id'] ?? '';
$status_code = $notification['status_code'] ?? '';
$gross_amount = $notification['gross_amount'] ?? '';
$transaction_status = $notification['transaction_status'] ?? '';
$type = $notification['payment_type'] ?? '';
$fraud_status = $notification['fraud_status'] ?? '';
$signature_key = $notification['signature_key'] ?? '';

if (empty($order_id) || empty($status_code) || empty($gross_amount) || empty($signature_key)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing signature details.']);
    exit();
}

// Verify Signature Key to ensure authenticity
// Signature format: SHA512(order_id + status_code + gross_amount + server_key)
$local_signature = hash('sha512', $order_id . $status_code . $gross_amount . $server_key);

if ($local_signature !== $signature_key) {
    echo json_encode(['status' => 'error', 'message' => 'Signature mismatch. Request is unauthorized.']);
    exit();
}

// Extract database order ID from Midtrans order_id (format: MW-[db_id]-[timestamp])
$parts = explode('-', $order_id);
$db_order_id = intval($parts[1] ?? 0);

if ($db_order_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid order ID mapping.']);
    exit();
}

// Determine Database Status
$status_pembayaran = 'pending';
$status_transfer = 'Proses';

if ($transaction_status === 'capture') {
    if ($type === 'credit_card') {
        if ($fraud_status === 'challenge') {
            $status_pembayaran = 'pending';
        } else {
            $status_pembayaran = 'success';
        }
    }
} elseif ($transaction_status === 'settlement') {
    $status_pembayaran = 'success';
} elseif ($transaction_status === 'pending') {
    $status_pembayaran = 'pending';
} elseif (in_array($transaction_status, ['deny', 'expire', 'cancel'])) {
    $status_pembayaran = 'failed';
}

// Update Database
try {
    if ($status_pembayaran === 'success') {
        // Update payment status, and also transition order status to 'Diproses' if it was waiting for payment
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status_pembayaran = ?, 
                status_order = CASE WHEN status_order = 'Menunggu Pembayaran' THEN 'Diproses' ELSE status_order END
            WHERE id = ?
        ");
        $stmt->execute([$status_pembayaran, $db_order_id]);

        // Trigger WA notification to partner for successful payment
        require_once '../wa_helper.php';
        notify_mitra_new_order($db_order_id, $pdo);
    } else {
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status_pembayaran = ? 
            WHERE id = ?
        ");
        $stmt->execute([$status_pembayaran, $db_order_id]);
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Order status updated to ' . $status_pembayaran
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database update failed: ' . $e->getMessage()
    ]);
}
