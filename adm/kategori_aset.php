<?php
session_start();
include('../include/koneksi.php');

$allowed_roles = ['admin', 'staf'];

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: login.php"); // Jika bukan role yang diizinkan, arahkan kembali ke login
    exit();
}

// Proses tambah kategori
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah'])) {
    $nama_kategori = trim($_POST['nama_kategori']);
    $deskripsi = trim($_POST['deskripsi']);

    if (!empty($nama_kategori)) {
        $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)");
        $stmt->bind_param("ss", $nama_kategori, $deskripsi);
        $stmt->execute();
        $stmt->close();

        header("Location: kategori_aset.php");
        exit();
    }
}

// Proses hapus kategori
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = $conn->prepare("DELETE FROM kategori WHERE id_kategori = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: kategori_aset.php");
    exit();
}

// Proses edit kategori
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit'])) {
    $id = intval($_POST['id_kategori']);
    $nama_kategori = trim($_POST['nama_kategori']);
    $deskripsi = trim($_POST['deskripsi']);

    if (!empty($nama_kategori)) {
        $stmt = $conn->prepare("UPDATE kategori SET nama_kategori = ?, deskripsi = ? WHERE id_kategori = ?");
        $stmt->bind_param("ssi", $nama_kategori, $deskripsi, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: kategori_aset.php");
        exit();
    }
}

// Ambil data kategori jika sedang mengedit
$kategori_edit = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM kategori WHERE id_kategori = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $kategori_edit = $result->fetch_assoc();
    $stmt->close();
}
?>
<?php
$dashboard = ($_SESSION['role'] === 'admin') ? '../adm/admin.php' : '../staf/staf.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kategori Aset</title>
    <link rel="stylesheet" href="../assets/pengelola_aset.css">
    <style>
        table th, table td { text-align: left; }
    </style>
</head>
<body>
    <div class="container">
        <h2> Kategori Aset</h2>

        <!-- Form tambah / edit kategori -->
        <form method="post" action="">
            <input type="hidden" name="id_kategori" value="<?= $kategori_edit['id_kategori'] ?? '' ?>">
            <label for="nama_kategori">Nama Kategori:</label>
            <input type="text" name="nama_kategori" id="nama_kategori" value="<?= htmlspecialchars($kategori_edit['nama_kategori'] ?? '') ?>" required>

            <label for="deskripsi">Deskripsi:</label>
            <textarea name="deskripsi" id="deskripsi" required><?= htmlspecialchars($kategori_edit['deskripsi'] ?? '') ?></textarea>

            <?php if ($kategori_edit): ?>
                <button type="submit" name="edit">Simpan Perubahan</button>
                <a href="kategori_aset.php" style="margin-left: 10px; color: red;">Batal</a>
            <?php else: ?>
                <button type="submit" name="tambah">Tambah</button>
            <?php endif; ?>
        </form>

        <hr>

        <h3>Daftar Kategori</h3>
        <table border="1" cellpadding="10">
            <tr>
                <th>ID</th>
                <th>Nama Kategori</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
            </tr>
            <?php
            $result = $conn->query("SELECT * FROM kategori ORDER BY id_kategori DESC");
            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
            ?>
            <tr>
                <td><?= htmlspecialchars($row['id_kategori']) ?></td>
                <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                <td>
                    <a href="?edit=<?= $row['id_kategori'] ?>">Edit</a> |
                    <a href="?hapus=<?= $row['id_kategori'] ?>" onclick="return confirm('Yakin ingin menghapus kategori ini?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="4">Belum ada kategori.</td></tr>
            <?php endif; ?>
        </table>
    </div>
    <a href="<?= $dashboard ?>" class="btn-kembali">⬅ Kembali ke Dashboard</a>
    <script>
        // Script untuk konfirmasi penghapusan
        document.querySelectorAll('a[href*="hapus"]').forEach(link => {
            link.addEventListener('click', function(event) {
                if (!confirm('Yakin ingin menghapus kategori ini?')) {
                    event.preventDefault();
                }
            });
        });
</body>
</html>
