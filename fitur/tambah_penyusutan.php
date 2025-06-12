<?php
session_start();
include('../include/koneksi.php');

// Ambil data aset untuk dropdown
$query = "SELECT * FROM aset ORDER BY nama_aset";
$result = mysqli_query($conn, $query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $id_aset = $_POST['id_aset'];
    $tahun = $_POST['tahun'];
    $nilai_susut = $_POST['nilai_susut'];
    $nilai_sisa = $_POST['nilai_sisa'];

    // Query untuk menyimpan data penyusutan
    $query = "INSERT INTO penyusutan (id_aset, tahun, nilai_susut, nilai_sisa) VALUES ('$id_aset', '$tahun', '$nilai_susut', '$nilai_sisa')";
    if (mysqli_query($conn, $query)) {
        header('Location: tampilkan_penyusutan.php'); // Redirect ke halaman tampilkan penyusutan
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Penyusutan</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
<div class="main-content">
    <h2>Tambah Penyusutan</h2>
    <form method="POST">
        <label for="id_aset">Pilih Aset:</label>
        <select name="id_aset" required>
            <?php while ($aset = mysqli_fetch_assoc($result)) : ?>
                <option value="<?= $aset['id_aset'] ?>"><?= htmlspecialchars($aset['nama_aset']) ?></option>
            <?php endwhile ?>
        </select><br>

        <label for="tahun">Tahun:</label>
        <input type="number" name="tahun" required><br>

        <label for="nilai_susut">Nilai Penyusutan (per tahun):</label>
        <input type="number" name="nilai_susut" step="0.01" required><br>

        <label for="nilai_sisa">Nilai Sisa:</label>
        <input type="number" name="nilai_sisa" step="0.01" required><br>

        <button type="submit">Simpan</button>
    </form>
</div>
</body>
</html>
