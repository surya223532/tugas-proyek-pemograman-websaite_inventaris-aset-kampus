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
if (isset($_POST['submit_lokasi'])) {
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
if (isset($_GET['hapus_lokasi'])) {
    $id = (int)$_GET['hapus_lokasi'];
    $query = "DELETE FROM lokasi WHERE id_lokasi = $id";
    if (mysqli_query($conn, $query)) {
        $message = "Lokasi aset berhasil dihapus!";
    } else {
        $message = "Gagal menghapus lokasi: " . mysqli_error($conn);
    }
}

// Edit lokasi (proses update)
if (isset($_POST['update_lokasi'])) {
    $id_lokasi = (int)$_POST['id_lokasi'];
    $nama_lokasi = mysqli_real_escape_string($conn, $_POST['nama_lokasi']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    $query = "UPDATE lokasi SET nama_lokasi = '$nama_lokasi', alamat = '$alamat' WHERE id_lokasi = $id_lokasi";
    if (mysqli_query($conn, $query)) {
        $message = "Data lokasi berhasil diperbarui.";
        unset($_GET['edit_lokasi']);
    } else {
        $message = "Gagal memperbarui lokasi: " . mysqli_error($conn);
    }
}

// Tambah ruangan
if (isset($_POST['submit_ruangan'])) {
    $nama_ruangan = mysqli_real_escape_string($conn, $_POST['nama_ruangan']);
    $id_lokasi = (int)$_POST['id_lokasi'];
    $kapasitas = (int)$_POST['kapasitas'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    if (!empty($nama_ruangan) && !empty($id_lokasi)) {
        $query = "INSERT INTO ruangan (nama_ruangan, id_lokasi, kapasitas, keterangan) 
                 VALUES ('$nama_ruangan', $id_lokasi, $kapasitas, '$keterangan')";
        if (mysqli_query($conn, $query)) {
            $message = "Ruangan berhasil ditambahkan!";
        } else {
            $message = "Error: " . mysqli_error($conn);
        }
    } else {
        $message = "Nama ruangan dan lokasi wajib diisi.";
    }
}

// Hapus ruangan
if (isset($_GET['hapus_ruangan'])) {
    $id = (int)$_GET['hapus_ruangan'];
    $query = "DELETE FROM ruangan WHERE id_ruangan = $id";
    if (mysqli_query($conn, $query)) {
        $message = "Ruangan berhasil dihapus!";
    } else {
        $message = "Gagal menghapus ruangan: " . mysqli_error($conn);
    }
}

// Edit ruangan (proses update)
if (isset($_POST['update_ruangan'])) {
    $id_ruangan = (int)$_POST['id_ruangan'];
    $nama_ruangan = mysqli_real_escape_string($conn, $_POST['nama_ruangan']);
    $id_lokasi = (int)$_POST['id_lokasi'];
    $kapasitas = (int)$_POST['kapasitas'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $query = "UPDATE ruangan SET 
              nama_ruangan = '$nama_ruangan', 
              id_lokasi = $id_lokasi, 
              kapasitas = $kapasitas, 
              keterangan = '$keterangan' 
              WHERE id_ruangan = $id_ruangan";
    
    if (mysqli_query($conn, $query)) {
        $message = "Data ruangan berhasil diperbarui.";
        unset($_GET['edit_ruangan']);
    } else {
        $message = "Gagal memperbarui ruangan: " . mysqli_error($conn);
    }
}

// Ambil data lokasi untuk edit (jika ada parameter edit)
$edit_lokasi_mode = false;
$edit_lokasi_data = null;
if (isset($_GET['edit_lokasi'])) {
    $edit_lokasi_mode = true;
    $id = (int)$_GET['edit_lokasi'];
    $query = "SELECT * FROM lokasi WHERE id_lokasi = $id";
    $result_edit = mysqli_query($conn, $query);
    $edit_lokasi_data = mysqli_fetch_assoc($result_edit);
}

// Ambil data ruangan untuk edit (jika ada parameter edit)
$edit_ruangan_mode = false;
$edit_ruangan_data = null;
if (isset($_GET['edit_ruangan'])) {
    $edit_ruangan_mode = true;
    $id = (int)$_GET['edit_ruangan'];
    $query = "SELECT * FROM ruangan WHERE id_ruangan = $id";
    $result_edit = mysqli_query($conn, $query);
    $edit_ruangan_data = mysqli_fetch_assoc($result_edit);
}

// Ambil semua data lokasi
$query_lokasi = "SELECT * FROM lokasi ORDER BY nama_lokasi ASC";
$result_lokasi = mysqli_query($conn, $query_lokasi);

// Ambil semua data ruangan dengan join ke tabel lokasi
$query_ruangan = "SELECT r.*, l.nama_lokasi 
                  FROM ruangan r 
                  JOIN lokasi l ON r.id_lokasi = l.id_lokasi 
                  ORDER BY r.nama_ruangan ASC";
$result_ruangan = mysqli_query($conn, $query_ruangan);
?>

<?php include('../include/header.php'); ?>
<?php include($_SESSION['role'] === 'admin' ? '../include/sidebar_admin.php' : '../include/sidebar_staf.php'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Konten Utama -->
<div class="main-content">
    <header>
        <h2>Manajemen Lokasi dan Ruangan Aset</h2>
    </header>

    <main>
        <?php if (!empty($message)) echo "<div class='alert'>$message</div>"; ?>

        <!-- Form Tambah/Edit Lokasi -->
        <section class="form-section">
            <h3><?= $edit_lokasi_mode ? "Edit Lokasi" : "Tambah Lokasi"; ?></h3>
            <form method="post" class="asset-form">
                <?php if ($edit_lokasi_mode): ?>
                    <input type="hidden" name="id_lokasi" value="<?= htmlspecialchars($edit_lokasi_data['id_lokasi']); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="nama_lokasi">Nama Lokasi:</label>
                    <input type="text" id="nama_lokasi" name="nama_lokasi" required 
                           value="<?= $edit_lokasi_mode ? htmlspecialchars($edit_lokasi_data['nama_lokasi']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat:</label>
                    <input type="text" id="alamat" name="alamat" required 
                           value="<?= $edit_lokasi_mode ? htmlspecialchars($edit_lokasi_data['alamat']) : ''; ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" name="<?= $edit_lokasi_mode ? 'update_lokasi' : 'submit_lokasi'; ?>" class="btn btn-primary">
                        <?= $edit_lokasi_mode ? 'Update Lokasi' : 'Tambah Lokasi'; ?>
                    </button>
                    <?php if ($edit_lokasi_mode): ?>
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
                        <?php if (mysqli_num_rows($result_lokasi) > 0): $no = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($result_lokasi)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($row['nama_lokasi']); ?></td>
                                    <td><?= htmlspecialchars($row['alamat']); ?></td>
                                    <td class="aksi">
                                        <a href="?edit_lokasi=<?= $row['id_lokasi'] ?>" class="btn-icon edit" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="?hapus_lokasi=<?= $row['id_lokasi'] ?>" class="btn-icon delete" title="Hapus" 
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

        <!-- Form Tambah/Edit Ruangan -->
        <section class="form-section">
            <h3><?= $edit_ruangan_mode ? "Edit Ruangan" : "Tambah Ruangan"; ?></h3>
            <form method="post" class="asset-form">
                <?php if ($edit_ruangan_mode): ?>
                    <input type="hidden" name="id_ruangan" value="<?= htmlspecialchars($edit_ruangan_data['id_ruangan']); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="nama_ruangan">Nama Ruangan:</label>
                    <input type="text" id="nama_ruangan" name="nama_ruangan" required 
                           value="<?= $edit_ruangan_mode ? htmlspecialchars($edit_ruangan_data['nama_ruangan']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="id_lokasi">Lokasi:</label>
                    <select id="id_lokasi" name="id_lokasi" required>
                        <option value="">-- Pilih Lokasi --</option>
                        <?php 
                        mysqli_data_seek($result_lokasi, 0); // Reset pointer result lokasi
                        while ($lokasi = mysqli_fetch_assoc($result_lokasi)): 
                        ?>
                            <option value="<?= $lokasi['id_lokasi']; ?>"
                                <?= ($edit_ruangan_mode && $edit_ruangan_data['id_lokasi'] == $lokasi['id_lokasi']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($lokasi['nama_lokasi']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="kapasitas">Kapasitas:</label>
                    <input type="number" id="kapasitas" name="kapasitas" 
                           value="<?= $edit_ruangan_mode ? htmlspecialchars($edit_ruangan_data['kapasitas']) : '0'; ?>">
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan:</label>
                    <textarea id="keterangan" name="keterangan"><?= $edit_ruangan_mode ? htmlspecialchars($edit_ruangan_data['keterangan']) : ''; ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" name="<?= $edit_ruangan_mode ? 'update_ruangan' : 'submit_ruangan'; ?>" class="btn btn-primary">
                        <?= $edit_ruangan_mode ? 'Update Ruangan' : 'Tambah Ruangan'; ?>
                    </button>
                    <?php if ($edit_ruangan_mode): ?>
                        <a href="lokasi_aset.php" class="btn btn-secondary">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <!-- Tabel Daftar Ruangan -->
        <section class="aset-list">
            <h3>Daftar Ruangan</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Ruangan</th>
                            <th>Lokasi</th>
                            <th>Kapasitas</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_ruangan) > 0): $no = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($result_ruangan)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($row['nama_ruangan']); ?></td>
                                    <td><?= htmlspecialchars($row['nama_lokasi']); ?></td>
                                    <td><?= htmlspecialchars($row['kapasitas']); ?></td>
                                    <td><?= htmlspecialchars($row['keterangan']); ?></td>
                                    <td class="aksi">
                                        <a href="?edit_ruangan=<?= $row['id_ruangan'] ?>" class="btn-icon edit" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="?hapus_ruangan=<?= $row['id_ruangan'] ?>" class="btn-icon delete" title="Hapus" 
                                           onclick="return confirm('Yakin ingin menghapus ruangan ini?');">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6">Tidak ada ruangan yang terdaftar.</td></tr>
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