<?php
// Mulai session
session_start();

// Hubungkan ke database
require_once '../koneksi.php';

// Memastikan request dikirim via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_login.php");
    exit();
}

// Mengambil dan mensanitasi input form
$username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
$password = $_POST['password'];

// Validasi input sederhana
if (empty($username) || empty($password)) {
    header("Location: admin_login.php?error=" . urlencode("Username dan password wajib diisi."));
    exit();
}

// Periksa akun di database berdasarkan username
$query = "SELECT * FROM admins WHERE username = '$username' LIMIT 1";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) === 0) {
    header("Location: admin_login.php?error=" . urlencode("Username tidak terdaftar."));
    exit();
}

$admin = mysqli_fetch_assoc($result);

// Verifikasi password hash
if (password_verify($password, $admin['password'])) {
    // Set session login untuk admin
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_nama'] = $admin['nama'];
    
    header("Location: ../admin_dashboard.php");
    exit();
} else {
    header("Location: admin_login.php?error=" . urlencode("Kata sandi salah."));
    exit();
}
?>
