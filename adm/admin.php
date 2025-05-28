<?php
session_start();
include('../include/koneksi.php'); // koneksi ke database 
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: /siman/login.php");
    // Jika bukan admin, arahkan kembali ke login
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../assets/admin.css"> <!-- Menautkan CSS -->
    <script src="../assets/admin.js" defer></script> <!-- Menautkan JavaScript -->
    <script>
        function toggleSubmenu(id) {
            var submenu = document.getElementById(id);
            if (submenu.style.display === "none" || submenu.style.display === "") {
                submenu.style.display = "block";
            } else {
                submenu.style.display = "none";
            }
        }
    </script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Manajemen Aset</h2>
        <ul>
            <li><a href="manajemen_pengguna.php">Manajemen Pengguna</a></li>

            <!-- Dropdown menu untuk Pengelolaan Aset Lengkap -->
            <li class="submenu-item">
                <a href="javascript:void(0);" onclick="toggleSubmenu('aset-lengkap')">Pengelolaan Aset Lengkap</a>
                <ul class="submenu" id="aset-lengkap">
                    <li><a href="atur_aset.php">Atur Aset</a></li>
                    <li><a href="kategori_aset.php">Kategori Aset</a></li>
                    <li><a href="lokasi_aset.php">Lokasi Aset</a></li>
                    <li><a href="lihat_aset.php">Lihat Aset</a></li>
                </ul>
            </li>

            <li><a href="../fitur/penyusutan.php">Kelola Penyusutan</a></li>
            <li><a href="../lap/laporan.php">Laporan & Statistik</a></li>

            <!-- Dropdown menu Backup dan Restore -->
            <li class="submenu-item">
                <a href="javascript:void(0);" onclick="toggleSubmenu('backup-restore')">Backup dan Restore</a>
                <ul class="submenu" id="backup-restore">
                    <li><a href="../backup/backup.php">Backup Data</a></li>
                    <li><a href="../backup/restore.php">Restore Data</a></li>
                </ul>
            </li>

            <!-- Dropdown menu Pengaturan -->
            <li class="submenu-item">
                <a href="javascript:void(0);" onclick="toggleSubmenu('pengaturan')">Pengaturan</a>
                <ul class="submenu" id="pengaturan">
                    <li><a href="setting1.php">Setting 1</a></li>
                    <li><a href="setting2.php">Setting 2</a></li>
                </ul>
                <a href="/siman/logout.php">Logout</a>
            </li>
        </ul>
    </div>

    <!-- Konten Utama -->
    <div class="main-content">
        <header>
           
        </header>

        <main>
            <section>
                <h3>Manajemen Sistem</h3>
                <div>
                    <a href="lihat_aset.php"><button>Lihat Aset</button></a>
                </div>
            </section>
        </main>

        <footer>
            <p>&copy; <?= date("Y") ?> Sistem Manajemen Aset Kampus</p>
        </footer>
    </div>
</body>
</html>
