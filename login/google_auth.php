<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak didukung.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$credential = $input['credential'] ?? '';

if (empty($credential)) {
    echo json_encode(['success' => false, 'message' => 'Token Google tidak ditemukan.']);
    exit();
}

// Decode JWT token
$parts = explode('.', $credential);
if (count($parts) !== 3) {
    echo json_encode(['success' => false, 'message' => 'Format token Google tidak valid.']);
    exit();
}

// Decode payload
$payload_b64 = $parts[1];
$payload_json = base64_decode(str_pad(strtr($payload_b64, '-_', '+/'), strlen($payload_b64) % 4, '=', STR_PAD_RIGHT));
$payload = json_decode($payload_json, true);

if (!$payload) {
    echo json_encode(['success' => false, 'message' => 'Gagal membaca payload token Google.']);
    exit();
}

// Verifikasi sederhana payload
$google_id = $payload['sub'] ?? '';
$email = $payload['email'] ?? '';
$name = $payload['name'] ?? '';
$picture = $payload['picture'] ?? '';

if (empty($google_id) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Informasi pengguna Google tidak lengkap.']);
    exit();
}

try {
    // 1. Cari berdasarkan google_id
    $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ?");
    $stmt->execute([$google_id]);
    $user = $stmt->fetch();

    if ($user) {
        // User sudah terdaftar via Google, langsung login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['nama'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['profile_pic'] = $user['foto_profil'];

        echo json_encode(['success' => true, 'redirect' => '../index.php']);
        exit();
    }

    // 2. Jika tidak ada google_id, cari berdasarkan email (untuk menghubungkan akun)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user_by_email = $stmt->fetch();

    if ($user_by_email) {
        // Update google_id dan foto profil ke akun yang sudah ada
        $update_stmt = $pdo->prepare("UPDATE users SET google_id = ?, foto_profil = ? WHERE id = ?");
        $update_stmt->execute([$google_id, $picture, $user_by_email['id']]);

        $_SESSION['user_id'] = $user_by_email['id'];
        $_SESSION['username'] = $user_by_email['nama'];
        $_SESSION['email'] = $user_by_email['email'];
        $_SESSION['profile_pic'] = $picture;

        echo json_encode(['success' => true, 'redirect' => '../index.php']);
        exit();
    }

    // 3. Jika benar-benar baru, daftarkan user baru
    $insert_stmt = $pdo->prepare("INSERT INTO users (nama, email, google_id, foto_profil) VALUES (?, ?, ?, ?)");
    $insert_stmt->execute([$name, $email, $google_id, $picture]);
    
    // Ambil user ID baru
    $new_user_id = $pdo->lastInsertId();

    $_SESSION['user_id'] = $new_user_id;
    $_SESSION['username'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['profile_pic'] = $picture;

    echo json_encode(['success' => true, 'redirect' => '../index.php']);
    exit();

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Kesalahan database: ' . $e->getMessage()]);
    exit();
}
?>
