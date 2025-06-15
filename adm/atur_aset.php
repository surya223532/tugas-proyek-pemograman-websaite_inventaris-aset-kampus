<?php
session_start();
include('../include/koneksi.php'); // koneksi ke database 
$allowed_roles = ['admin', 'staf'];

$allowed_roles = ['admin', 'staf'];

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: login.php"); 
    exit();
}

if ($_SESSION['role'] === 'admin') {
    $dashboard = '../adm/admin.php';
} elseif ($_SESSION['role'] === 'staf') {
    $dashboard = '../staf/staf.php';
} else {
    $dashboard = '../dashboard.php'; // fallback
}




// Fungsi tambah aset
if (isset($_POST['tambah_aset'])) {
    $nama_aset = $_POST['nama_aset'];
    $kategori_id = $_POST['kategori_id'];
    $lokasi_id = $_POST['lokasi_id'];
    $tanggal_perolehan = $_POST['tanggal_perolehan'];
    $nilai_awal = $_POST['nilai_awal'];
    $status = $_POST['status'];
    $masa_manfaat = $_POST['masa_manfaat']; // Tambahkan input masa manfaat

    $query = "INSERT INTO aset (nama_aset, kategori_id, lokasi_id, tanggal_perolehan, nilai_awal, status, masa_manfaat)
              VALUES ('$nama_aset', '$kategori_id', '$lokasi_id', '$tanggal_perolehan', '$nilai_awal', '$status', '$masa_manfaat')";

    if (mysqli_query($conn, $query)) {
        $message = "Aset berhasil ditambahkan!";
    } else {
        $message = "Gagal menambahkan aset: " . mysqli_error($conn);
    }
}

// Fungsi edit aset
if (isset($_POST['edit_aset'])) {
    $id_aset = $_POST['id_aset'];
    $nama_aset = $_POST['nama_aset'];
    $kategori_id = $_POST['kategori_id'];
    $lokasi_id = $_POST['lokasi_id'];
    $tanggal_perolehan = $_POST['tanggal_perolehan'];
    $nilai_awal = $_POST['nilai_awal'];
    $status = $_POST['status'];
    $masa_manfaat = $_POST['masa_manfaat']; // Tambahkan input masa manfaat

    $query = "UPDATE aset SET nama_aset = '$nama_aset', kategori_id = '$kategori_id', lokasi_id = '$lokasi_id', 
              tanggal_perolehan = '$tanggal_perolehan', nilai_awal = '$nilai_awal', status = '$status', masa_manfaat = '$masa_manfaat' 
              WHERE id_aset = '$id_aset'";

    if (mysqli_query($conn, $query)) {
        $message = "Aset berhasil diperbarui!";
        unset($_GET['edit_aset']);
    } else {
        $message = "Gagal memperbarui aset: " . mysqli_error($conn);
    }
}

// Fungsi hapus aset
if (isset($_GET['hapus_aset'])) {
    $id_aset = $_GET['hapus_aset'];

    $query = "DELETE FROM aset WHERE id_aset = '$id_aset'";
    if (mysqli_query($conn, $query)) {
        $message = "Aset berhasil dihapus!";
    } else {
        $message = "Gagal menghapus aset: " . mysqli_error($conn);
    }
}

// Ambil data kategori dan lokasi untuk dropdown
$kategori_list = mysqli_query($conn, "SELECT * FROM kategori");
$lokasi_list = mysqli_query($conn, "SELECT * FROM lokasi");

