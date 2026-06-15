<?php
// Mulai session
session_start();

// Hubungkan ke database
require_once 'koneksi.php';

// Memastikan request dikirim via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.html");
    exit();
}

// Mengambil dan mensanitasi input form
$email = mysqli_real_escape_string($koneksi, trim($_POST['email']));
$password = $_POST['password'];

// Validasi input sederhana
if (empty($email) || empty($password)) {
    header("Location: login.html?error=" . urlencode("Email dan kata sandi wajib diisi."));
    exit();
}

// Periksa akun di database berdasarkan email
$query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) === 0) {
    header("Location: login.html?error=" . urlencode("Alamat email tidak terdaftar. Silakan daftar terlebih dahulu."));
    exit();
}

$user = mysqli_fetch_assoc($result);

// Memeriksa jika akun dibuat via Google (password NULL)
if (empty($user['password']) && !empty($user['google_id'])) {
    header("Location: login.html?error=" . urlencode("Akun Anda terdaftar menggunakan Google. Silakan klik tombol 'Continue with Google' untuk masuk."));
    exit();
}

// Verifikasi password hash
if (password_verify($password, $user['password'])) {
    // Set session login untuk pengguna
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['email'] = $user['email'];
    
    header("Location: dashboard_user.html");
    exit();
} else {
    header("Location: login.html?error=" . urlencode("Kata sandi yang Anda masukkan salah. Silakan coba lagi."));
    exit();
}
?>
