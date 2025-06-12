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
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #f4f6f9, #e6ecf3);
            margin: 0;
            padding: 0;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .profile-container {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .profile-container h2 {
            font-size: 26px;
            font-weight: 600;
            color: #003366;
            margin-bottom: 20px;
        }

        .profile-container table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .profile-container table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .profile-container table td:first-child {
            font-weight: bold;
            color: #003366;
        }

        .profile-container a {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 24px;
            background-color: #003366;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .profile-container a:hover {
            background-color: #002244;
        }

        .profile-container form {
            margin-top: 20px;
            text-align: left;
        }

        .profile-container form label {
            display: block;
            margin-bottom: 10px;
            color: #003366;
        }

        .profile-container form input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .profile-container form button {
            background-color: #003366;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }

        .profile-container form button:hover {
            background-color: #002244;
        }

        .profile-container p {
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
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
    </div>
</body>
</html>
