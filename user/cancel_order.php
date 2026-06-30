<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Anda harus login terlebih dahulu.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak didukung.']);
    exit();
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$username = $_SESSION['username'];

if ($order_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID pesanan tidak valid.']);
    exit();
}

try {
    // Fetch the order to verify owner and eligibility
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND nama_pelanggan = ?");
    $stmt->execute([$order_id, $username]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Pesanan tidak ditemukan atau Anda tidak memiliki akses.']);
        exit();
    }

    $eligible_statuses = ['Menunggu Penjemputan', 'Menunggu Timbangan', 'Menunggu Pembayaran'];
    if (!in_array($order['status_order'], $eligible_statuses) || $order['status_pembayaran'] === 'success') {
        echo json_encode(['status' => 'error', 'message' => 'Pesanan tidak dapat dibatalkan karena sudah diproses atau sudah dibayar.']);
        exit();
    }

    // Update status to Dibatalkan
    $update_stmt = $pdo->prepare("UPDATE orders SET status_order = 'Dibatalkan' WHERE id = ?");
    $update_stmt->execute([$order_id]);

    echo json_encode(['status' => 'success', 'message' => 'Pesanan berhasil dibatalkan.']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Kesalahan database: ' . $e->getMessage()]);
}
