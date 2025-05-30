<?php
session_start();
include('../include/koneksi.php'); // koneksi ke database 
$allowed_roles = ['staf']; // bisa ditentukan sesuai kebutuhan

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /siman/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard <?= ucfirst($_SESSION['role']) ?></title>
    <link rel="stylesheet" href="../assets/admin.css">
    <script src="../assets/admin.js" defer></script>
    <script>
        function toggleSubmenu(id) {
            var submenu = document.getElementById(id);
            submenu.style.display = (submenu.style.display === "none" || submenu.style.display === "") ? "block" : "none";
        }

        function showProfilePopup() {
            document.getElementById('profile-popup').style.display = 'block';
        }

        function closeProfilePopup() {
            document.getElementById('profile-popup').style.display = 'none';
        }
    </script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Manajemen Aset</h2>
        <ul>
            

            <li class="submenu-item">
                <a href="javascript:void(0);" onclick="toggleSubmenu('aset-lengkap')">📝 Pengelolaan Aset Lengkap</a>
                <ul class="submenu" id="aset-lengkap">
                    <li><a href="../adm/atur_aset.php">⚙️ Atur Aset</a></li>
                    <li><a href="../adm/kategori_aset.php">📦 Kategori Aset</a></li>
                    <li><a href="../adm/lokasi_aset.php">📍 Lokasi Aset</a></li>
                    <li><a href="../adm/lihat_aset.php">🔍 Lihat Aset</a></li>
                </ul>
            </li>

            <li><a href="../fitur/penyusutan.php">📉 Kelola Penyusutan</a></li>
            <li><a href="../lap/laporan.php">📊 Laporan & Statistik</a></li>

            <li class="submenu-item">
                <a href="#" onclick="showProfilePopup();return false;">👤 Profil</a>
                <ul class="submenu" id="profil">
                    <li><a href="#" onclick="showProfilePopup();return false;">Lihat Profil</a></li>

                </ul>
            </li>

            <li class="submenu-item">
                <a href="javascript:void(0);" onclick="toggleSubmenu('pengaturan')">⚙️ Pengaturan</a>
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
                <h3>Selamat datang, <?= ucfirst($_SESSION['role']) ?></h3>
                <div>
                    <a href="../adm/lihat_aset.php"><button>🔍 Lihat Aset</button></a>
                </div>
            </section>
        </main>

        <footer>
            <p>&copy; <?= date("Y") ?> Sistem Manajemen Aset Kampus</p>
        </footer>
    </div>

    <!-- Popup Profil -->
    <div class="profile-popup" id="profile-popup" style="display:none;">
      <button onclick="closeProfilePopup()" style="float:right;background:none;border:none;font-size:18px;color:#1abc9c;cursor:pointer;">&times;</button>
      <h2>Profil Saya</h2>
      <table>
        <tr><td>Email</td><td><?= htmlspecialchars($user['email']) ?></td></tr>
        <tr><td>Nama</td><td><?= htmlspecialchars($user['nama']) ?></td></tr>
        <tr><td>Role</td><td><?= htmlspecialchars($user['role']) ?></td></tr>
      </table>
      <!-- Form ubah password, dst, copy dari profil.php -->
    </div>
</body>
</html>
