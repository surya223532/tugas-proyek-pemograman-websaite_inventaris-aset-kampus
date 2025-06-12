<!-- filepath: c:\xampp\htdocs\siman\include\popup_profil.php -->
<style>
    .profile-popup {
        position: fixed;
        top: 10px; /* Jarak dari atas */
        right: 10px; /* Jarak dari kanan */
        background: #fff;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        z-index: 1000;
        display: none; /* Popup akan terlihat saat diubah ke block */
    }
</style>
<div class="profile-popup" id="profile-popup" style="display:none;">
    <button onclick="closeProfilePopup()" style="float:right;background:none;border:none;font-size:18px;color:#1abc9c;cursor:pointer;">&times;</button>
    <h2>Profil Saya</h2>
    <table>
        <tr><td>Email</td><td><?= htmlspecialchars($user['email'] ?? 'Tidak tersedia') ?></td></tr>
        <tr><td>Nama</td><td><?= htmlspecialchars($user['nama'] ?? 'Tidak tersedia') ?></td></tr>
        <tr><td>Role</td><td><?= htmlspecialchars($user['role'] ?? 'Tidak tersedia') ?></td></tr>
    </table>
    <h3>Ubah Password</h3>
    <?php if (isset($pesan) && $pesan): ?>
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
<?php
if (!isset($conn)) {
    die('Koneksi database tidak tersedia.');
}

if (empty($user)) {
    $pesan = 'Data pengguna tidak tersedia.';
} else {
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
            $update = mysqli_query($conn, "UPDATE users SET password='$password_baru' WHERE email='{$user['email']}'");
            if ($update) {
                $pesan = 'Password berhasil diubah!';
            } else {
                $pesan = 'Gagal mengubah password. Error: ' . mysqli_error($conn);
            }
        }
    }
}


?>
<script>
function showProfilePopup() {
    document.getElementById('profile-popup').style.display = 'block';
    console.log('Popup dibuka');
}

function closeProfilePopup() {
    document.getElementById('profile-popup').style.display = 'none';
}
</script>