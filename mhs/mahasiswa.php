<?php
session_start();
include('../include/koneksi.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: login.php"); // Jika bukan mahasiswa, arahkan kembali ke login
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mahasiswa Dashboard</title>
</head>
<body>
    <h2>Welcome, Mahasiswa</h2>
    <p>Anda berhasil login sebagai Mahasiswa.</p>
    <a href="/pinjam/logout.php">Logout</a>
    <!-- Tambahkan fitur mahasiswa di sini -->
</body>
</html>
