<?php
session_start();
include('../include/koneksi.php');

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

$dashboard = ($_SESSION['role'] === 'admin') ? '../adm/admin.php' : '../staf/staf.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Penyusutan</title>
    <link rel="stylesheet" href="../assets/penyu.css">
</head>
<body>
<header>
    <h2>Kelola Penyusutan</h2>
</header>

<main>
    <h3>Daftar Penyusutan Aset</h3>

    <!-- Pencarian -->
    <form onsubmit="event.preventDefault(); filterTable();">
        <input type="text" id="searchInput" placeholder="Cari aset...">
        <button type="submit">Cari</button>
    </form>

    <!-- Tabel -->
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
                    <th>Masa Manfaat (tahun)</th>
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
                <td>Rp<?= number_format($penyusutan['nilai_awal'], 2, ',', '.') ?></td>
                <td>Rp<?= number_format($penyusutan['nilai_susut'], 2, ',', '.') ?></td>
                <td>Rp<?= number_format($penyusutan['nilai_sisa'], 2, ',', '.') ?></td>
                <td><?= htmlspecialchars($penyusutan['masa_manfaat']) ?> tahun</td>
                <td><?= $penyusutan['tahun'] ?></td>
            </tr>
            <?php endwhile ?>
            </tbody>
        </table>
    </div>
    <div class="dashboard-container">
        <button onclick="window.location.href='<?= $dashboard ?>'">Kembali</button>
    </div>
</main>
<footer>
    &copy; <?= date('Y') ?> Sistem Informasi Manajemen Aset
</footer>
<script>
    function filterTable() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const match = Array.from(cells).some(cell => cell.textContent.toLowerCase().includes(searchInput));
            row.style.display = match ? '' : 'none';
        });
    }
</script>
</body>
</html>