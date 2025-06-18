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

// Handle search functionality
$search_term = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Ambil aset yang belum disusutkan tahun ini (only if not searching)
if (empty($search_term)) {
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
}

// Ambil data penyusutan dengan menambahkan tahun perolehan, garansi, dan ruangan
$query_penyusutan = "
    SELECT p.*, a.nama_aset, a.nilai_awal, a.masa_manfaat, a.tanggal_perolehan, 
           a.jenis_garansi, a.garansi_berakhir, a.penyedia_garansi, a.nomor_garansi,
           k.nama_kategori AS kategori, l.nama_lokasi AS lokasi,
           r.nama_ruangan AS ruangan
    FROM penyusutan p
    JOIN aset a ON p.id_aset = a.id_aset
    JOIN kategori k ON a.kategori_id = k.id_kategori
    JOIN lokasi l ON a.lokasi_id = l.id_lokasi
    LEFT JOIN ruangan r ON a.ruangan_id = r.id_ruangan
    WHERE 1=1
";

// Add search conditions if search term exists
if (!empty($search_term)) {
    $query_penyusutan .= "
        AND (
            a.nama_aset LIKE '%$search_term%' OR
            k.nama_kategori LIKE '%$search_term%' OR
            l.nama_lokasi LIKE '%$search_term%' OR
            r.nama_ruangan LIKE '%$search_term%' OR
            a.jenis_garansi LIKE '%$search_term%' OR
            a.penyedia_garansi LIKE '%$search_term%' OR
            a.nomor_garansi LIKE '%$search_term%' OR
            p.tahun LIKE '%$search_term%'
        )
    ";
}

$query_penyusutan .= " ORDER BY a.tanggal_perolehan DESC, a.nama_aset";

$result_penyusutan = mysqli_query($conn, $query_penyusutan);

// Fungsi format Rupiah untuk PHP
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Fungsi format tanggal
function formatTanggal($date) {
    return $date ? date('d-m-Y', strtotime($date)) : '-';
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
        <form method="GET" action="" class="search-form">
            <div class="search-container">
                <input type="text" name="search" id="searchInput" placeholder="Cari aset..." 
                       value="<?= htmlspecialchars($search_term) ?>" class="search-input">
                <button type="submit" class="btn btn-primary">Cari</button>
                <?php if (!empty($search_term)): ?>
                    <a href="?" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>

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
                            <th>Ruangan</th>
                            <th>Tanggal Perolehan</th>
                            <th>Nilai Awal</th>
                            <th>Nilai Penyusutan</th>
                            <th>Nilai Sisa</th>
                            <th>Masa Manfaat</th>
                            <th>Garansi</th>
                            <th>Berlaku Sampai</th>
                            <th>Tahun Penyusutan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_penyusutan) > 0): ?>
                            <?php while ($penyusutan = mysqli_fetch_assoc($result_penyusutan)) : ?>
                            <tr>
                                <td><?= htmlspecialchars($penyusutan['nama_aset']) ?></td>
                                <td><?= htmlspecialchars($penyusutan['kategori']) ?></td>
                                <td><?= htmlspecialchars($penyusutan['lokasi']) ?></td>
                                <td><?= $penyusutan['ruangan'] ? htmlspecialchars($penyusutan['ruangan']) : '-' ?></td>
                                <td><?= formatTanggal($penyusutan['tanggal_perolehan']) ?></td>
                                <td><?= formatRupiah($penyusutan['nilai_awal']) ?></td>
                                <td><?= formatRupiah($penyusutan['nilai_susut']) ?></td>
                                <td><?= formatRupiah($penyusutan['nilai_sisa']) ?></td>
                                <td><?= htmlspecialchars($penyusutan['masa_manfaat']) ?> Tahun</td>
                                <td>
                                    <?php if ($penyusutan['jenis_garansi']): ?>
                                        <?= ucfirst($penyusutan['jenis_garansi']) ?><br>
                                        <small><?= $penyusutan['penyedia_garansi'] ?></small>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= formatTanggal($penyusutan['garansi_berakhir']) ?>
                                    <?php if ($penyusutan['garansi_berakhir'] && strtotime($penyusutan['garansi_berakhir']) < time()): ?>
                                        <span class="badge bg-danger">Expired</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d-m-Y', strtotime($penyusutan['tahun'].'-01-01')) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="12">Tidak ada data ditemukan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

       <!-- <div class="form-actions">
            <button onclick="window.location.href='<?= ($_SESSION['role'] === 'admin') ? '../adm/admin.php' : '../staf/staf.php' ?>'" 
                    class="btn btn-secondary">
                Kembali 
            </button>
        </div>-->
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Sistem Manajemen Aset Kampus</p>
    </footer>
</div>

<script>
    // Enable filtering when pressing Enter in search input
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            this.form.submit();
        }
    });

    // Client-side filtering for better UX
    function filterTable() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        let hasResults = false;
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const match = Array.from(cells).some(cell => 
                cell.textContent.toLowerCase().includes(searchInput)
            );
            
            if (match) {
                row.style.display = '';
                hasResults = true;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show "no results" message if needed
        const noResultsRow = document.querySelector('.no-results');
        if (!hasResults && !noResultsRow) {
            const tbody = document.querySelector('tbody');
            const tr = document.createElement('tr');
            tr.className = 'no-results';
            tr.innerHTML = '<td colspan="12">Tidak ada hasil yang cocok</td>';
            tbody.appendChild(tr);
        } else if (hasResults && noResultsRow) {
            noResultsRow.remove();
        }
    }
</script>

<?php include('../include/footer.php'); ?>
<?php mysqli_close($conn); ?>