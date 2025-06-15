<?php
session_start();
include('../include/koneksi.php'); // koneksi ke database

// Cek apakah user sudah login dan memiliki role yang sesuai
$allowed_roles = ['pimpinan', 'admin']; // Sesuaikan dengan role
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /siman/login.php");
    exit();
}

// Fetch user data from the database
$user = [];
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $query = "SELECT email, nama, role, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $user = []; // Jika tidak ada data, set $user sebagai array kosong
    }
}

// Proses ubah password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ubah_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi'];

    // Cek password lama
    if ($password_lama !== $user['password']) {
        $pesan = 'Password lama salah!';
    } elseif ($password_baru !== $konfirmasi) {
        $pesan = 'Konfirmasi password baru tidak cocok!';
    } else {
        // Simpan password baru langsung (plaintext)
        $update = mysqli_query($conn, "UPDATE users SET password='$password_baru' WHERE email='{$user['email']}'");
        if ($update) {
            $pesan = 'Password berhasil diubah!';
        } else {
            $pesan = 'Gagal mengubah password. Error: ' . mysqli_error($conn);
        }
    }
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

// Tambahkan include untuk popup_profil.php
include('../include/popup_profil.php');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pimpinan</title>
    <link rel="stylesheet" href="../assets/nadmin.css"> <!-- Menautkan CSS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Tambahkan Chart.js -->
    <script src="../assets/admin.js" defer></script>
    <script>
    function toggleSubmenu(id) {
        var submenu = document.getElementById(id);
        submenu.style.display = submenu.style.display === "block" ? "none" : "block";
    }

    function showProfilePopup() {
        document.getElementById('profile-popup').style.display = 'block';
        document.getElementById('popup-overlay').style.display = 'block';
        console.log("Menampilkan popup dan overlay");
    }

    function closeProfilePopup() {
        document.getElementById('profile-popup').style.display = 'none';
        document.getElementById('popup-overlay').style.display = 'none';
        console.log("Menutup popup dan overlay");
    }
</script>

</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <h2>Manajemen Aset</h2>
        <ul>
            <li><a href="../lap/laporan.php">Laporan & Statistik</a></li>
            <li class="submenu-item">
                
                <ul class="submenu" id="profil">
                    <li><a href="../fitur/profil.php">Lihat Profil</a></li></ul>

          

            <!-- Dropdown menu Pengaturan -->
            <li class="submenu-item">
                <a href="javascript:void(0);" onclick="toggleSubmenu('pengaturan')">Pengaturan</a>
                <ul class="submenu" id="pengaturan">
                    <li><a href="javascript:void(0);" onclick="showProfilePopup()">Profil</a></li>
                    <li><a href="/siman/logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </div>

    <button class="toggle-btn" id="sidebarToggle">
        ☰
    </button>

    <!-- Konten Utama -->
    <div class="main-content"> 
        <header>
            <h2>Dashboard Pimpinan</h2>
        </header>

        <main>
            <!-- Tabel Aset -->
            <section>
                <h3>Daftar Aset</h3>
                <div class="table-container">
                    <table>
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
            barThickness: 50 // Atur ketebalan batang sesuai kebutuhan
        };

        // Render diagram pie
        const ctxPie = document.getElementById('statistikDiagramPie').getContext('2d');
        new Chart(ctxPie, configPie);

        // Render diagram kolom
        const ctxBar = document.getElementById('statistikDiagramBar').getContext('2d');
        new Chart(ctxBar, configBar);
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
        const toggleBtn = document.getElementById("sidebarToggle");
        const sidebar = document.querySelector(".sidebar");
        const body = document.body;

        toggleBtn.addEventListener("click", function () {
        sidebar.classList.toggle("collapsed");
        body.classList.toggle("sidebar-collapsed");
        });
    });
    </script>
</body>
</html>