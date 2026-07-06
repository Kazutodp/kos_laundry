<?php
// mitra/logout.php
session_start();
unset($_SESSION['mitra_logged_in']);
unset($_SESSION['mitra_id']);
unset($_SESSION['mitra_nama']);
session_destroy();

header("Location: ../mitra/login.php");
exit();
