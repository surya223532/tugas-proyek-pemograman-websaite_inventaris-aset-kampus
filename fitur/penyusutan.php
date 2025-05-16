<?php
session_start();
include('../include/koneksi.php');

$tahun_sekarang = date('Y');

// Tabel 1: Aset yang belum pernah disusutkan tahun ini
$query_aset = "
    SELECT * FROM aset 
    WHERE id_aset NOT IN (
        SELECT id_aset FROM penyusutan WHERE tahun = '$tahun_sekarang'
    )
    ORDER BY nama_aset
";
$result_aset = mysqli_query($conn, $query_aset);

// Tabel 2: Penyusutan yang telah disimpan (termasuk kategori dan lokasi)
$query_penyusutan = "
    SELECT p.*, a.nama_aset, a.nilai_awal, a.masa_manfaat, k.nama_kategori AS kategori, l.nama_lokasi AS lokasi
    FROM penyusutan p
    JOIN aset a ON p.id_aset = a.id_aset
    JOIN kategori k ON a.kategori_id = k.id_kategori
    JOIN lokasi l ON a.lokasi_id = l.id_lokasi
    ORDER BY p.tahun DESC, a.nama_aset
";

$result_penyusutan = mysqli_query($conn, $query_penyusutan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Penyusutan</title>
    <link rel="stylesheet" href="../assets/penyusutan.css">
</head>
<body>
<div class="main-content">
    <h2>📉 Kelola Penyusutan Aset</h2>
    <a href="../adm/admin.php" class="btn-kembali">⬅️ Kembali ke Dashboard</a>

    <!-- Tabel 1: Kelola Penyusutan -->
    <h3>Aset Belum Disusutkan (<?= $tahun_sekarang ?>)</h3>
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Nama Aset</th>
                <th>Tanggal Perolehan</th>
                <th>Nilai Awal</th>
                <th>Nilai Penyusutan (per tahun)</th>
                <th>Nilai Sisa</th>
                <th>Masa Manfaat (tahun)</th> <!-- Kolom baru untuk Masa Manfaat -->
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($aset = mysqli_fetch_assoc($result_aset)) :
            // Periksa apakah masa_manfaat ada dan valid
            if (!isset($aset['masa_manfaat']) || !$aset['masa_manfaat'] || !$aset['tanggal_perolehan'] || !$aset['nilai_awal']) continue;

            $tahun_perolehan = date('Y', strtotime($aset['tanggal_perolehan']));
            $umur = max(0, $tahun_sekarang - $tahun_perolehan); // Tidak boleh negatif
            $masa_manfaat = $aset['masa_manfaat']; // Mengambil masa manfaat dari tabel aset
            $nilai_awal = $aset['nilai_awal'];
            $nilai_susut = $masa_manfaat > 0 ? $nilai_awal / $masa_manfaat : 0;

            // Perhitungan nilai sisa
            if ($umur >= $masa_manfaat) {
                $nilai_sisa = 0;
            } elseif ($umur > 0) {
                $nilai_sisa = $nilai_awal - ($nilai_susut * $umur);
            } else {
                // Jika umur kurang dari 1 tahun, nilai_sisa bisa dihitung berdasarkan pro-rata
                $nilai_sisa = $nilai_awal;
            }

        ?>
        <tr>
            <td><?= htmlspecialchars($aset['nama_aset']) ?></td>
            <td><?= date('d-m-Y', strtotime($aset['tanggal_perolehan'])) ?></td>
            <td>Rp<?= number_format($nilai_awal, 2, ',', '.') ?></td>
            <td>Rp<?= number_format($nilai_susut, 2, ',', '.') ?></td>
            <td>Rp<?= number_format($nilai_sisa, 2, ',', '.') ?></td>
            <td><?= htmlspecialchars($masa_manfaat) ?> tahun</td> <!-- Tampilkan Masa Manfaat -->
            <td>
                <form method="POST" action="simpan_penyusutan.php">
                    <input type="hidden" name="id_aset" value="<?= $aset['id_aset'] ?>">
                    <input type="hidden" name="tahun" value="<?= $tahun_sekarang ?>">
                    <input type="hidden" name="nilai_susut" value="<?= $nilai_susut ?>">
                    <input type="hidden" name="nilai_sisa" value="<?= $nilai_sisa ?>">
                    <button type="submit" class="btn-simpan">💾 Simpan</button>
                </form>
            </td>
        </tr>
        <?php endwhile ?>
        </tbody>
    </table>

    <!-- Tabel 2: Penyusutan yang Telah Disimpan -->
    <h3>📁 Penyusutan yang Telah Disimpan</h3>
    <table border="1" cellpadding="10" cellspacing="0">
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
                <th>Aksi</th>
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
            <td>
                <?php
                    $id_penyusutan = htmlspecialchars($penyusutan['id_penyusutan']);
                    echo '<a href="hapus_penyusutan.php?id=' . $id_penyusutan . '" onclick="return confirm(\'Yakin hapus data ini?\')">🗑️ Hapus</a>';
                ?>
            </td>
        </tr>
        <?php endwhile ?>
        </tbody>
    </table>
</div>
</body>
</html>
