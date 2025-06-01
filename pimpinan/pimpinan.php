<?php
session_start();
include('../include/koneksi.php'); // koneksi ke database 
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pimpinan') {
    header("Location: /siman/login.php");
    // Jika bukan pimpinan, arahkan kembali ke login
    exit();
}

// Ambil data pengguna yang sedang login
$email = $_SESSION['email'];
$query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
$user = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pimpinan</title>
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
       

            

            
            <li><a href="../lap/laporan.php"> Laporan & Statistik</a></li>
             <li class="submenu-item">
                <a href="javascript:void(0);" onclick="showProfilePopup()">👤 Profil</a>
            </li>

          

            <!-- Dropdown menu Pengaturan -->
            <li class="submenu-item">
                <a href="javascript:void(0);" onclick="toggleSubmenu('pengaturan')"> Pengaturan</a>
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
                    <a href="../adm/lihat_aset.php"><button> Lihat Aset</button></a>
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
        <h3>Ubah Password</h3>
        <form method="post" action="../fitur/profil.php">
            <button type="submit">Ubah Password</button>
        </form>
    </div>
</body>
</html>
