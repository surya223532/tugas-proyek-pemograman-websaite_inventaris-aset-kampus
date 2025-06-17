<?php
session_start();
include('../include/koneksi.php');
include('../include/popup_profil.php');

$allowed_roles = ['admin', 'staf'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /siman/login.php");
    exit();
}

$message = "";

// Tambah lokasi
if (isset($_POST['submit'])) {
    $nama_lokasi = mysqli_real_escape_string($conn, $_POST['nama_lokasi']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    if (!empty($nama_lokasi) && !empty($alamat)) {
        $query = "INSERT INTO lokasi (nama_lokasi, alamat) VALUES ('$nama_lokasi', '$alamat')";
        if (mysqli_query($conn, $query)) {
            $message = "Lokasi aset berhasil ditambahkan!";
        } else {
            $message = "Error: " . mysqli_error($conn);
        }
    } else {
        $message = "Semua field wajib diisi.";
    }
}

// Hapus lokasi
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $query = "DELETE FROM lokasi WHERE id_lokasi = $id";
    if (mysqli_query($conn, $query)) {
        $message = "Lokasi aset berhasil dihapus!";
    } else {
        $message = "Gagal menghapus lokasi: " . mysqli_error($conn);
    }
}

// Edit lokasi (proses update)
if (isset($_POST['update'])) {
    $id_lokasi = (int)$_POST['id_lokasi'];
    $nama_lokasi = mysqli_real_escape_string($conn, $_POST['nama_lokasi']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    $query = "UPDATE lokasi SET nama_lokasi = '$nama_lokasi', alamat = '$alamat' WHERE id_lokasi = $id_lokasi";
    if (mysqli_query($conn, $query)) {
        $message = "Data lokasi berhasil diperbarui.";
        unset($_GET['edit']);
    } else {
        $message = "Gagal memperbarui lokasi: " . mysqli_error($conn);
    }
}

// Ambil data lokasi untuk edit (jika ada parameter edit)
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = (int)$_GET['edit'];
    $query = "SELECT * FROM lokasi WHERE id_lokasi = $id";
    $result_edit = mysqli_query($conn, $query);
    $edit_data = mysqli_fetch_assoc($result_edit);
}

// Ambil semua data lokasi
$query = "SELECT * FROM lokasi ORDER BY nama_lokasi ASC";
$result = mysqli_query($conn, $query);
?>

<?php include('../include/header.php'); ?>
<?php include($_SESSION['role'] === 'admin' ? '../include/sidebar_admin.php' : '../include/sidebar_staf.php'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Konten Utama -->
<div class="main-content">
    <header>
        <h2>Manajemen Lokasi Aset</h2>
    </header>

    <main>
        <?php if (!empty($message)) echo "<div class='alert'>$message</div>"; ?>

        <!-- Form Tambah/Edit Lokasi -->
        <section class="form-section">
            <h3><?= $edit_mode ? "Edit Lokasi" : "Tambah Lokasi"; ?></h3>
            <form method="post" class="asset-form">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="id_lokasi" value="<?= htmlspecialchars($edit_data['id_lokasi']); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="nama_lokasi">Nama Lokasi:</label>
                    <input type="text" id="nama_lokasi" name="nama_lokasi" required 
                           value="<?= $edit_mode ? htmlspecialchars($edit_data['nama_lokasi']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat:</label>
                    <input type="text" id="alamat" name="alamat" required 
                           value="<?= $edit_mode ? htmlspecialchars($edit_data['alamat']) : ''; ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" name="<?= $edit_mode ? 'update' : 'submit'; ?>" class="btn btn-primary">
                        <?= $edit_mode ? 'Update Lokasi' : 'Tambah Lokasi'; ?>
                    </button>
                    <?php if ($edit_mode): ?>
                        <a href="lokasi_aset.php" class="btn btn-secondary">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <!-- Tabel Daftar Lokasi -->
        <section class="aset-list">
            <h3>Daftar Lokasi Aset</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Lokasi</th>
                            <th>Alamat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): $no = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($row['nama_lokasi']); ?></td>
                                    <td><?= htmlspecialchars($row['alamat']); ?></td>
                                    <td class="aksi">
                                        <a href="?edit=<?= $row['id_lokasi'] ?>" class="btn-icon edit" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="?hapus=<?= $row['id_lokasi'] ?>" class="btn-icon delete" title="Hapus" 
                                           onclick="return confirm('Yakin ingin menghapus lokasi ini?');">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4">Tidak ada lokasi aset yang terdaftar.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Sistem Manajemen Aset Kampus</p>
    </footer>
</div>

<?php include('../include/footer.php'); ?>
<?php mysqli_close($conn); ?>