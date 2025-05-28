<?php
session_start();
include('include/koneksi.php');

// Cek apakah user sudah login dan role staf
$allowed_roles = ['staf']; 
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /siman/login.php");
    exit();
}

$username = $_SESSION['email'];  // Sesuaikan dengan session yang kamu pakai, misal email atau user_id
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $message = "Semua kolom harus diisi.";
    } elseif ($new_password !== $confirm_password) {
        $message = "Password baru dan konfirmasi tidak sama.";
    } else {
        // Ambil password lama dari database
        $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($current_password);
            $stmt->fetch();

            if ($old_password === $current_password) {
                // Update password baru
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $update_stmt->bind_param("ss", $new_password, $username);

                if ($update_stmt->execute()) {
                    $message = "Password berhasil diubah.";
                } else {
                    $message = "Gagal mengubah password, coba lagi.";
                }
                $update_stmt->close();
            } else {
                $message = "Password lama salah.";
            }
        } else {
            $message = "User tidak ditemukan.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Ubah Password</title>
    <link rel="stylesheet" href="../assets/admin.css" />
</head>
<body>
    <div class="ubah-password-container">
        <h2>Ubah Password</h2>
        <?php if ($message): ?>
            <p style="color:red;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form action="" method="post">
            <label for="old_password">Password Lama:</label><br>
            <input type="password" id="old_password" name="old_password" required><br><br>

            <label for="new_password">Password Baru:</label><br>
            <input type="password" id="new_password" name="new_password" required><br><br>

            <label for="confirm_password">Konfirmasi Password Baru:</label><br>
            <input type="password" id="confirm_password" name="confirm_password" required><br><br>

            <button type="submit">Ubah Password</button>
        </form>
        <br>
        <a href="/pinjam/staf/staf.php">Kembali ke Dashboard</a>

    </div>
</body>
</html>
