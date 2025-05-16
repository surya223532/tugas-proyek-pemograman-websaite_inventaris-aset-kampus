<?php
session_start();
include('include/koneksi.php');
// Hapus semua variabel sesi
$_SESSION = array();

// Hapus cookie sesi jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, 
        $params["path"], $params["domain"], 
        $params["secure"], $params["httponly"]
    );
}

// Hapus sesi dengan session_unset() sebelum session_destroy()
session_unset();
session_destroy();

// Redirect ke halaman login di lokasi yang benar
header("Location: /pinjam/login.php");
exit();
?>
