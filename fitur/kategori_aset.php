<?php
session_start();
include('../include/koneksi.php');
include('../include/popup_profil.php');
$allowed_roles = ['admin', 'staf'];

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /siman/login.php");
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

// Ambil daftar kategori untuk tabel
$result = $conn->query("SELECT * FROM kategori ORDER BY id_kategori DESC");
?>

<?php include('../include/header.php'); ?>
<?php include($_SESSION['role'] === 'admin' ? '../include/sidebar_admin.php' : '../include/sidebar_staf.php'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/kategori_aset.css">
<!-- Konten Utama -->
<div class="main-content">
    <header>
        <h2>Manajemen Kategori Aset</h2>
    </header>

    <main>
        <!-- Form Tambah/Edit Kategori -->
        <section class="form-section">
            <h3><?= $kategori_edit ? 'Edit Kategori' : 'Tambah Kategori Baru' ?></h3>
            <form method="post" class="kategori-form">
                <input type="hidden" name="id_kategori" value="<?= $kategori_edit['id_kategori'] ?? '' ?>">
                
                <div class="form-group">
                    <label for="nama_kategori">Nama Kategori:</label>
                    <input type="text" name="nama_kategori" id="nama_kategori" 
                           value="<?= htmlspecialchars($kategori_edit['nama_kategori'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi:</label>
                    <textarea name="deskripsi" id="deskripsi" required><?= htmlspecialchars($kategori_edit['deskripsi'] ?? '') ?></textarea>
                </div>

                <div class="form-actions">
                    <?php if ($kategori_edit): ?>
                        <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="kategori_aset.php" class="btn btn-secondary">Batal</a>
                    <?php else: ?>
                        <button type="submit" name="tambah" class="btn btn-primary">Tambah Kategori</button>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <!-- Tabel Daftar Kategori -->
        <section class="kategori-list">
            <h3>Daftar Kategori Aset</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id_kategori']) ?></td>
                                <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                                <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                                <td class="aksi">
                                    <a href="?edit=<?= $row['id_kategori'] ?>" class="btn-icon edit" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="?hapus=<?= $row['id_kategori'] ?>" class="btn-icon delete" title="Hapus" onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Belum ada kategori yang terdaftar</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Sistem Manajemen Aset Kampus</p>
    </footer>
</div>

<script>
    // Konfirmasi sebelum menghapus
    document.querySelectorAll('.btn-icon.delete').forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Yakin ingin menghapus kategori ini?')) {
                e.preventDefault();
            }
        });
    });
</script>
    <!-- Kembali Button - Recommended Position -->
    <div class="form-actions text-end mb-4">
        <button onclick="window.location.href='<?= 
            ($_SESSION['role'] === 'admin') ? '../adm/admin.php' : 
            (($_SESSION['role'] === 'pimpinan') ? '../pimpinan/pimpinan.php' : '../staf/staf.php') 
        ?>'" 
        class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
        </button>
    </div>
<?php include('../include/footer.php'); ?>