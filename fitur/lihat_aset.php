<?php
session_start();
include('../include/koneksi.php');
include('../include/popup_profil.php');

$allowed_roles = ['pimpinan', 'admin', 'staf'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /siman/login.php");
    exit();
}

// Fungsi format Rupiah untuk PHP
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Fungsi untuk format tanggal
function formatTanggal($date) {
    return date('d-m-Y', strtotime($date));
}

// Mendapatkan nilai pencarian jika ada
$cari = isset($_POST['cari']) ? mysqli_real_escape_string($conn, $_POST['cari']) : '';

// Query untuk mengambil data aset, dengan filter pencarian
$query = "SELECT aset.id_aset, aset.nama_aset, kategori.nama_kategori, lokasi.nama_lokasi, 
                 aset.tanggal_perolehan, aset.nilai_awal, aset.status 
          FROM aset 
          JOIN kategori ON aset.kategori_id = kategori.id_kategori 
          JOIN lokasi ON aset.lokasi_id = lokasi.id_lokasi
          WHERE aset.nama_aset LIKE '%$cari%' 
             OR kategori.nama_kategori LIKE '%$cari%' 
             OR lokasi.nama_lokasi LIKE '%$cari%'
          ORDER BY aset.nama_aset ASC";

$result = mysqli_query($conn, $query);
?>

<?php include('../include/header.php'); ?>
<?php 
if ($_SESSION['role'] === 'admin') {
    include('../include/sidebar_admin.php');
} elseif ($_SESSION['role'] === 'staf') {
    include('../include/sidebar_staf.php');
} elseif ($_SESSION['role'] === 'pimpinan') {
    include('../include/sidebar_pimpinan.php');
}
?>

<!-- Konten Utama -->
<div class="main-content">
    <header>
        <h2>Daftar Aset</h2>
    </header>

    <main>
        <section class="aset-list">
            <h3>Daftar Aset yang Tersedia</h3>
            
            <!-- Form Pencarian -->
            <form method="POST" action="" class="search-form">
                <div class="form-group">
                    <input type="text" name="cari" placeholder="Cari aset..." value="<?= htmlspecialchars($cari) ?>" 
                           class="search-input">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </form>

            <!-- Tabel Aset -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID Aset</th>
                            <th>Nama Aset</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Tanggal Perolehan</th>
                            <th>Nilai Awal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id_aset']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_aset']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_lokasi']) ?></td>
                                    <td><?= formatTanggal($row['tanggal_perolehan']) ?></td>
                                    <td><?= formatRupiah($row['nilai_awal']) ?></td>
                                    <td><?= htmlspecialchars($row['status']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">Tidak ada data aset yang ditemukan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="form-actions">
                <button onclick="window.history.back()" class="btn btn-secondary">Kembali</button>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Sistem Manajemen Aset Kampus</p>
    </footer>
</div>

<?php include('../include/footer.php'); ?>
<?php mysqli_close($conn); ?>