<?php
session_start();
include('../include/koneksi.php');
include('../include/popup_profil.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: /siman/login.php");
    exit();
}

// Handle form submissions
if (isset($_POST['tambah'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $query = "INSERT INTO users (email, nama, password, role) VALUES ('$email', '$nama', '$password', '$role')";
    if (!mysqli_query($conn, $query)) {
        $message = "Error: " . mysqli_error($conn);
    } else {
        $message = "Pengguna berhasil ditambahkan!";
    }
}

if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $query = "DELETE FROM users WHERE id_user=$id";
    if (!mysqli_query($conn, $query)) {
        $message = "Error: " . mysqli_error($conn);
    } else {
        $message = "Pengguna berhasil dihapus!";
    }
}

if (isset($_GET['reset'])) {
    $id = intval($_GET['reset']);
    $defaultPassword = '12345678';
    $query = "UPDATE users SET password='$defaultPassword' WHERE id_user=$id";
    if (!mysqli_query($conn, $query)) {
        $message = "Error: " . mysqli_error($conn);
    } else {
        $message = "Password berhasil direset ke default!";
    }
}

// Search functionality
$keyword = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$query = ($keyword != '') ? 
    "SELECT * FROM users WHERE nama LIKE '%$keyword%' OR email LIKE '%$keyword%' ORDER BY nama ASC" :
    "SELECT * FROM users ORDER BY nama ASC";
$users = mysqli_query($conn, $query);
?>

<?php include('../include/header.php'); ?>
<?php include('../include/sidebar_admin.php'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Konten Utama -->
<div class="main-content">
    <header>
        <h2>Manajemen Pengguna</h2>
    </header>

    <main>
        <?php if (isset($message)) echo "<div class='alert'>$message</div>"; ?>

        <!-- Form Pencarian -->
        <section class="search-section">
            <form method="get" class="search-form">
                <div class="form-group">
                    <input type="text" name="search" placeholder="Cari nama atau email" 
                           value="<?= htmlspecialchars($keyword) ?>" class="search-input">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </form>
        </section>

        <!-- Form Tambah Pengguna -->
        <section class="form-section">
            <h3>Tambah Pengguna Baru</h3>
            <form method="post" class="user-form">
                <div class="form-group">
                    <label for="nama">Nama Lengkap:</label>
                    <input type="text" id="nama" name="nama" placeholder="Nama Lengkap" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="text" id="password" name="password" placeholder="Password" required>
                </div>

                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="admin">Admin</option>
                        <option value="staf">Staf</option>
                        <option value="pimpinan">Pimpinan</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" name="tambah" class="btn btn-primary">Tambah Pengguna</button>
                </div>
            </form>
        </section>

        <!-- Daftar Pengguna -->
        <section class="user-list">
            <h3>Daftar Pengguna</h3>
            <div class="table-container">
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
                        <?php if (mysqli_num_rows($users) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($users)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td>********</td>
                                    <td><?= htmlspecialchars($row['role']) ?></td>
                                    <td class="aksi">
                                        <a href="?reset=<?= $row['id_user'] ?>" class="btn-icon reset" title="Reset Password" 
                                           onclick="return confirm('Reset password ke default?')">
                                            <i class="fa-solid fa-rotate-left"></i>
                                        </a>
                                        <a href="?hapus=<?= $row['id_user'] ?>" class="btn-icon delete" title="Hapus Pengguna" 
                                           onclick="return confirm('Yakin ingin menghapus pengguna ini?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">Tidak ada data pengguna yang ditemukan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="form-actions">
            <a href="../adm/admin.php" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Sistem Manajemen Aset Kampus</p>
    </footer>
</div>

<?php include('../include/footer.php'); ?>
<?php mysqli_close($conn); ?>