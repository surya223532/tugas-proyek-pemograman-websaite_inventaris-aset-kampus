<?php
session_start();
include('../include/koneksi.php');
include('../include/popup_profil.php');
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

// Query untuk tabel aset dengan ruangan dan garansi
$query_tabel = "
    SELECT 
        aset.id_aset, 
        aset.nama_aset, 
        kategori.nama_kategori, 
        lokasi.nama_lokasi,
        ruangan.nama_ruangan,
        aset.tanggal_perolehan, 
        aset.nilai_awal, 
        aset.status,
        aset.jenis_garansi,
        aset.garansi_berakhir,
        aset.penyedia_garansi
    FROM aset 
    JOIN kategori ON aset.kategori_id = kategori.id_kategori 
    JOIN lokasi ON aset.lokasi_id = lokasi.id_lokasi
    LEFT JOIN ruangan ON aset.ruangan_id = ruangan.id_ruangan
";
$result_tabel = mysqli_query($conn, $query_tabel);

// Fungsi format tanggal
function formatTanggal($date) {
    return date('d-m-Y', strtotime($date));
}
?>

<?php include('../include/header.php'); ?>
<?php include('../include/sidebar_admin.php'); ?>

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
                            <th>Ruangan</th>
                            <th>Tanggal Perolehan</th>
                            <th>Nilai Awal</th>
                            <th>Status</th>
                            <th>Garansi</th>
                            <th>Berlaku Sampai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result_tabel)) { ?>
                            <tr>
                                <td><?= $row['id_aset'] ?></td>
                                <td><?= $row['nama_aset'] ?></td>
                                <td><?= $row['nama_kategori'] ?></td>
                                <td><?= $row['nama_lokasi'] ?></td>
                                <td><?= $row['nama_ruangan'] ? $row['nama_ruangan'] : '-' ?></td>
                                <td><?= formatTanggal($row['tanggal_perolehan']) ?></td>
                                <td>Rp <?= number_format($row['nilai_awal'], 0, ',', '.') ?></td>
                                <td><?= $row['status'] ?></td>
                                <td>
                                    <?php if ($row['jenis_garansi']): ?>
                                        <?= ucfirst($row['jenis_garansi']) ?><br>
                                        <small><?= $row['penyedia_garansi'] ?></small>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $row['garansi_berakhir'] ? formatTanggal($row['garansi_berakhir']) : '-' ?>
                                    <?php if ($row['garansi_berakhir'] && strtotime($row['garansi_berakhir']) < time()): ?>
                                        <span style="background-color: #dc3545; color: white; padding: 2px 5px; border-radius: 3px; font-size: 0.8em;">Expired</span>
                                    <?php endif; ?>
                                </td>
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
        barThickness: 50
    };

    // Render diagram pie
    const ctxPie = document.getElementById('statistikDiagramPie').getContext('2d');
    new Chart(ctxPie, configPie);

    // Render diagram kolom
    const ctxBar = document.getElementById('statistikDiagramBar').getContext('2d');
    new Chart(ctxBar, configBar);
</script>

<?php include('../include/footer.php'); ?>