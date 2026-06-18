<?php
session_start();
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_nama']);

// Redirect to admin login page
header("Location: admin.php");
exit();
?>
