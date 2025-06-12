<?php
session_start();
include('../include/koneksi.php');

$tahun_sekarang = date('Y');

// Fetch aset yang belum disusutkan tahun ini
$query_aset = "
    SELECT * FROM aset 
    WHERE id_aset NOT IN (
        SELECT id_aset FROM penyusutan WHERE tahun = '$tahun_sekarang'
    )
";
$result_aset = mysqli_query($conn, $query_aset);

// Automatically save depreciation for each asset
while ($aset = mysqli_fetch_assoc($result_aset)) {
    if (!isset($aset['masa_manfaat']) || !$aset['masa_manfaat'] || !$aset['tanggal_perolehan'] || !$aset['nilai_awal']) continue;

    $tahun_perolehan = date('Y', strtotime($aset['tanggal_perolehan']));
    $umur = max(0, $tahun_sekarang - $tahun_perolehan);
    $masa_manfaat = $aset['masa_manfaat'];
    $nilai_awal = $aset['nilai_awal'];
    $nilai_susut = $masa_manfaat > 0 ? $nilai_awal / $masa_manfaat : 0;

    // Calculate remaining value
    if ($umur >= $masa_manfaat) {
        $nilai_sisa = 0;
    } elseif ($umur > 0) {
        $nilai_sisa = $nilai_awal - ($nilai_susut * $umur);
    } else {
        $nilai_sisa = $nilai_awal;
    }

    // Save depreciation data
    $id_aset = $aset['id_aset'];
    $query_simpan = "
        INSERT INTO penyusutan (id_aset, tahun, nilai_susut, nilai_sisa) 
        VALUES ('$id_aset', '$tahun_sekarang', '$nilai_susut', '$nilai_sisa')
    ";
    mysqli_query($conn, $query_simpan);
}

// Fetch saved depreciation data
$query_penyusutan = "
    SELECT p.*, a.nama_aset, a.nilai_awal, a.masa_manfaat, k.nama_kategori AS kategori, l.nama_lokasi AS lokasi
    FROM penyusutan p
    JOIN aset a ON p.id_aset = a.id_aset
    JOIN kategori k ON a.kategori_id = k.id_kategori
    JOIN lokasi l ON a.lokasi_id = l.id_lokasi
    ORDER BY p.tahun DESC, a.nama_aset
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
    <button onclick="window.location.href='<?= $dashboard ?>'">Dashboard</button>
</header>
<main>
    <h3>Daftar Penyusutan Aset</h3>
    <form onsubmit="event.preventDefault(); filterTable();">
        <input type="text" id="searchInput" placeholder="Cari aset...">
        <button type="submit">Cari</button>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nama Aset</th>
                    <th>Kategori</th>
                    <th>Lokasi</th>
                    <th>Tahun</th>
                    <th>Nilai Awal</th>
                    <th>Nilai Penyusutan</th>
                    <th>Nilai Sisa</th>
                    <th>Masa Manfaat (tahun)</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($penyusutan = mysqli_fetch_assoc($result_penyusutan)) : ?>
            <tr>
                <td><?= htmlspecialchars($penyusutan['nama_aset']) ?></td>
                <td><?= htmlspecialchars($penyusutan['kategori']) ?></td>
                <td><?= htmlspecialchars($penyusutan['lokasi']) ?></td>
                <td><?= $penyusutan['tahun'] ?></td>
                <td>Rp<?= number_format($penyusutan['nilai_awal'], 2, ',', '.') ?></td>
                <td>Rp<?= number_format($penyusutan['nilai_susut'], 2, ',', '.') ?></td>
                <td>Rp<?= number_format($penyusutan['nilai_sisa'], 2, ',', '.') ?></td>
                <td><?= htmlspecialchars($penyusutan['masa_manfaat']) ?> tahun</td>
            </tr>
            <?php endwhile ?>
            </tbody>
        </table>
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
