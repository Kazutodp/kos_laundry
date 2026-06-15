<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "kos_laundry"; // Silakan ganti sesuai dengan nama database Anda di phpMyAdmin

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    // Jika koneksi gagal, kembalikan respons JSON error
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Koneksi ke database gagal: ' . mysqli_connect_error()
    ]);
    exit();
}
?>
