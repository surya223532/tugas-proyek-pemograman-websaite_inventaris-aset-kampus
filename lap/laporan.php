<?php
session_start();
include('../include/koneksi.php');
include('../include/popup_profil.php');

$allowed_roles = ['admin', 'staf', 'pimpinan'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /siman/login.php");
    exit();
}

// Fungsi untuk format Rupiah yang aman
function formatRupiah($value) {
    if ($value === null || $value === '') {
        return 'Rp 0';
    }
    return 'Rp ' . number_format((float)$value, 0, ',', '.');
}

// Fungsi untuk format tanggal
function formatTanggal($date) {
    if (empty($date)) return '-';
    return date('d-m-Y', strtotime($date));
}

// Ambil parameter filter dari URL
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$lokasi_filter = isset($_GET['lokasi']) ? $_GET['lokasi'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : '';
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : '';

// Ambil nama kategori untuk ditampilkan di PDF
$nama_kategori_filter = '';
if (!empty($kategori_filter)) {
    $kategori_query = "SELECT nama_kategori FROM kategori WHERE id_kategori = '" . $conn->real_escape_string($kategori_filter) . "'";
    $kategori_result = $conn->query($kategori_query);
    if ($kategori_result->num_rows > 0) {
        $kategori_row = $kategori_result->fetch_assoc();
        $nama_kategori_filter = $kategori_row['nama_kategori'];
    }
}

// Juga ambil nama lokasi untuk ditampilkan di PDF
$nama_lokasi_filter = '';
if (!empty($lokasi_filter)) {
    $lokasi_query = "SELECT nama_lokasi FROM lokasi WHERE id_lokasi = '" . $conn->real_escape_string($lokasi_filter) . "'";
    $lokasi_result = $conn->query($lokasi_query);
    if ($lokasi_result->num_rows > 0) {
        $lokasi_row = $lokasi_result->fetch_assoc();
        $nama_lokasi_filter = $lokasi_row['nama_lokasi'];
    }
}
?>

<?php include('../include/header.php'); ?>
<?php include($_SESSION['role'] === 'admin' ? '../include/sidebar_admin.php' : ($_SESSION['role'] === 'pimpinan' ? '../include/sidebar_pimpinan.php' : '../include/sidebar_staf.php')); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/laporan.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<!-- Konten Utama -->
<div class="main-content">
    <header>
        <h2>Laporan Aset dan Penyusutan</h2>
    </header>

    <main>
        <!-- Form Filter dan Pencarian -->
        <div class="filter-container">
            <form method="GET" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="kategori">Kategori:</label>
                        <select id="kategori" name="kategori" class="form-control">
                            <option value="">Semua Kategori</option>
                            <?php
                            $kategori_query = "SELECT * FROM kategori";
                            $kategori_result = $conn->query($kategori_query);
                            while ($kategori = $kategori_result->fetch_assoc()) {
                                $selected = ($kategori_filter == $kategori['id_kategori']) ? 'selected' : '';
                                echo "<option value='{$kategori['id_kategori']}' $selected>{$kategori['nama_kategori']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="lokasi">Lokasi:</label>
                        <select id="lokasi" name="lokasi" class="form-control">
                            <option value="">Semua Lokasi</option>
                            <?php
                            $lokasi_query = "SELECT * FROM lokasi";
                            $lokasi_result = $conn->query($lokasi_query);
                            while ($lokasi = $lokasi_result->fetch_assoc()) {
                                $selected = ($lokasi_filter == $lokasi['id_lokasi']) ? 'selected' : '';
                                echo "<option value='{$lokasi['id_lokasi']}' $selected>{$lokasi['nama_lokasi']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="aktif" <?= ($status_filter == 'aktif') ? 'selected' : '' ?>>Aktif</option>
                            <option value="non-aktif" <?= ($status_filter == 'non-aktif') ? 'selected' : '' ?>>Non-Aktif</option>
                            <option value="dijual" <?= ($status_filter == 'dijual') ? 'selected' : '' ?>>Dalam perbaikan</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="tanggal_mulai">Tanggal Mulai:</label>
                        <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control" value="<?= htmlspecialchars($tanggal_mulai) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="tanggal_akhir">Tanggal Akhir:</label>
                        <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control" value="<?= htmlspecialchars($tanggal_akhir) ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group search-group">
                        <label for="search">Pencarian:</label>
                        <input type="text" id="search" name="search" class="form-control" placeholder="Cari nama aset..." value="<?= htmlspecialchars($search_query) ?>">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                        <button type="button" onclick="resetFilter()" class="btn btn-secondary"><i class="fas fa-sync-alt"></i> Reset</button>
                        <button onclick="downloadPDF()" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Download PDF</button>
                        <button onclick="printPage()" class="btn btn-primary"><i class="fas fa-print"></i> Print</button>
                    </div>
                </div>
            </form>
        </div>

        <?php
        // Bangun query dengan filter
        $query = "
            SELECT 
                a.id_aset, 
                a.nama_aset, 
                a.tanggal_perolehan, 
                COALESCE(a.nilai_awal, 0) as nilai_awal, 
                a.status, 
                k.nama_kategori, 
                l.nama_lokasi, 
                r.nama_ruangan,
                COALESCE(p.nilai_susut, 0) as nilai_susut,
                a.masa_manfaat,
                a.jenis_garansi,
                a.garansi_berakhir,
                a.penyedia_garansi,
                a.nomor_garansi
            FROM aset a
            LEFT JOIN kategori k ON a.kategori_id = k.id_kategori
            LEFT JOIN lokasi l ON a.lokasi_id = l.id_lokasi
            LEFT JOIN ruangan r ON a.ruangan_id = r.id_ruangan
            LEFT JOIN penyusutan p ON a.id_aset = p.id_aset
            WHERE 1=1
        ";
        
        // Tambahkan kondisi filter jika ada
        if (!empty($kategori_filter)) {
            $query .= " AND a.kategori_id = '" . $conn->real_escape_string($kategori_filter) . "'";
        }
        
        if (!empty($lokasi_filter)) {
            $query .= " AND a.lokasi_id = '" . $conn->real_escape_string($lokasi_filter) . "'";
        }
        
        if (!empty($status_filter)) {
            $query .= " AND a.status = '" . $conn->real_escape_string($status_filter) . "'";
        }
        
        if (!empty($search_query)) {
            $query .= " AND (a.nama_aset LIKE '%" . $conn->real_escape_string($search_query) . "%' 
                          OR r.nama_ruangan LIKE '%" . $conn->real_escape_string($search_query) . "%'
                          OR a.penyedia_garansi LIKE '%" . $conn->real_escape_string($search_query) . "%')";
        }
        
        // Tambahkan filter tanggal jika diisi
        if (!empty($tanggal_mulai)) {
            $query .= " AND a.tanggal_perolehan >= '" . $conn->real_escape_string($tanggal_mulai) . "'";
        }
        
        if (!empty($tanggal_akhir)) {
            $query .= " AND a.tanggal_perolehan <= '" . $conn->real_escape_string($tanggal_akhir) . "'";
        }
        
        $query .= " ORDER BY a.nama_aset";
        
        $result = $conn->query($query);
        $labels = [];
        $nilai_awal_list = [];
        $nilai_susut_list = [];

        if ($result->num_rows > 0) {
            echo "<div class='table-container'>
                    <table id='tabelLaporan'>
                        <thead>
                        <tr>
                            <th>ID Aset</th>
                            <th>Nama Aset</th>
                            <th>Tanggal Perolehan</th>
                            <th>Nilai Awal</th>
                            <th>Status</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Ruangan</th>
                            <th>Garansi</th>
                            <th>Berlaku Sampai</th>
                            <th>Nilai Susut</th>
                            <th>Masa Manfaat</th>
                        </tr>
                        </thead>
                        <tbody>";

            $totalPenyusutan = 0;
            $totalNilaiAset = 0;

            while ($row = $result->fetch_assoc()) {
                $totalPenyusutan += $row['nilai_susut'];
                $totalNilaiAset += $row['nilai_awal'];
                $labels[] = $row['nama_aset'];
                $nilai_awal_list[] = $row['nilai_awal'];
                $nilai_susut_list[] = $row['nilai_susut'];

                echo "<tr>
                        <td>{$row['id_aset']}</td>
                        <td>{$row['nama_aset']}</td>
                        <td>" . formatTanggal($row['tanggal_perolehan']) . "</td>
                        <td>" . formatRupiah($row['nilai_awal']) . "</td>
                        <td>{$row['status']}</td>
                        <td>{$row['nama_kategori']}</td>
                        <td>{$row['nama_lokasi']}</td>
                        <td>" . ($row['nama_ruangan'] ? $row['nama_ruangan'] : '-') . "</td>
                        <td>";
                
                // Tampilkan informasi garansi
                if ($row['jenis_garansi']) {
                    echo ucfirst($row['jenis_garansi']);
                    if ($row['penyedia_garansi']) {
                        echo "<br><small>{$row['penyedia_garansi']}</small>";
                    }
                    if ($row['nomor_garansi']) {
                        echo "<br><small>No: {$row['nomor_garansi']}</small>";
                    }
                } else {
                    echo "-";
                }
                
                echo "</td>
                      <td>" . formatTanggal($row['garansi_berakhir']) . "</td>
                      <td>" . formatRupiah($row['nilai_susut']) . "</td>
                      <td>{$row['masa_manfaat']} Tahun</td>
                      </tr>";
            }

            $persentasePenyusutan = ($totalNilaiAset > 0) ? ($totalPenyusutan / $totalNilaiAset) * 100 : 0;

            echo "<tr><td colspan='10' style='text-align:right; font-weight:bold;'>Total Penyusutan</td><td colspan='2'>" . formatRupiah($totalPenyusutan) . "</td></tr>";
            echo "<tr><td colspan='10' style='text-align:right; font-weight:bold;'>Total Nilai Aset</td><td colspan='2'>" . formatRupiah($totalNilaiAset) . "</td></tr>";
            echo "<tr><td colspan='10' style='text-align:right; font-weight:bold;'>Persentase Penyusutan</td><td colspan='2'>" . number_format($persentasePenyusutan, 2) . "%</td></tr>";
            echo "</tbody></table></div>";

            $json_labels = json_encode($labels);
            $json_nilai_awal = json_encode($nilai_awal_list);
            $json_nilai_susut = json_encode($nilai_susut_list);
        } else {
            echo "<p>Tidak ada data ditemukan.</p>";
        }
        $conn->close();
        ?>

        <?php if (!empty($labels)) : ?>
        <div class="chart-container">
            <canvas id="grafikAset" width="800" height="400"></canvas>
        </div>
        
        <!--<div class="button-container">
            <button onclick="window.history.back()" class="btn btn-secondary">Kembali</button>
            <button onclick="downloadPDF()" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Download PDF</button>
            <button onclick="printPage()" class="btn btn-primary"><i class="fas fa-print"></i> Print</button>
        </div>-->
        <?php endif; ?>

        <script>
        // Fungsi untuk reset filter
        function resetFilter() {
            // Reset semua nilai form ke default
            document.getElementById('kategori').value = '';
            document.getElementById('lokasi').value = '';
            document.getElementById('status').value = '';
            document.getElementById('search').value = '';
            document.getElementById('tanggal_mulai').value = '';
            document.getElementById('tanggal_akhir').value = '';
            
            // Submit form kosong untuk reset filter
            document.forms[0].submit();
        }
        
        const labels = <?= $json_labels ?? '[]' ?>;
        const nilaiAwal = <?= $json_nilai_awal ?? '[]' ?>;
        const nilaiSusut = <?= $json_nilai_susut ?? '[]' ?>;

        // Format nilai untuk tooltip
        const formatTooltip = (value) => {
            return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        };

        const ctx = document.getElementById('grafikAset').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { 
                        label: 'Nilai Awal', 
                        data: nilaiAwal, 
                        backgroundColor: 'rgba(52, 152, 219, 0.7)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 1
                    },
                    { 
                        label: 'Nilai Penyusutan', 
                        data: nilaiSusut, 
                        backgroundColor: 'rgba(231, 76, 60, 0.7)',
                        borderColor: 'rgba(231, 76, 60, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            padding: 20,
                            font: {
                                size: 13
                            }
                        }
                    },
                    title: { 
                        display: true, 
                        text: 'Perbandingan Nilai Aset dan Penyusutan',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            bottom: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 0, 0, 0.8)',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 12
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += formatTooltip(context.raw);
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value/1000000).toFixed(1) + ' jt';
                                } else if (value >= 1000) {
                                    return 'Rp ' + (value/1000).toFixed(0) + ' rb';
                                }
                                return 'Rp ' + value;
                            },
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                },
                barPercentage: 0.6,
                categoryPercentage: 0.8
            }
        });

        // Fungsi untuk download PDF
        function downloadPDF() {
            window.jsPDF = window.jspdf.jsPDF;
            const doc = new jsPDF('landscape');
            
            // Add title
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(18);
            doc.text("Laporan Aset dan Penyusutan", 14, 15);
            
            // Add filter information
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(10);
            
            let filterText = "Filter: ";
            let filters = [];
            
            <?php if (!empty($nama_kategori_filter)): ?>
                filters.push("Kategori: <?= htmlspecialchars($nama_kategori_filter) ?>");
            <?php endif; ?>
            
            <?php if (!empty($nama_lokasi_filter)): ?>
                filters.push("Lokasi: <?= htmlspecialchars($nama_lokasi_filter) ?>");
            <?php endif; ?>
            
            <?php if (!empty($status_filter)): ?>
                filters.push("Status: <?= htmlspecialchars($status_filter) ?>");
            <?php endif; ?>
            
            <?php if (!empty($search_query)): ?>
                filters.push("Pencarian: <?= htmlspecialchars($search_query) ?>");
            <?php endif; ?>
            
            <?php if (!empty($tanggal_mulai) || !empty($tanggal_akhir)): ?>
                filters.push("Periode: <?= !empty($tanggal_mulai) ? htmlspecialchars($tanggal_mulai) : 'Awal' ?> hingga <?= !empty($tanggal_akhir) ? htmlspecialchars($tanggal_akhir) : 'Akhir' ?>");
            <?php endif; ?>
            
            if (filters.length === 0) {
                filterText += "Semua Data";
            } else {
                filterText += filters.join(", ");
            }
            
            doc.text(filterText, 14, 22);
            
            // Add current date
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            const today = new Date().toLocaleDateString('id-ID', options);
            doc.text(`Dicetak pada: ${today}`, 14, 29);
            
            // Add table
            doc.autoTable({ 
                html: '#tabelLaporan', 
                startY: 35,
                styles: {
                    fontSize: 8,
                    cellPadding: 2,
                    font: 'helvetica'
                },
                headStyles: {
                    fillColor: [41, 128, 185],
                    textColor: 255,
                    fontStyle: 'bold'
                },
                alternateRowStyles: {
                    fillColor: [245, 245, 245]
                },
                columnStyles: {
                    8: {cellWidth: 30}, // Kolom Garansi
                    9: {cellWidth: 20}  // Kolom Berlaku Sampai
                }
            });
            
            // Add chart image
            const canvas = document.getElementById('grafikAset');
            const chartImage = canvas.toDataURL('image/png', 1.0);
            doc.addPage('landscape');
            doc.setFontSize(16);
            doc.text("Grafik Perbandingan Nilai Aset dan Penyusutan", 14, 15);
            doc.addImage(chartImage, 'PNG', 15, 25, 260, 120);
            
            doc.save('laporan_aset.pdf');
        }

        function printPage() {
            window.print();
        }
        </script>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Sistem Manajemen Aset Kampus</p>
    </footer>
</div>
<?php include('../include/footer.php'); ?>
