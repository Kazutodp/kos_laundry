<?php
// Mulai session
session_start();

// Hapus semua data session
$_SESSION = array();

// Hancurkan session
session_destroy();

// Alihkan admin ke login.php dengan pesan sukses
header("Location: login.php?error=" . urlencode("Anda telah berhasil keluar dari sesi admin."));
exit();
?>
