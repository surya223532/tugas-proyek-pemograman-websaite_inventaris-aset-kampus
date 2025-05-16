<?php
session_start();
include('../include/koneksi.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'staf') {
    header("Location: login.php"); // Jika bukan staf, arahkan kembali ke login
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staf Dashboard</title>
</head>
<body>
    <h2>Welcome, Staf</h2>
    <p>Anda berhasil login sebagai Staf.</p>
    <a href="/pinjam/logout.php">Logout</a>
    <!-- Tambahkan fitur staf di sini -->
</body>
</html>
