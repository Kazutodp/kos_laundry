<?php
header('Content-Type: application/json');
session_start();
require_once '../db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Silakan login terlebih dahulu.'
    ]);
    exit();
}

$order_id = intval($_GET['order_id'] ?? 0);
$user_nama = $_SESSION['user_nama'];

if ($order_id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID Pesanan tidak valid.'
    ]);
    exit();
}

// Fetch order
try {
    $stmt = $pdo->prepare("
        SELECT o.*, u.email, u.no_telp 
        FROM orders o
        JOIN users u ON o.nama_pelanggan = u.nama
        WHERE o.id = ? AND o.nama_pelanggan = ? AND o.status_pembayaran = 'pending' AND o.status_order = 'Menunggu Pembayaran'
    ");
    $stmt->execute([$order_id, $user_nama]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Pesanan tidak ditemukan atau belum ditimbang oleh outlet.'
        ]);
        exit();
    }
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Kesalahan database: ' . $e->getMessage()
    ]);
    exit();
}

// Load Midtrans Configuration
$config_file = '../admin/settings_config.json';
$config = [
    'midtrans_environment' => 'sandbox',
    'midtrans_server_key' => ''
];

if (file_exists($config_file)) {
    $loaded_config = json_decode(file_get_contents($config_file), true);
    if ($loaded_config) {
        $config = array_merge($config, $loaded_config);
    }
}

if (empty($config['midtrans_server_key'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Integrasi pembayaran (Midtrans) belum dikonfigurasi oleh administrator.'
    ]);
    exit();
}

$midtrans_order_id = 'MW-' . $order['id'] . '-' . time();
$total_harga = intval($order['total_harga']);
$tarif_per_kg = intval($order['tarif_per_kg']);
$qty = floatval($order['berat_atau_qty']);
$biaya_antar_jemput = intval($order['biaya_antar_jemput']);

// Prepare Midtrans Payload
$payload = [
    'transaction_details' => [
        'order_id' => $midtrans_order_id,
        'gross_amount' => $total_harga
    ],
    'item_details' => [
        [
            'id' => 'SVC-' . substr(md5($order['layanan']), 0, 5),
            'price' => $tarif_per_kg,
            'quantity' => $qty,
            'name' => substr($order['layanan'], 0, 50)
        ],
        [
            'id' => 'SHIPPING-FLAT',
            'price' => $biaya_antar_jemput,
            'quantity' => 1,
            'name' => 'Biaya Antar-Jemput'
        ]
    ],
    'customer_details' => [
        'first_name' => $user_nama,
        'email' => $order['email'],
        'phone' => $order['no_telp'] ?? ''
    ]
];

// Midtrans Snap API Endpoint
$endpoint = ($config['midtrans_environment'] === 'production')
    ? 'https://app.midtrans.com/snap/v1/transactions'
    : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

// Base64 encode the Server Key with a trailing colon
$auth_header = 'Basic ' . base64_encode($config['midtrans_server_key'] . ':');

// Execute POST Request via cURL
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: ' . $auth_header
]);

// Handle SSL for local dev XAMPP
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Koneksi ke Midtrans gagal: ' . $curl_error
    ]);
    exit();
}

$result = json_decode($response, true);

if ($http_code === 201 && isset($result['token'])) {
    echo json_encode([
        'status' => 'success',
        'token' => $result['token'],
        'redirect_url' => $result['redirect_url']
    ]);
} else {
    $error_msg = $result['error_messages'][0] ?? 'Gagal membuat invoice pembayaran.';
    echo json_encode([
        'status' => 'error',
        'message' => 'Midtrans Error: ' . $error_msg,
        'debug_http_code' => $http_code
    ]);
}
?>
