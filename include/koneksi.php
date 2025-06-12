<?php
$host = "localhost";      // biasanya localhost
$user = "root";           // default user MySQL
$pass = "";               // kosong jika tidak ada password (XAMPP)
$db   = "mith";  // ganti dengan nama database kamu

$conn = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
