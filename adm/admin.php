<?php
session_start();
include('../include/koneksi.php'); // koneksi ke database 
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: /siman/login.php");
    exit();
}

// Query untuk statistik aset
$query_statistik = "
    SELECT 
        COUNT(*) AS total_aset, 
        SUM(aset.nilai_awal) AS total_nilai_awal, 
        SUM(penyusutan.nilai_susut) AS total_nilai_susut 
    FROM aset
    LEFT JOIN penyusutan ON aset.id_aset = penyusutan.id_aset
";
$result_statistik = mysqli_query($conn, $query_statistik);
$statistik = mysqli_fetch_assoc($result_statistik);

// Query untuk tabel aset
$query_tabel = "
    SELECT 
        aset.id_aset, 
        aset.nama_aset, 
        kategori.nama_kategori, 
        lokasi.nama_lokasi, 
        aset.tanggal_perolehan, 
        aset.nilai_awal, 
        aset.status 
    FROM aset 
    JOIN kategori ON aset.kategori_id = kategori.id_kategori 
    JOIN lokasi ON aset.lokasi_id = lokasi.id_lokasi
";
$result_tabel = mysqli_query($conn, $query_tabel);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../assets/nadmin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Tambahkan Chart.js -->
    <script src="../assets/admin.js" defer></script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Manajemen Aset</h2>
        <ul>
            <li><a href="manajemen_pengguna.php">Manajemen Pengguna</a></li>
            <li class="submenu-item">
                <a href="javascript:void(0);" onclick="toggleSubmenu('aset-lengkap')">Manajemen Aset</a>
                <ul class="submenu" id="aset-lengkap">
                    <li><a href="atur_aset.php">Atur Aset</a></li>
                    <li><a href="kategori_aset.php">Kategori Aset</a></li>
                    <li><a href="lokasi_aset.php">Lokasi Aset</a></li>
                    <li><a href="lihat_aset.php">Lihat Aset</a></li>
                </ul>
            </li>
            <li><a href="../fitur/penyusutan.php">Kelola Penyusutan</a></li>
            <li><a href="../lap/laporan.php">Laporan & Statistik</a></li>
            <li class="submenu-item">
                <a href="javascript:void(0);" onclick="toggleSubmenu('backup-restore')">Backup dan Restore</a>
                <ul class="submenu" id="backup-restore">
                    <li><a href="../backup/backup.php">Backup Data</a></li>
                    <li><a href="../backup/restore.php">Restore Data</a></li>
                </ul>
            </li>
            <li class="submenu-item">
                <a href="javascript:void(0);" onclick="toggleSubmenu('pengaturan')">Pengaturan</a>
                <ul class="submenu" id="pengaturan">
                    <li><a href="setting1.php">Setting 1</a></li>
                    <li><a href="/siman/logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </div>

    <!-- Konten Utama -->
    <div class="main-content">
        <header>
            <h2>Dashboard Admin</h2>
        </header>

        <main>
            <!-- Tabel Aset -->
            <section>
                <h3>Daftar Aset</h3>
                <div class="table-container">
                    <table style="width: 100%; border-collapse: collapse; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
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
                            <?php while ($row = mysqli_fetch_assoc($result_tabel)) { ?>
                                <tr>
                                    <td><?= $row['id_aset'] ?></td>
                                    <td><?= $row['nama_aset'] ?></td>
                                    <td><?= $row['nama_kategori'] ?></td>
                                    <td><?= $row['nama_lokasi'] ?></td>
                                    <td><?= $row['tanggal_perolehan'] ?></td>
                                    <td>Rp <?= number_format($row['nilai_awal'], 0, ',', '.') ?></td>
                                    <td><?= $row['status'] ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Statistik Aset -->
            <section>
                <h3>Statistik Aset</h3>
                <div>
                    <p>Total Aset: <?= $statistik['total_aset'] ?></p>
                    <p>Total Nilai Awal: Rp <?= number_format($statistik['total_nilai_awal'], 0, ',', '.') ?></p>
                    <p>Total Nilai Penyusutan: Rp <?= number_format($statistik['total_nilai_susut'], 0, ',', '.') ?></p>
                </div>
            </section>

            <!-- Diagram Statistik -->
            <section>
                <h3>Diagram Statistik Aset</h3>
                <div class="diagram-container">
                    <!-- Diagram Pie -->
                    <canvas id="statistikDiagramPie" width="400" height="400"></canvas>
                    <!-- Diagram Kolom -->
                    <canvas id="statistikDiagramBar" width="400" height="400"></canvas>
                </div>
            </section>
        </main>

        <footer>
            <p>&copy; <?= date("Y") ?> Sistem Manajemen Aset Kampus</p>
        </footer>
    </div>

    <script>
        // Data untuk diagram pie
        const dataPie = {
            labels: ['Total Aset', 'Total Nilai Awal', 'Total Nilai Penyusutan'],
            datasets: [{
                label: 'Statistik Aset',
                data: [
                    <?= $statistik['total_aset'] ?>, 
                    <?= $statistik['total_nilai_awal'] ?>, 
                    <?= $statistik['total_nilai_susut'] ?>
                ],
                backgroundColor: ['rgba(54, 162, 235, 0.6)', 'rgba(255, 99, 132, 0.6)', 'rgba(75, 192, 192, 0.6)'],
                borderColor: ['rgba(54, 162, 235, 1)', 'rgba(255, 99, 132, 1)', 'rgba(75, 192, 192, 1)'],
                borderWidth: 1
            }]
        };

        // Konfigurasi diagram pie
        const configPie = {
            type: 'pie',
            data: dataPie,
            options: {
                responsive: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Statistik Aset (Pie Chart)'
                    }
                }
            }
        };

        // Data untuk diagram kolom
        const dataBar = {
            labels: ['Total Aset', 'Total Nilai Awal', 'Total Nilai Penyusutan'],
            datasets: [{
                label: 'Statistik Aset',
                data: [
                    <?= $statistik['total_aset'] ?>, 
                    <?= $statistik['total_nilai_awal'] ?>, 
                    <?= $statistik['total_nilai_susut'] ?>
                ],
                backgroundColor: ['rgba(54, 162, 235, 0.6)', 'rgba(255, 99, 132, 0.6)', 'rgba(75, 192, 192, 0.6)'],
                borderColor: ['rgba(54, 162, 235, 1)', 'rgba(255, 99, 132, 1)', 'rgba(75, 192, 192, 1)'],
                borderWidth: 1
            }]
        };

        // Konfigurasi diagram kolom
        const configBar = {
            type: 'bar',
            data: dataBar,
            options: {
                responsive: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Statistik Aset (Bar Chart)'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            },
            // Tambahkan properti berikut untuk membuat batang persegi panjang
            barThickness: 50 // Atur ketebalan batang sesuai kebutuhan
        };

        // Render diagram pie
        const ctxPie = document.getElementById('statistikDiagramPie').getContext('2d');
        new Chart(ctxPie, configPie);

        // Render diagram kolom
        const ctxBar = document.getElementById('statistikDiagramBar').getContext('2d');
        new Chart(ctxBar, configBar);
    </script>
</body>
</html>
