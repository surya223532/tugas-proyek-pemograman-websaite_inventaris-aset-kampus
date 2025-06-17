<?php
session_start();
include('../include/koneksi.php'); // Koneksi ke database

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
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

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restore Data</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
    <div class="main-content">
        <header>
            <a href="../adm/admin.php" class="back-btn">Kembali</a>
        </header>

        <main>
            <section>
                <h3>🔄 Restore Data</h3>
                <?php if (!empty($pesan)) : ?>
                    <div style="margin: 10px 0; padding: 10px; background-color: #eee; border: 1px solid #ccc;">
                        <?= htmlspecialchars($pesan) ?>
                    </div>
                <?php endif; ?>
                <form action="restore.php" method="POST" enctype="multipart/form-data">
                    <label>Pilih file backup (.sql):</label><br>
                    <input type="file" name="backup_file" accept=".sql" required>
                    <br><br>
                    <button type="submit">🔁 Restore Sekarang</button>
                </form>
            </section>
        </main>

        <footer>
            <p>&copy; <?= date("Y") ?> Sistem Manajemen Aset Kampus</p>
        </footer>
    </div>
</body>
</html>
