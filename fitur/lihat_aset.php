<?php
session_start();
include('../include/koneksi.php'); // koneksi ke database 
$allowed_roles = [ 'pimpinan','admin', 'staf']; 

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: login.php"); // Jika bukan role yang diizinkan, arahkan kembali ke login
    exit();
}

// Mendapatkan nilai pencarian jika ada
$cari = isset($_POST['cari']) ? $_POST['cari'] : '';

// Query untuk mengambil data aset, dengan filter pencarian
$query = "SELECT aset.id_aset, aset.nama_aset, kategori.nama_kategori, lokasi.nama_lokasi, aset.tanggal_perolehan, aset.nilai_awal, aset.status 
          FROM aset 
          JOIN kategori ON aset.kategori_id = kategori.id_kategori 
          JOIN lokasi ON aset.lokasi_id = lokasi.id_lokasi
          WHERE aset.nama_aset LIKE '%$cari%' OR kategori.nama_kategori LIKE '%$cari%' OR lokasi.nama_lokasi LIKE '%$cari%'";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/lihat_aset.css">
    <title>Data Aset</title>
    <link rel="stylesheet" href="../assets/style.css"> 
</head>
<body>
    <header>
        <h2> Daftar Aset</h2>
    </header>

    <main>
        <section>
            <h3>Daftar Aset yang Tersedia</h3>
            <!-- Form Pencarian -->
            <form method="POST" action="">
                <input type="text" name="cari" placeholder="Cari aset..." value="<?= $cari ?>" />
                <button type="submit">Cari</button>
            </form>

            <!-- Tabel Aset -->
            <table border="1">
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
                    <?php while($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?= $row['id_aset'] ?></td>
                            <td><?= $row['nama_aset'] ?></td>
                            <td><?= $row['nama_kategori'] ?></td>
                            <td><?= $row['nama_lokasi'] ?></td>
                            <td><?= $row['tanggal_perolehan'] ?></td>
                            <td><?= $row['nilai_awal'] ?></td>
                            <td><?= $row['status'] ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <button class="btn-kembali" onclick="window.history.back()">Kembali</button>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Sistem Manajemen Aset Kampus</p>
    </footer>
</body>
</html>
