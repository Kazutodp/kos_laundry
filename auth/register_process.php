<?php
// Mulai session
session_start();

// Hubungkan ke database
require_once '../koneksi.php';

// Memastikan request dikirim via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.html");
    exit();
}

// Mengambil dan mensanitasi input form
$nama = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
$email = mysqli_real_escape_string($koneksi, trim($_POST['email']));
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validasi input sederhana
if (empty($nama) || empty($email) || empty($password) || empty($confirm_password)) {
    header("Location: register.html?error=" . urlencode("Semua kolom pendaftaran wajib diisi."));
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: register.html?error=" . urlencode("Format alamat email tidak valid."));
    exit();
}

if (strlen($password) < 8) {
    header("Location: register.html?error=" . urlencode("Kata sandi harus minimal 8 karakter."));
    exit();
}

if ($password !== $confirm_password) {
    header("Location: register.html?error=" . urlencode("Konfirmasi kata sandi tidak cocok."));
    exit();
}

// Periksa apakah email sudah terdaftar di database
$query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    if (!empty($user['google_id']) && empty($user['password'])) {
        // Akun terdaftar melalui Google (password kosong)
        header("Location: register.html?error=" . urlencode("Email ini sudah terdaftar menggunakan Google. Silakan masuk menggunakan tombol Google."));
    } else {
        // Akun terdaftar secara manual
        header("Location: register.html?error=" . urlencode("Alamat email sudah terdaftar. Silakan gunakan email lain atau langsung masuk."));
    }
    exit();
}

// Melakukan hashing password untuk keamanan data
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Memasukkan pengguna baru ke database
$insert_query = "INSERT INTO users (nama, email, password) VALUES ('$nama', '$email', '$hashed_password')";

if (mysqli_query($koneksi, $insert_query)) {
    $new_user_id = mysqli_insert_id($koneksi);
    
    // Set session untuk Auto-Login setelah registrasi berhasil
    $_SESSION['user_id'] = $new_user_id;
    $_SESSION['nama'] = $nama;
    $_SESSION['email'] = $email;
    
    header("Location: ../dashboard_user.html");
    exit();
} else {
    header("Location: register.html?error=" . urlencode("Terjadi kesalahan database saat mendaftar: " . mysqli_error($koneksi)));
    exit();
}
?>
