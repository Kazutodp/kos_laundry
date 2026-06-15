<?php
// Mulai session
session_start();

// Hapus semua data session
$_SESSION = array();

// Hancurkan session
session_destroy();

// Alihkan pengguna ke login.html dengan pesan sukses
header("Location: login.html?success=" . urlencode("Anda telah berhasil keluar dari akun Anda."));
exit();
?>
