<?php
session_start();
include('../include/koneksi.php');
include('../include/popup_profil.php');

$allowed_roles = ['admin', 'staf'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /siman/login.php");
    exit();
}

// Fungsi format Rupiah untuk PHP
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Fungsi bersihkan nilai Rupiah
function bersihkanRupiah($rupiah) {
    return preg_replace('/[^0-9]/', '', $rupiah);
}

// Fungsi tambah aset
if (isset($_POST['tambah_aset'])) {
    $nama_aset = mysqli_real_escape_string($conn, $_POST['nama_aset']);
    $kategori_id = (int)$_POST['kategori_id'];
    $lokasi_id = (int)$_POST['lokasi_id'];
    $tanggal_perolehan = mysqli_real_escape_string($conn, $_POST['tanggal_perolehan']);
    $nilai_awal = bersihkanRupiah($_POST['nilai_awal']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $masa_manfaat = (int)$_POST['masa_manfaat'];

    $query = "INSERT INTO aset (nama_aset, kategori_id, lokasi_id, tanggal_perolehan, nilai_awal, status, masa_manfaat)
              VALUES ('$nama_aset', $kategori_id, $lokasi_id, '$tanggal_perolehan', $nilai_awal, '$status', $masa_manfaat)";

    if (mysqli_query($conn, $query)) {
        $message = "Aset berhasil ditambahkan!";
    } else {
        $message = "Gagal menambahkan aset: " . mysqli_error($conn);
    }
}

// Fungsi edit aset
if (isset($_POST['edit_aset'])) {
    $id_aset = (int)$_POST['id_aset'];
    $nama_aset = mysqli_real_escape_string($conn, $_POST['nama_aset']);
    $kategori_id = (int)$_POST['kategori_id'];
    $lokasi_id = (int)$_POST['lokasi_id'];
    $tanggal_perolehan = mysqli_real_escape_string($conn, $_POST['tanggal_perolehan']);
    $nilai_awal = bersihkanRupiah($_POST['nilai_awal']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $masa_manfaat = (int)$_POST['masa_manfaat'];

    $query = "UPDATE aset SET 
              nama_aset = '$nama_aset', 
              kategori_id = $kategori_id, 
              lokasi_id = $lokasi_id, 
              tanggal_perolehan = '$tanggal_perolehan', 
              nilai_awal = $nilai_awal, 
              status = '$status', 
              masa_manfaat = $masa_manfaat 
              WHERE id_aset = $id_aset";

    if (mysqli_query($conn, $query)) {
        $message = "Aset berhasil diperbarui!";
        unset($_GET['edit_aset']);
    } else {
        $message = "Gagal memperbarui aset: " . mysqli_error($conn);
    }
}

// Fungsi hapus aset
if (isset($_GET['hapus_aset'])) {
    $id_aset = (int)$_GET['hapus_aset'];

    $query = "DELETE FROM aset WHERE id_aset = $id_aset";
    if (mysqli_query($conn, $query)) {
        $message = "Aset berhasil dihapus!";
    } else {
        $message = "Gagal menghapus aset: " . mysqli_error($conn);
    }
}

// Ambil data untuk dropdown
$kategori_list = mysqli_query($conn, "SELECT * FROM kategori");
$lokasi_list = mysqli_query($conn, "SELECT * FROM lokasi");

// Mode edit
if (isset($_GET['edit_aset'])) {
    $id_aset = (int)$_GET['edit_aset'];
    $aset_query = mysqli_query($conn, "SELECT * FROM aset WHERE id_aset = $id_aset");
    $aset = mysqli_fetch_assoc($aset_query);
    
    // Reset pointer untuk dropdown
    mysqli_data_seek($kategori_list, 0);
    mysqli_data_seek($lokasi_list, 0);
}
?>

<?php include('../include/header.php'); ?>
<?php include($_SESSION['role'] === 'admin' ? '../include/sidebar_admin.php' : '../include/sidebar_staf.php'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Konten Utama -->
<div class="main-content">
    <header>
        <h2>Manajemen Aset</h2>
    </header>

    <main>
        <?php if (isset($message)) echo "<div class='alert'>$message</div>"; ?>

        <!-- Form Tambah/Edit Aset -->
        <section class="form-section">
            <h3><?= isset($aset) ? "Edit Aset" : "Tambah Aset"; ?></h3>
            <form method="post" class="asset-form">
                <?php if (isset($aset)) { ?>
                    <input type="hidden" name="id_aset" value="<?= htmlspecialchars($aset['id_aset']); ?>">
                <?php } ?>
                
                <div class="form-group">
                    <label for="nama_aset">Nama Aset:</label>
                    <input type="text" name="nama_aset" required value="<?= isset($aset) ? htmlspecialchars($aset['nama_aset']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="kategori_id">Kategori Aset:</label>
                    <select name="kategori_id" required>
                        <option value="">Pilih Kategori</option>
                        <?php 
                        mysqli_data_seek($kategori_list, 0);
                        while($kategori = mysqli_fetch_assoc($kategori_list)) { 
                        ?>
                            <option value="<?= $kategori['id_kategori'] ?>" <?= isset($aset) && $aset['kategori_id'] == $kategori['id_kategori'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($kategori['nama_kategori']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="lokasi_id">Lokasi Aset:</label>
                    <select name="lokasi_id" required>
                        <option value="">Pilih Lokasi</option>
                        <?php 
                        mysqli_data_seek($lokasi_list, 0);
                        while($lokasi = mysqli_fetch_assoc($lokasi_list)) { 
                        ?>
                            <option value="<?= $lokasi['id_lokasi'] ?>" <?= isset($aset) && $aset['lokasi_id'] == $lokasi['id_lokasi'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($lokasi['nama_lokasi']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tanggal_perolehan">Tanggal Perolehan:</label>
                    <input type="date" name="tanggal_perolehan" required 
                           value="<?= isset($aset) ? htmlspecialchars($aset['tanggal_perolehan']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="nilai_awal">Nilai Awal Aset:</label>
                    <div class="input-rupiah">
                        <input type="text" name="nilai_awal" required 
                               value="<?= isset($aset) ? number_format($aset['nilai_awal'], 0, ',', '.') : ''; ?>"
                               onkeyup="formatRupiah(this)">
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Status:</label>
                    <select name="status" required>
                        <option value="Aktif" <?= isset($aset) && $aset['status'] == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="Tidak Aktif" <?= isset($aset) && $aset['status'] == 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
                        <option value="Dalam Perbaikan" <?= isset($aset) && $aset['status'] == 'Dalam Perbaikan' ? 'selected' : ''; ?>>Dalam Perbaikan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="masa_manfaat">Masa Manfaat (tahun):</label>
                    <input type="number" name="masa_manfaat" required min="1" max="50"
                           value="<?= isset($aset) ? (int)$aset['masa_manfaat'] : ''; ?>">
                </div>

                <div class="form-actions">
                    <?php if (isset($aset)) { ?>
                        <button type="submit" name="edit_aset" class="btn btn-primary">Update Aset</button>
                        <a href="atur_aset.php" class="btn btn-secondary">Batal</a>
                    <?php } else { ?>
                        <button type="submit" name="tambah_aset" class="btn btn-primary">Tambah Aset</button>
                    <?php } ?>
                </div>
            </form>
        </section>

        <!-- Tabel Daftar Aset -->
        <section class="aset-list">
            <h3>Daftar Aset</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Aset</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Tanggal Perolehan</th>
                            <th>Nilai Awal</th>
                            <th>Status</th>
                            <th>Masa Manfaat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result_aset = mysqli_query($conn, "SELECT aset.*, kategori.nama_kategori, lokasi.nama_lokasi 
                                                          FROM aset 
                                                          JOIN kategori ON aset.kategori_id = kategori.id_kategori
                                                          JOIN lokasi ON aset.lokasi_id = lokasi.id_lokasi
                                                          ORDER BY aset.nama_aset ASC");
                        while ($aset_item = mysqli_fetch_assoc($result_aset)) { 
                            $tanggal = date('d-m-Y', strtotime($aset_item['tanggal_perolehan']));
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($aset_item['nama_aset']) ?></td>
                                <td><?= htmlspecialchars($aset_item['nama_kategori']) ?></td>
                                <td><?= htmlspecialchars($aset_item['nama_lokasi']) ?></td>
                                <td><?= $tanggal ?></td>
                                <td><?= formatRupiah($aset_item['nilai_awal']) ?></td>
                                <td><?= htmlspecialchars($aset_item['status']) ?></td>
                                <td><?= (int)$aset_item['masa_manfaat'] ?> Tahun</td>
                                <td class="aksi">
                                    <a href="?edit_aset=<?= $aset_item['id_aset'] ?>" class="btn-icon edit" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="?hapus_aset=<?= $aset_item['id_aset'] ?>" class="btn-icon delete" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus aset ini?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
        </main>
    
    <!-- Kembali Button - Recommended Position -->
    <div class="form-actions text-end mb-4">
        <button onclick="window.location.href='<?= 
            ($_SESSION['role'] === 'admin') ? '../adm/admin.php' : 
            (($_SESSION['role'] === 'pimpinan') ? '../pimpinan/pimpinan.php' : '../staf/staf.php') 
        ?>'" 
        class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
        </button>
    </div>

    <footer>
        <p>&copy; <?= date("Y") ?> Sistem Manajemen Aset Kampus</p>
    </footer>
</div>

<script>
    // Fungsi format Rupiah untuk input
    function formatRupiah(input) {
        // Hapus semua karakter selain angka
        let value = input.value.replace(/\D/g, '');
        
        // Format dengan titik sebagai pemisah ribuan
        if (value.length > 0) {
            value = parseInt(value).toLocaleString('id-ID');
        }
        
        input.value = value;
    }

    // Format semua input Rupiah saat load halaman
    document.addEventListener('DOMContentLoaded', function() {
        const rupiahInputs = document.querySelectorAll('input[name="nilai_awal"]');
        rupiahInputs.forEach(input => {
            if (input.value) {
                let value = input.value.replace(/\D/g, '');
                if (value.length > 0) {
                    input.value = parseInt(value).toLocaleString('id-ID');
                }
            }
        });
    });
</script>

<?php include('../include/footer.php'); ?>