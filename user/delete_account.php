<?php
session_start();
require_once '../db_connect.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    try {
        // Ambil path foto profil dari database untuk dihapus dari server
        $stmt = $pdo->prepare("SELECT foto_profil FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user && !empty($user['foto_profil'])) {
            $file_path = '../' . $user['foto_profil'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Hapus data user dari database
        $stmt_delete = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt_delete->execute([$user_id]);
        
        // Hancurkan session
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        
        // Redirect ke beranda
        header("Location: ../index.php");
        exit();
    } catch (PDOException $e) {
        die("Gagal menghapus akun: " . $e->getMessage());
    }
} else {
    // Jika diakses selain dengan POST, redirect ke security.php
    header("Location: security.php");
    exit();
}
