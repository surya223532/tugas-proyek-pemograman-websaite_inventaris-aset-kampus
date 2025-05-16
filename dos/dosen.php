<?php
session_start();
include('../include/koneksi.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'dosen') {
    header("Location: login.php"); // Jika bukan dosen, arahkan kembali ke login
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosen Dashboard</title>
</head>
<body>
    <h2>Welcome, Dosen</h2>
    <p>Anda berhasil login sebagai Dosen.</p>
    <a href="/pinjam/logout.php">Logout</a>
    <!-- Tambahkan fitur dosen di sini -->
</body>
</html>
