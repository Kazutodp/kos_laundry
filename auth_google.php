<?php
header('Content-Type: application/json');

// Mulai session
session_start();

// Hubungkan ke database
require_once 'koneksi.php';

// Membaca input JSON dari fetch
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_token'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Token Google tidak valid atau tidak ditemukan.'
    ]);
    exit();
}

$id_token = $input['id_token'];

// Verifikasi token melalui endpoint Google Tokeninfo resmi
$url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($id_token);

// Lakukan request HTTP GET ke Google API menggunakan cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Aktifkan verifikasi sertifikat SSL untuk keamanan publik
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    echo json_encode([
        'success' => false,
        'message' => 'Verifikasi token Google gagal atau token telah kadaluarsa.'
    ]);
    exit();
}

$payload = json_decode($response, true);

if (!isset($payload['sub'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Payload token Google tidak valid.'
    ]);
    exit();
}

// Data pengguna yang dikonfirmasi dari Google
$google_id = mysqli_real_escape_string($koneksi, $payload['sub']);
$email = mysqli_real_escape_string($koneksi, $payload['email']);
$nama = mysqli_real_escape_string($koneksi, $payload['name']);

// Periksa apakah pengguna sudah terdaftar di database
$query = "SELECT * FROM users WHERE google_id = '$google_id' OR email = '$email' LIMIT 1";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) > 0) {
    // Pengguna sudah ada di database, ambil datanya
    $user = mysqli_fetch_assoc($result);
    
    // Jika sebelumnya user daftar manual dengan email yang sama, hubungkan google_id ke akun tersebut
    if (empty($user['google_id'])) {
        mysqli_query($koneksi, "UPDATE users SET google_id = '$google_id' WHERE id = " . $user['id']);
    }
    
    // Set session login
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['email'] = $user['email'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Login berhasil!'
    ]);
} else {
    // Pengguna belum terdaftar, masukkan sebagai akun baru ke database (Registrasi otomatis)
    $insert_query = "INSERT INTO users (nama, email, google_id) VALUES ('$nama', '$email', '$google_id')";
    
    if (mysqli_query($koneksi, $insert_query)) {
        $new_user_id = mysqli_insert_id($koneksi);
        
        // Set session login untuk pengguna baru
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['nama'] = $nama;
        $_SESSION['email'] = $email;
        
        echo json_encode([
            'success' => true,
            'message' => 'Registrasi dan login berhasil!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal mendaftarkan akun baru ke database: ' . mysqli_error($koneksi)
        ]);
    }
}
?>