// Ambil data aset untuk edit
if (isset($_GET['edit_aset'])) {
    $id_aset = $_GET['edit_aset'];
    $aset_query = mysqli_query($conn, "SELECT * FROM aset WHERE id_aset = '$id_aset'");
    $aset = mysqli_fetch_assoc($aset_query);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengelolaan Aset</title>
    <link rel="stylesheet" href="../assets/atur_aset.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
 
</head>
<body>
    <div class="container">
        <h2>Manajement Aset</h2>

        <?php if (isset($message)) echo "<p class='message'><strong>$message</strong></p>"; ?>

        <!-- Form Tambah/Edit Aset -->
        <div class="form-section">
            <h3><?= isset($aset) ? "Edit Aset" : "Tambah Aset"; ?></h3>
            <form method="post">
                <?php if (isset($aset)) { ?>
                    <input type="hidden" name="id_aset" value="<?= $aset['id_aset']; ?>">
                <?php } ?>
                <label for="nama_aset">Nama Aset:</label>
                <input type="text" name="nama_aset" required value="<?= isset($aset) ? $aset['nama_aset'] : ''; ?>">

                <label for="kategori_id">Kategori Aset:</label>
                <select name="kategori_id" required>
                    <option value="">Pilih Kategori</option>
                    <?php while($kategori = mysqli_fetch_assoc($kategori_list)) { ?>
                        <option value="<?= $kategori['id_kategori'] ?>" <?= isset($aset) && $aset['kategori_id'] == $kategori['id_kategori'] ? 'selected' : ''; ?>><?= $kategori['nama_kategori'] ?></option>
                    <?php } ?>
                </select>

                <label for="lokasi_id">Lokasi Aset:</label>
                <select name="lokasi_id" required>
                    <option value="">Pilih Lokasi</option>
                    <?php while($lokasi = mysqli_fetch_assoc($lokasi_list)) { ?>
                        <option value="<?= $lokasi['id_lokasi'] ?>" <?= isset($aset) && $aset['lokasi_id'] == $lokasi['id_lokasi'] ? 'selected' : ''; ?>><?= $lokasi['nama_lokasi'] ?></option>
                    <?php } ?>
                </select>

                <label for="tanggal_perolehan">Tanggal Perolehan:</label>
                <input type="date" name="tanggal_perolehan" required value="<?= isset($aset) ? $aset['tanggal_perolehan'] : ''; ?>">

                <label for="nilai_awal">Nilai Awal Aset (dalam Rupiah):</label>
                <input type="number" name="nilai_awal" required value="<?= isset($aset) ? $aset['nilai_awal'] : ''; ?>">

                <label for="status">Status:</label>
                <select name="status" required>
                    <option value="Aktif" <?= isset($aset) && $aset['status'] == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="Tidak Aktif" <?= isset($aset) && $aset['status'] == 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
                </select>

                <label for="masa_manfaat">Masa Manfaat (dalam tahun):</label>
                <input type="number" name="masa_manfaat" required value="<?= isset($aset) ? $aset['masa_manfaat'] : ''; ?>">

                <?php if (isset($aset)) { ?>
                    <button type="submit" name="edit_aset">Update Aset</button>
                <?php } else { ?>
                    <button type="submit" name="tambah_aset">Tambah Aset</button>
                <?php } ?>
            </form>
        </div>

        <!-- Tabel Daftar Aset -->
        <div class="aset-list">
            <h3>Daftar Aset</h3>
            <table border="1" cellpadding="10">
                <thead>
                    <tr>
                        <th>Nama Aset</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Tanggal Perolehan</th>
                        <th>Nilai Awal</th>
                        <th>Status</th>
                        <th>Masa Manfaat</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result_aset = mysqli_query($conn, "SELECT aset.*, kategori.nama_kategori, lokasi.nama_lokasi 
                                                        FROM aset 
                                                        JOIN kategori ON aset.kategori_id = kategori.id_kategori
                                                        JOIN lokasi ON aset.lokasi_id = lokasi.id_lokasi");
                    while ($aset_item = mysqli_fetch_assoc($result_aset)) { ?>
                        <tr>
                            <td><?= $aset_item['nama_aset'] ?></td>
                            <td><?= $aset_item['nama_kategori'] ?></td>
                            <td><?= $aset_item['nama_lokasi'] ?></td>
                            <td><?= $aset_item['tanggal_perolehan'] ?></td>
                            <td>Rp <?= number_format($aset_item['nilai_awal'], 0, ',', '.') ?></td>
                            <td><?= $aset_item['status'] ?></td>
                            <td><?= $aset_item['masa_manfaat'] ?> Tahun</td>
                            <td class="aksi">
                                <a href="?edit_aset=<?= $aset_item['id_aset'] ?>" class="btn-icon edit" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a href="?hapus_aset=<?= $aset_item['id_aset'] ?>" class="btn-icon delete" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus aset ini?')">
                                <i class="fa-solid fa-trash"></i>
                            </td>

                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <button class="btn-kembali" onclick="window.location.href='<?= $dashboard ?>'">Kembali</button>

    </div>
</body>
</html>
