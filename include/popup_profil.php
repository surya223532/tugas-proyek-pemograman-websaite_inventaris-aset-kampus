<?php
session_start();
include_once('../include/koneksi.php');

// Ambil data user dari session
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $query_user = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' LIMIT 1");
    $user = mysqli_fetch_assoc($query_user);
} else {
    $user = null;
}

// Proses ubah password (plain text)
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ubah_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi'];

    // Cek password lama (plain text)
    if ($password_lama != $user['password']) {
        $pesan = 'Password lama salah!';
    } elseif ($password_baru != $konfirmasi) {
        $pesan = 'Konfirmasi password baru tidak cocok!';
    } else {
        // Update password (plain text)
        $update = mysqli_query($conn, "UPDATE users SET password='$password_baru' WHERE email='{$user['email']}'");
        $pesan = $update ? 'Password berhasil diubah!' : 'Gagal mengubah password: ' . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil Pengguna</title>
    <link rel="stylesheet" href="../assets/profil.css">
</head>
<body>

<!-- Overlay (untuk menutup popup saat klik di luar) -->
<div class="overlay" id="profile-overlay" onclick="closeProfile()"></div>

<!-- Popup Profil -->
<div class="profile-popup" id="profile-popup">
    <button class="close-btn" onclick="closeProfile()">&times;</button>
    
    <div class="popup-header">
        <h2 style="margin: 0; font-size: 20px;">Profil Pengguna</h2>
    </div>
    
    <table class="profile-table">
        <tr>
            <td>Email</td>
            <td><?= htmlspecialchars($user['email'] ?? 'Tidak tersedia') ?></td>
        </tr>
        <tr>
            <td>Nama</td>
            <td><?= htmlspecialchars($user['nama'] ?? 'Tidak tersedia') ?></td>
        </tr>
        <tr>
            <td>Role</td>
            <td><?= htmlspecialchars($user['role'] ?? 'Tidak tersedia') ?></td>
        </tr>
    </table>
    
    <h3 style="margin-top: 20px; font-size: 16px;">Ubah Password</h3>
    
    <?php if ($pesan): ?>
        <div class="message <?= strpos($pesan, 'berhasil') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($pesan) ?>
        </div>
    <?php endif; ?>
    
    <form method="post" class="password-form">
        <input type="hidden" name="ubah_password" value="1">
        
        <label style="font-weight: bold; display: block; margin-top: 10px;">Password Lama:</label>
        <input type="password" name="password_lama" required>
        
        <label style="font-weight: bold; display: block;">Password Baru:</label>
        <input type="password" name="password_baru" required>
        
        <label style="font-weight: bold; display: block;">Konfirmasi Password:</label>
        <input type="password" name="konfirmasi" required>
        
        <button type="submit">Simpan Perubahan</button>
    </form>
</div>

<script>
// Fungsi buka popup profil
function showProfile() {
    document.getElementById('profile-overlay').style.display = 'block';
    document.getElementById('profile-popup').style.display = 'block';
}

// Fungsi tutup popup profil
function closeProfile() {
    document.getElementById('profile-overlay').style.display = 'none';
    document.getElementById('profile-popup').style.display = 'none';
}

// Untuk dipanggil dari tombol profil di sidebar
function openProfileFromSidebar() {
    showProfile();
}
</script>

</body>
</html>