<?php
session_start();
include('../include/koneksi.php');

// Cek apakah user sudah login dan memiliki role yang diizinkan
$allowed_roles = ['staf', 'admin', 'pimpinan']; 
if (!isset($_SESSION['role']) || !isset($_SESSION['email']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /siman/login.php");
    exit();
}

$email = $_SESSION['email'];

// Ambil data user yang login
$query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
$user = mysqli_fetch_assoc($query);

$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ubah_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi'];

    // Cek password lama
    if ($password_lama !== $user['password']) {
        $pesan = 'Password lama salah!';
    } elseif ($password_baru !== $konfirmasi) {
        $pesan = 'Konfirmasi password baru tidak cocok!';
    } else {
        // Simpan password baru langsung (plaintext)
        $update = mysqli_query($conn, "UPDATE users SET password='$password_baru' WHERE email='$email'");
        if ($update) {
            $pesan = 'Password berhasil diubah!';
        } else {
            $pesan = 'Gagal mengubah password. Silakan coba lagi.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya</title>
    <link rel="stylesheet" href="../assets/profil.css">
</head>
<body>
    <h2>Profil Saya</h2>
    <table>
        <tr><td>Email</td><td><?= htmlspecialchars($user['email']) ?></td></tr>
        <tr><td>Nama</td><td><?= htmlspecialchars($user['nama']) ?></td></tr>
        <tr><td>Role</td><td><?= htmlspecialchars($user['role']) ?></td></tr>
    </table>
    <?php
    // Tentukan halaman kembali berdasarkan role
    $role = $_SESSION['role'];
    switch ($role) {
        case 'staf':
            $kembali = '../staf/staf.php';
            break;
        case 'admin':
            $kembali = '../adm/admin.php';
            break;
        case 'pimpinan':
            $kembali = '../pimpinan/pimpinan.php';
            break;
        default:
            $kembali = '../index.php';
    }
    ?>
    <a href="<?= $kembali ?>">Kembali</a>

    <h3>Ubah Password</h3>
    <?php if ($pesan): ?>
        <p style="color:<?= $pesan === 'Password berhasil diubah!' ? 'green' : 'red' ?>;">
            <?= htmlspecialchars($pesan) ?>
        </p>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="ubah_password" value="1">
        <label>Password Lama:<br>
            <input type="password" name="password_lama" required>
        </label><br>
        <label>Password Baru:<br>
            <input type="password" name="password_baru" required>
        </label><br>
        <label>Konfirmasi Password Baru:<br>
            <input type="password" name="konfirmasi" required>
        </label><br>
        <button type="submit">Ubah Password</button>
    </form>
</body>
</html>
