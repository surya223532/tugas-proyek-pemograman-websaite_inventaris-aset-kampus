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

// Fungsi tambah aset dengan garansi
if (isset($_POST['tambah_aset'])) {
    $nama_aset = mysqli_real_escape_string($conn, $_POST['nama_aset']);
    $kategori_id = (int)$_POST['kategori_id'];
    $lokasi_id = (int)$_POST['lokasi_id'];
    $ruangan_id = !empty($_POST['ruangan_id']) ? (int)$_POST['ruangan_id'] : NULL;
    $tanggal_perolehan = mysqli_real_escape_string($conn, $_POST['tanggal_perolehan']);
    $nilai_awal = bersihkanRupiah($_POST['nilai_awal']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $masa_manfaat = (int)$_POST['masa_manfaat'];
    $jenis_garansi = isset($_POST['jenis_garansi']) ? mysqli_real_escape_string($conn, $_POST['jenis_garansi']) : NULL;
    $garansi_berakhir = isset($_POST['garansi_berakhir']) ? mysqli_real_escape_string($conn, $_POST['garansi_berakhir']) : NULL;
    $penyedia_garansi = isset($_POST['penyedia_garansi']) ? mysqli_real_escape_string($conn, $_POST['penyedia_garansi']) : NULL;
    $nomor_garansi = isset($_POST['nomor_garansi']) ? mysqli_real_escape_string($conn, $_POST['nomor_garansi']) : NULL;

    $query = "INSERT INTO aset (nama_aset, kategori_id, lokasi_id, ruangan_id, tanggal_perolehan, nilai_awal, status, masa_manfaat, jenis_garansi, garansi_berakhir, penyedia_garansi, nomor_garansi)
              VALUES ('$nama_aset', $kategori_id, $lokasi_id, " . ($ruangan_id ? "$ruangan_id" : "NULL") . ", '$tanggal_perolehan', $nilai_awal, '$status', $masa_manfaat, " .
              ($jenis_garansi ? "'$jenis_garansi'" : "NULL") . ", " .
              ($garansi_berakhir ? "'$garansi_berakhir'" : "NULL") . ", " .
              ($penyedia_garansi ? "'$penyedia_garansi'" : "NULL") . ", " .
              ($nomor_garansi ? "'$nomor_garansi'" : "NULL") . ")";

    if (mysqli_query($conn, $query)) {
        $message = "Aset berhasil ditambahkan!";
    } else {
        $message = "Gagal menambahkan aset: " . mysqli_error($conn);
    }
}

// Fungsi edit aset dengan garansi
if (isset($_POST['edit_aset'])) {
    $id_aset = (int)$_POST['id_aset'];
    $nama_aset = mysqli_real_escape_string($conn, $_POST['nama_aset']);
    $kategori_id = (int)$_POST['kategori_id'];
    $lokasi_id = (int)$_POST['lokasi_id'];
    $ruangan_id = !empty($_POST['ruangan_id']) ? (int)$_POST['ruangan_id'] : NULL;
    $tanggal_perolehan = mysqli_real_escape_string($conn, $_POST['tanggal_perolehan']);
    $nilai_awal = bersihkanRupiah($_POST['nilai_awal']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $masa_manfaat = (int)$_POST['masa_manfaat'];
    $jenis_garansi = isset($_POST['jenis_garansi']) ? mysqli_real_escape_string($conn, $_POST['jenis_garansi']) : NULL;
    $garansi_berakhir = isset($_POST['garansi_berakhir']) ? mysqli_real_escape_string($conn, $_POST['garansi_berakhir']) : NULL;
    $penyedia_garansi = isset($_POST['penyedia_garansi']) ? mysqli_real_escape_string($conn, $_POST['penyedia_garansi']) : NULL;
    $nomor_garansi = isset($_POST['nomor_garansi']) ? mysqli_real_escape_string($conn, $_POST['nomor_garansi']) : NULL;

    $query = "UPDATE aset SET 
              nama_aset = '$nama_aset', 
              kategori_id = $kategori_id, 
              lokasi_id = $lokasi_id, 
              ruangan_id = " . ($ruangan_id ? "$ruangan_id" : "NULL") . ", 
              tanggal_perolehan = '$tanggal_perolehan', 
              nilai_awal = $nilai_awal, 
              status = '$status', 
              masa_manfaat = $masa_manfaat,
              jenis_garansi = " . ($jenis_garansi ? "'$jenis_garansi'" : "NULL") . ",
              garansi_berakhir = " . ($garansi_berakhir ? "'$garansi_berakhir'" : "NULL") . ",
              penyedia_garansi = " . ($penyedia_garansi ? "'$penyedia_garansi'" : "NULL") . ",
              nomor_garansi = " . ($nomor_garansi ? "'$nomor_garansi'" : "NULL") . "
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
    
    // Ambil daftar ruangan berdasarkan lokasi yang dipilih
    if ($aset['lokasi_id']) {
        $ruangan_list = mysqli_query($conn, "SELECT * FROM ruangan WHERE id_lokasi = " . $aset['lokasi_id']);
    }
    
    // Reset pointer untuk dropdown
    mysqli_data_seek($kategori_list, 0);
    mysqli_data_seek($lokasi_list, 0);
}
?>

<?php include('../include/header.php'); ?>
<?php include($_SESSION['role'] === 'admin' ? '../include/sidebar_admin.php' : '../include/sidebar_staf.php'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/atur_aset.css">
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
                    <select name="lokasi_id" id="lokasi_id" required onchange="updateRuanganDropdown(this.value)">
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
                    <label for="ruangan_id">Ruangan:</label>
                    <select name="ruangan_id" id="ruangan_id">
                        <option value="">Pilih Ruangan</option>
                        <?php 
                        if (isset($aset) && $aset['lokasi_id']) {
                            $ruangan_query = mysqli_query($conn, "SELECT * FROM ruangan WHERE id_lokasi = " . $aset['lokasi_id']);
                            while($ruangan = mysqli_fetch_assoc($ruangan_query)) {
                        ?>
                            <option value="<?= $ruangan['id_ruangan'] ?>" <?= isset($aset) && $aset['ruangan_id'] == $ruangan['id_ruangan'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($ruangan['nama_ruangan']) ?>
                            </option>
                        <?php 
                            }
                        }
                        ?>
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
                        <option value="Hilang" <?= isset($aset) && $aset['status'] == 'Hilang' ? 'selected' : ''; ?>>Hilang</option>
                        <option value="Rusak" <?= isset($aset) && $aset['status'] == 'Rusak' ? 'selected' : ''; ?>>Rusak</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="masa_manfaat">Masa Manfaat (tahun):</label>
                    <input type="number" name="masa_manfaat" required min="1" max="50"
                           value="<?= isset($aset) ? (int)$aset['masa_manfaat'] : ''; ?>">
                </div>

                <!-- Form Garansi -->
                <div class="form-group">
                    <label for="jenis_garansi">Jenis Garansi:</label>
                    <select name="jenis_garansi" id="jenis_garansi" class="form-control">
                        <option value="">Pilih Jenis Garansi</option>
                        <option value="full" <?= isset($aset) && $aset['jenis_garansi'] == 'full' ? 'selected' : '' ?>>Full Warranty</option>
                        <option value="limited" <?= isset($aset) && $aset['jenis_garansi'] == 'limited' ? 'selected' : '' ?>>Limited Warranty</option>
                        <option value="extended" <?= isset($aset) && $aset['jenis_garansi'] == 'extended' ? 'selected' : '' ?>>Extended Warranty</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="garansi_berakhir">Tanggal Berakhir Garansi:</label>
                    <input type="date" name="garansi_berakhir" id="garansi_berakhir" 
                           value="<?= isset($aset) ? htmlspecialchars($aset['garansi_berakhir']) : '' ?>" 
                           class="form-control">
                </div>

                <div class="form-group">
                    <label for="penyedia_garansi">Penyedia Garansi:</label>
                    <input type="text" name="penyedia_garansi" id="penyedia_garansi"
                           value="<?= isset($aset) ? htmlspecialchars($aset['penyedia_garansi']) : '' ?>"
                           class="form-control">
                </div>

                <div class="form-group">
                    <label for="nomor_garansi">Nomor Garansi:</label>
                    <input type="text" name="nomor_garansi" id="nomor_garansi"
                           value="<?= isset($aset) ? htmlspecialchars($aset['nomor_garansi']) : '' ?>"
                           class="form-control">
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
                            <th>Ruangan</th>
                            <th>Tanggal Perolehan</th>
                            <th>Nilai Awal</th>
                            <th>Status</th>
                            <th>Garansi</th>
                            <th>Berlaku Sampai</th>
                            <th>Masa Manfaat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result_aset = mysqli_query($conn, "SELECT aset.*, kategori.nama_kategori, lokasi.nama_lokasi, ruangan.nama_ruangan 
                                                          FROM aset 
                                                          JOIN kategori ON aset.kategori_id = kategori.id_kategori
                                                          JOIN lokasi ON aset.lokasi_id = lokasi.id_lokasi
                                                          LEFT JOIN ruangan ON aset.ruangan_id = ruangan.id_ruangan
                                                          ORDER BY aset.nama_aset ASC");
                        while ($aset_item = mysqli_fetch_assoc($result_aset)) { 
                            $tanggal = date('d-m-Y', strtotime($aset_item['tanggal_perolehan']));
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($aset_item['nama_aset']) ?></td>
                                <td><?= htmlspecialchars($aset_item['nama_kategori']) ?></td>
                                <td><?= htmlspecialchars($aset_item['nama_lokasi']) ?></td>
                                <td><?= $aset_item['nama_ruangan'] ? htmlspecialchars($aset_item['nama_ruangan']) : '-' ?></td>
                                <td><?= $tanggal ?></td>
                                <td><?= formatRupiah($aset_item['nilai_awal']) ?></td>
                                <td><?= htmlspecialchars($aset_item['status']) ?></td>
                                <td>
                                    <?php if ($aset_item['jenis_garansi']): ?>
                                        <?= ucfirst($aset_item['jenis_garansi']) ?><br>
                                        <small><?= $aset_item['penyedia_garansi'] ?></small>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $aset_item['garansi_berakhir'] ? date('d-m-Y', strtotime($aset_item['garansi_berakhir'])) : '-' ?>
                                    <?php if ($aset_item['garansi_berakhir'] && strtotime($aset_item['garansi_berakhir']) < time()): ?>
                                        <span class="badge bg-danger">Expired</span>
                                    <?php endif; ?>
                                </td>
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
    
    <!-- Kembali Button -->
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

    // Fungsi untuk update dropdown ruangan berdasarkan lokasi yang dipilih
    function updateRuanganDropdown(lokasiId, selectedId = null) {
        const ruanganDropdown = document.getElementById('ruangan_id');
        
        // Kosongkan dropdown terlebih dahulu
        ruanganDropdown.innerHTML = '<option value="">Memuat ruangan...</option>';
        
        if (!lokasiId) {
            ruanganDropdown.innerHTML = '<option value="">Pilih lokasi terlebih dahulu</option>';
            return;
        }

        fetch(`get_ruangan.php?lokasi_id=${lokasiId}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                ruanganDropdown.innerHTML = '<option value="">Pilih Ruangan</option>';
                
                data.forEach(ruangan => {
                    const option = document.createElement('option');
                    option.value = ruangan.id;
                    option.textContent = ruangan.nama;
                    if (selectedId && ruangan.id == selectedId) {
                        option.selected = true;
                    }
                    ruanganDropdown.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                ruanganDropdown.innerHTML = '<option value="">Gagal memuat data ruangan</option>';
            });
    }

    // Panggil fungsi saat halaman dimuat jika lokasi sudah dipilih (mode edit)
    document.addEventListener('DOMContentLoaded', function() {
        const lokasiDropdown = document.getElementById('lokasi_id');
        const ruanganId = <?= isset($aset) && isset($aset['ruangan_id']) ? $aset['ruangan_id'] : 'null' ?>;
        
        if (lokasiDropdown.value) {
            updateRuanganDropdown(lokasiDropdown.value, ruanganId);
        }
        
        // Tambahkan event listener untuk perubahan lokasi
        lokasiDropdown.addEventListener('change', function() {
            updateRuanganDropdown(this.value);
        });
    });

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