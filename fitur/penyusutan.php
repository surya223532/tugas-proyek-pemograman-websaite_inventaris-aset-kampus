<?php
session_start();
include('../include/koneksi.php');
include('../include/popup_profil.php');

$allowed_roles = ['admin', 'staf'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /siman/login.php");
    exit();
}

$tahun_sekarang = date('Y');

// Ambil aset yang belum disusutkan tahun ini
$query_aset = "
    SELECT * FROM aset 
    WHERE id_aset NOT IN (
        SELECT id_aset FROM penyusutan WHERE tahun = '$tahun_sekarang'
    )
";
$result_aset = mysqli_query($conn, $query_aset);

// Simpan otomatis data penyusutan
while ($aset = mysqli_fetch_assoc($result_aset)) {
    if (!isset($aset['masa_manfaat']) || !$aset['masa_manfaat'] || !$aset['tanggal_perolehan'] || !$aset['nilai_awal']) continue;

    $tahun_perolehan = date('Y', strtotime($aset['tanggal_perolehan']));
    $umur = max(0, $tahun_sekarang - $tahun_perolehan);
    $masa_manfaat = $aset['masa_manfaat'];
    $nilai_awal = $aset['nilai_awal'];
    $nilai_susut = $masa_manfaat > 0 ? $nilai_awal / $masa_manfaat : 0;

    if ($umur >= $masa_manfaat) {
        $nilai_sisa = 0;
    } elseif ($umur > 0) {
        $nilai_sisa = $nilai_awal - ($nilai_susut * $umur);
    } else {
        $nilai_sisa = $nilai_awal;
    }

    $id_aset = $aset['id_aset'];
    $query_simpan = "
        INSERT INTO penyusutan (id_aset, tahun, nilai_susut, nilai_sisa) 
        VALUES ('$id_aset', '$tahun_sekarang', '$nilai_susut', '$nilai_sisa')
    ";
    mysqli_query($conn, $query_simpan);
}

// Ambil data penyusutan dengan menambahkan tahun perolehan
$query_penyusutan = "
    SELECT p.*, a.nama_aset, a.nilai_awal, a.masa_manfaat, a.tanggal_perolehan, 
           YEAR(a.tanggal_perolehan) AS tahun_perolehan,
           k.nama_kategori AS kategori, l.nama_lokasi AS lokasi
    FROM penyusutan p
    JOIN aset a ON p.id_aset = a.id_aset
    JOIN kategori k ON a.kategori_id = k.id_kategori
    JOIN lokasi l ON a.lokasi_id = l.id_lokasi
    ORDER BY a.tanggal_perolehan DESC, a.nama_aset
";
$result_penyusutan = mysqli_query($conn, $query_penyusutan);

// Fungsi format Rupiah untuk PHP
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>

<?php include('../include/header.php'); ?>
<?php include($_SESSION['role'] === 'admin' ? '../include/sidebar_admin.php' : '../include/sidebar_staf.php'); ?>

<!-- Konten Utama -->
<div class="main-content">
    <header>
        <h2>Kelola Penyusutan Aset</h2>
    </header>

    <main>
        <!-- Pencarian -->
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Cari aset..." class="search-input">
            <button type="button" onclick="filterTable()" class="btn btn-primary">Cari</button>
        </div>

        <!-- Tabel Daftar Penyusutan -->
        <section class="aset-list">
            <h3>Daftar Penyusutan Aset</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Aset</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Tahun Perolehan</th>
                            <th>Nilai Awal</th>
                            <th>Nilai Penyusutan</th>
                            <th>Nilai Sisa</th>
                            <th>Masa Manfaat</th>
                            <th>Tahun Penyusutan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($penyusutan = mysqli_fetch_assoc($result_penyusutan)) : ?>
                        <tr>
                            <td><?= htmlspecialchars($penyusutan['nama_aset']) ?></td>
                            <td><?= htmlspecialchars($penyusutan['kategori']) ?></td>
                            <td><?= htmlspecialchars($penyusutan['lokasi']) ?></td>
                            <td><?= $penyusutan['tahun_perolehan'] ?></td>
                            <td><?= formatRupiah($penyusutan['nilai_awal']) ?></td>
                            <td><?= formatRupiah($penyusutan['nilai_susut']) ?></td>
                            <td><?= formatRupiah($penyusutan['nilai_sisa']) ?></td>
                            <td><?= htmlspecialchars($penyusutan['masa_manfaat']) ?> Tahun</td>
                            <td><?= $penyusutan['tahun'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="form-actions">
            <button onclick="window.location.href='<?= ($_SESSION['role'] === 'admin') ? '../adm/admin.php' : '../staf/staf.php' ?>'" 
                    class="btn btn-secondary">
                Kembali ke Dashboard
            </button>
        </div>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Sistem Manajemen Aset Kampus</p>
    </footer>
</div>

<script>
    function filterTable() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const match = Array.from(cells).some(cell => 
                cell.textContent.toLowerCase().includes(searchInput)
            );
            row.style.display = match ? '' : 'none';
        });
    }
    
    // Enable filtering when pressing Enter in search input
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            filterTable();
        }
    });
</script>

<?php include('../include/footer.php'); ?>
<?php mysqli_close($conn); ?>