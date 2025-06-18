<?php
session_start();
include('../include/koneksi.php');
include('../include/popup_profil.php');
$allowed_roles = ['admin']; // Only admin can access restore

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /siman/login.php");
    exit();
}

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['backup_file'])) {
    $backupFile = $_FILES['backup_file']['tmp_name'];

    if (pathinfo($_FILES['backup_file']['name'], PATHINFO_EXTENSION) != 'sql') {
        $pesan = "Hanya file SQL yang diperbolehkan.";
    } else {
        $sqlContent = file_get_contents($backupFile);
        $sqlContent = str_replace("\r", '', $sqlContent); // Bersihkan karakter carriage return

        $queries = explode(";\n", $sqlContent); // Pecah setiap perintah SQL berdasarkan titik koma + newline

        $berhasil = true;

        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                if (!mysqli_query($conn, $query)) {
                    $pesan = "Error saat mengembalikan data: " . mysqli_error($conn);
                    $berhasil = false;
                    break;
                }
            }
        }

        if ($berhasil) {
            $pesan = "✅ Proses restore selesai!";
        }
    }
}
?>

<?php include('../include/header.php'); ?>
<?php include($_SESSION['role'] === 'admin' ? '../include/sidebar_admin.php' : '../include/sidebar_staf.php'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/admin.css">

<!-- Konten Utama -->
<div class="main-content">
    <header>
        <h2>Restore Database</h2>
    </header>

    <main>
        <section class="form-section">
            <h3>🔄 Restore Data</h3>
            <?php if (!empty($pesan)) : ?>
                <div class="alert-message">
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>
            
            <form action="restore.php" method="POST" enctype="multipart/form-data" class="restore-form">
                <div class="form-group">
                    <label for="backup_file">Pilih file backup (.sql):</label>
                    <input type="file" name="backup_file" id="backup_file" accept=".sql" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt me-2"></i> Restore Sekarang
                    </button>
                </div>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Sistem Manajemen Aset Kampus</p>
    </footer>
</div>

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