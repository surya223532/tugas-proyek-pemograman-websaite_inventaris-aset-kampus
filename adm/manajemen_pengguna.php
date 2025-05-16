<?php
// Koneksi ke database
include('../include/koneksi.php');

// Tangani aksi tambah pengguna
if (isset($_POST['tambah'])) {
    $email = $_POST['email'];
    $nama = $_POST['nama'];
    $password = $_POST['password']; // plaintext
    $role = $_POST['role'];

    $query = "INSERT INTO users (email, nama, password, role) VALUES ('$email', '$nama', '$password', '$role')";
    
    if (!mysqli_query($conn, $query)) {
        die("Error: " . mysqli_error($conn));
    }

    // Redirect setelah berhasil tambah
    header("Location: manajemen_pengguna.php");
    exit();
}

// Tangani aksi hapus pengguna
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $query = "DELETE FROM users WHERE id_user=$id";

    if (!mysqli_query($conn, $query)) {
        die("Error: " . mysqli_error($conn));
    }

    // Redirect setelah berhasil hapus
    header("Location: manajemen_pengguna.php");
    exit();
}

// Ambil data untuk form edit
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = mysqli_query($conn, "SELECT * FROM users WHERE id_user=$id");
    $editData = mysqli_fetch_assoc($query);
}

// Tangani aksi update pengguna
if (isset($_POST['update'])) {
    $id = $_POST['user_id'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];  // plaintext
    $role = $_POST['role'];

    // Cek jika password diubah, jika tidak, password tetap yang lama
    if (empty($password)) {
        // Jika password kosong, update tanpa mengganti password
        $query = "UPDATE users SET nama='$nama', email='$email', role='$role' WHERE id_user=$id";
    } else {
        // Jika password diubah, update dengan password baru
        $query = "UPDATE users SET nama='$nama', email='$email', password='$password', role='$role' WHERE id_user=$id";
    }

    if (!mysqli_query($conn, $query)) {
        die("Error: " . mysqli_error($conn));
    }

    // Redirect setelah berhasil update
    header("Location: manajemen_pengguna.php");
    exit();
}

// Ambil semua data pengguna
$users = mysqli_query($conn, "SELECT * FROM users");

?>

<!-- Tampilkan Halaman HTML -->
<h2>Manajemen Pengguna</h2>

<!-- Form Edit Pengguna -->
<?php if (isset($_GET['edit'])) { ?>
    <h3>Edit Pengguna</h3>
    <form method="post">
        <input type="hidden" name="user_id" value="<?= $editData['id_user'] ?>">
        <input type="text" name="nama" value="<?= $editData['nama'] ?>" required>
        <input type="email" name="email" value="<?= $editData['email'] ?>" required>
        <input type="password" name="password" placeholder="Isi jika ingin ganti password">
        <select name="role">
            <option value="admin" <?= $editData['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="staf" <?= $editData['role'] == 'staf' ? 'selected' : '' ?>>Staf</option>
            <option value="pimpinan" <?= $editData['role'] == 'pimpinan' ? 'selected' : '' ?>>Pimpinan</option>
            <option value="dosen" <?= $editData['role'] == 'dosen' ? 'selected' : '' ?>>Dosen</option>
        </select>
        <button type="submit" name="update">Simpan Perubahan</button>
        <a href="manajemen_pengguna.php">Batal</a>
    </form>
<?php } ?>

<!-- Form Tambah Pengguna -->
<h3>Tambah Pengguna</h3>
<form method="post">
    <input type="text" name="nama" placeholder="Nama Lengkap" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="password" placeholder="Password" required>
    <select name="role">
        <option value="admin">Admin</option>
        <option value="staf">Staf</option>
        <option value="pimpinan">Pimpinan</option>
        <option value="dosen">Dosen</option>
    </select>
    <button type="submit" name="tambah">Tambah</button>
</form>

<!-- Tabel Pengguna -->
<table border="1">
    <tr><th>Nama</th><th>Email</th><th>Password</th><th>Role</th><th>Aksi</th></tr>
    <?php while ($row = mysqli_fetch_assoc($users)) { ?>
    <tr>
        <td><?= htmlspecialchars($row['nama']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['password']) ?></td>
        <td><?= htmlspecialchars($row['role']) ?></td>
        <td>
            <a href="?edit=<?= htmlspecialchars($row['id_user']) ?>">Edit</a> |
            <a href="?hapus=<?= htmlspecialchars($row['id_user']) ?>" onclick="return confirm('Yakin hapus?')">Hapus</a>
        </td>
    </tr>
    <?php } ?>
</table>

<!-- Tombol Kembali -->
<a href="admin.php" style="display: inline-block; padding: 10px; background: #007BFF; color: white; text-decoration: none; border-radius: 5px;">⬅ Kembali ke Admin</a>
