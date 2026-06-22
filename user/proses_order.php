<?php
header('Content-Type: application/json');
session_start();

require_once '../db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Silakan login terlebih dahulu untuk melakukan pemesanan.'
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

// Get POST Data
$raw_input = file_get_contents('php://input');
$input = json_decode($raw_input, true);

if (!$input) {
    // Fallback if not JSON
    $input = $_POST;
}

$mitra_id = intval($input['mitra_id'] ?? 0);
$layanan = trim($input['layanan'] ?? '');
$qty = floatval($input['qty'] ?? 0);
$tarif_per_kg = intval($input['tarif_per_kg'] ?? 0);
$biaya_antar_jemput = intval($input['biaya_antar_jemput'] ?? 1500);

if ($mitra_id <= 0 || empty($layanan) || $qty <= 0 || $tarif_per_kg <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Parameter pemesanan tidak lengkap atau tidak valid.'
    ]);
    exit();
}

// Fetch user data from database for customer details
try {
    $user_stmt = $pdo->prepare("SELECT nama, email, no_telp FROM users WHERE id = ?");
    $user_stmt->execute([$_SESSION['user_id']]);
    $user = $user_stmt->fetch();
    
    if (!$user) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Pengguna tidak ditemukan di database.'
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

// Calculate Total Price
$harga_layanan = round($qty * $tarif_per_kg);
$total_harga = $harga_layanan + $biaya_antar_jemput;

// Save initial transaction to database (Status: pending)
try {
    $order_stmt = $pdo->prepare("
        INSERT INTO orders (mitra_id, nama_pelanggan, layanan, berat_atau_qty, tarif_per_kg, biaya_antar_jemput, total_harga, status_pembayaran, status_transfer)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'Proses')
    ");
    $order_stmt->execute([
        $mitra_id,
        $user['nama'],
        $layanan,
        $qty,
        $tarif_per_kg,
        $biaya_antar_jemput,
        $total_harga
    ]);
    
    $order_id = $pdo->lastInsertId();
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
    ]);
    exit();
}

// Generate unique order ID for Midtrans (MW-[database-id]-[timestamp])
$midtrans_order_id = 'MW-' . $order_id . '-' . time();

// Prepare Midtrans Payload
$payload = [
    'transaction_details' => [
        'order_id' => $midtrans_order_id,
        'gross_amount' => $total_harga
    ],
    'item_details' => [
        [
            'id' => 'SVC-' . substr(md5($layanan), 0, 5),
            'price' => $tarif_per_kg,
            'quantity' => $qty,
            'name' => substr($layanan, 0, 50)
        ],
        [
            'id' => 'SHIPPING-FLAT',
            'price' => $biaya_antar_jemput,
            'quantity' => 1,
            'name' => 'Biaya Antar-Jemput'
        ]
    ],
    'customer_details' => [
        'first_name' => $user['nama'],
        'email' => $user['email'],
        'phone' => $user['no_telp'] ?? ''
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
    // Return token and redirect URL
    echo json_encode([
        'status' => 'success',
        'token' => $result['token'],
        'redirect_url' => $result['redirect_url'],
        'order_id' => $order_id
    ]);
} else {
    // Log error or return details
    $error_msg = $result['error_messages'][0] ?? 'Gagal membuat invoice pembayaran.';
    echo json_encode([
        'status' => 'error',
        'message' => 'Midtrans Error: ' . $error_msg,
        'debug_http_code' => $http_code
    ]);
}
