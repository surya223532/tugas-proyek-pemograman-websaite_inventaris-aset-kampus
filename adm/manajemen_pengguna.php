<?php
session_start();
include('../include/koneksi.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: /siman/login.php");
    exit();
}

if (isset($_POST['tambah'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $query = "INSERT INTO users (email, nama, password, role) VALUES ('$email', '$nama', '$password', '$role')";
    if (!mysqli_query($conn, $query)) die("Error: " . mysqli_error($conn));
    header("Location: manajemen_pengguna.php");
    exit();
}

if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $query = "DELETE FROM users WHERE id_user=$id";
    if (!mysqli_query($conn, $query)) die("Error: " . mysqli_error($conn));
    header("Location: manajemen_pengguna.php");
    exit();
}

if (isset($_GET['reset'])) {
    $id = intval($_GET['reset']);
    $defaultPassword = '12345678';
    $query = "UPDATE users SET password='$defaultPassword' WHERE id_user=$id";
    if (!mysqli_query($conn, $query)) die("Error: " . mysqli_error($conn));
    header("Location: manajemen_pengguna.php");
    exit();
}

$keyword = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$users = ($keyword != '') ?
    mysqli_query($conn, "SELECT * FROM users WHERE nama LIKE '%$keyword%' OR email LIKE '%$keyword%'") :
    mysqli_query($conn, "SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Pengguna</title>
    <link rel="stylesheet" href="../assets/atur_pengguna.css">
</head>
<body>
    <div class="container">
        <h2>Manajemen Pengguna</h2>

        <form method="get" class="search-form">
            <input type="text" name="search" placeholder="Cari nama atau email" value="<?= htmlspecialchars($keyword) ?>">
            <button type="submit">Cari</button>
        </form>

        <h3>Tambah Pengguna</h3>
        <form method="post" class="form-tambah">
            <input type="text" name="nama" placeholder="Nama Lengkap" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="password" placeholder="Password" required>
            <select name="role" required>
                <option value="admin">Admin</option>
                <option value="staf">Staf</option>
                <option value="pimpinan">Pimpinan</option>
            </select>
            <button type="submit" name="tambah">Tambah</button>
        </form>

        <h3>Daftar Pengguna</h3>
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($users)) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>********</td> <!-- Password tidak ditampilkan -->
                    <td><?= htmlspecialchars($row['role']) ?></td>
                    <td>
                        <a href="?reset=<?= $row['id_user'] ?>" onclick="return confirm('Reset password ke default?')">Reset</a> |
                        <a href="?hapus=<?= $row['id_user'] ?>" onclick="return confirm('Yakin ingin menghapus pengguna ini?')">Hapus</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <br>
        <a href="admin.php" class="back-btn">⬅ Kembali ke Admin</a>
    </div>
</body>
</html>
