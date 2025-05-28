<?php
session_start();
include('../include/koneksi.php'); // koneksi ke database 
$allowed_roles = ['admin', 'staf'];

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: login.php"); // Jika bukan role yang diizinkan, arahkan kembali ke login
    exit();
}   

if ($_SESSION['role'] === 'admin') {
    $dashboard = '../adm/admin.php';
} elseif ($_SESSION['role'] === 'staf') {
    $dashboard = '../staf/staf.php';
} elseif ($_SESSION['role'] === 'pimpinan') {
    $dashboard = '../pimpinan/pimpinan.php';
} else {
    $dashboard = '../dashboard.php'; // fallback jika ada role lain
}

$message = "";

// Tambah lokasi
if (isset($_POST['submit'])) {
    $nama_lokasi = $_POST['nama_lokasi'];
    $alamat = $_POST['alamat'];

    if (!empty($nama_lokasi) && !empty($alamat)) {
        $stmt = $conn->prepare("INSERT INTO lokasi (nama_lokasi, alamat) VALUES (?, ?)");
        $stmt->bind_param("ss", $nama_lokasi, $alamat);
        if ($stmt->execute()) {
            $message = "Lokasi aset berhasil ditambahkan!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Semua field wajib diisi.";
    }
}

// Hapus lokasi
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM lokasi WHERE id_lokasi = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Lokasi aset berhasil dihapus!";
    } else {
        $message = "Gagal menghapus lokasi.";
    }
    $stmt->close();
}

// Edit lokasi (proses update)
if (isset($_POST['update'])) {
    $id_lokasi = $_POST['id_lokasi'];
    $nama_lokasi = $_POST['nama_lokasi'];
    $alamat = $_POST['alamat'];

    $stmt = $conn->prepare("UPDATE lokasi SET nama_lokasi = ?, alamat = ? WHERE id_lokasi = ?");
    $stmt->bind_param("ssi", $nama_lokasi, $alamat, $id_lokasi);
    if ($stmt->execute()) {
        $message = "Data lokasi berhasil diperbarui.";
    } else {
        $message = "Gagal memperbarui lokasi.";
    }
    $stmt->close();
}

// Ambil data lokasi untuk edit (jika ada parameter edit)
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM lokasi WHERE id_lokasi = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result_edit = $stmt->get_result();
    $edit_data = $result_edit->fetch_assoc();
    $stmt->close();
}

// Ambil semua data lokasi
$sql = "SELECT * FROM lokasi";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lokasi Aset - Sistem Manajemen Aset Kampus</title>
    <link rel="stylesheet" href="../assets/pengelola_aset.css">
</head>
<body>
<div class="container">
    <h1>Manajemen Lokasi Aset</h1>
    <?php if (!empty($message)) echo "<p>$message</p>"; ?>

    <!-- Form tambah/edit lokasi -->
    <form action="lokasi_aset.php" method="POST">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="id_lokasi" value="<?= $edit_data['id_lokasi']; ?>">
        <?php endif; ?>

        <label for="nama_lokasi">Nama Lokasi:</label>
        <input type="text" id="nama_lokasi" name="nama_lokasi" required value="<?= $edit_mode ? $edit_data['nama_lokasi'] : ''; ?>">

        <label for="alamat">Alamat:</label>
        <input type="text" id="alamat" name="alamat" required value="<?= $edit_mode ? $edit_data['alamat'] : ''; ?>">

        <button type="submit" name="<?= $edit_mode ? 'update' : 'submit'; ?>">
            <?= $edit_mode ? 'Update Lokasi' : 'Tambah Lokasi'; ?>
        </button>
        <?php if ($edit_mode): ?>
            <a href="lokasi_aset.php">Batal Edit</a>
        <?php endif; ?>
    </form>

    <hr>

    <h2>Daftar Lokasi Aset</h2>
    <table>
        <thead>
        <tr>
            <th>No</th>
            <th>Nama Lokasi</th>
            <th>Alamat</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): $no = 1; ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['nama_lokasi']); ?></td>
                    <td><?= htmlspecialchars($row['alamat']); ?></td>
                    <td>
                        <a href="lokasi_aset.php?edit=<?= $row['id_lokasi']; ?>">Edit</a> |
                        <a href="lokasi_aset.php?hapus=<?= $row['id_lokasi']; ?>" onclick="return confirm('Yakin ingin menghapus lokasi ini?');">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">Tidak ada lokasi aset yang terdaftar.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<a href="<?= $dashboard ?>" class="btn-kembali"> Kembali ke Dashboard</a>
</body>
</html>

<?php $conn->close(); ?>
