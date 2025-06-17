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
?>

<?php include('../include/header.php'); ?>
<?php include($_SESSION['role'] === 'admin' ? '../include/sidebar_admin.php' : ($_SESSION['role'] === 'pimpinan' ? '../include/sidebar_pimpinan.php' : '../include/sidebar_staf.php')); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<!-- Konten Utama -->
<div class="main-content">
    <header>
        <h2>Laporan Aset dan Penyusutan</h2>
    </header>

    <main>
        <?php
        $query = "
            SELECT 
                a.id_aset, 
                a.nama_aset, 
                a.tanggal_perolehan, 
                COALESCE(a.nilai_awal, 0) as nilai_awal, 
                a.status, 
                k.nama_kategori, 
                l.nama_lokasi, 
                COALESCE(p.nilai_susut, 0) as nilai_susut,
                a.masa_manfaat
            FROM aset a
            LEFT JOIN kategori k ON a.kategori_id = k.id_kategori
            LEFT JOIN lokasi l ON a.lokasi_id = l.id_lokasi
            LEFT JOIN penyusutan p ON a.id_aset = p.id_aset
        ";

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
                        <td>" . date('d-m-Y', strtotime($row['tanggal_perolehan'])) . "</td>
                        <td>" . formatRupiah($row['nilai_awal']) . "</td>
                        <td>{$row['status']}</td>
                        <td>{$row['nama_kategori']}</td>
                        <td>{$row['nama_lokasi']}</td>
                        <td>" . formatRupiah($row['nilai_susut']) . "</td>
                        <td>{$row['masa_manfaat']} Tahun</td>
                      </tr>";
            }

            $persentasePenyusutan = ($totalNilaiAset > 0) ? ($totalPenyusutan / $totalNilaiAset) * 100 : 0;

            echo "<tr><td colspan='7' style='text-align:right; font-weight:bold;'>Total Penyusutan</td><td colspan='2'>" . formatRupiah($totalPenyusutan) . "</td></tr>";
            echo "<tr><td colspan='7' style='text-align:right; font-weight:bold;'>Total Nilai Aset</td><td colspan='2'>" . formatRupiah($totalNilaiAset) . "</td></tr>";
            echo "<tr><td colspan='7' style='text-align:right; font-weight:bold;'>Persentase Penyusutan</td><td colspan='2'>" . number_format($persentasePenyusutan, 2) . "%</td></tr>";
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
        
        <div class="button-container">
            <button onclick="window.history.back()" class="btn btn-secondary">Kembali</button>
            <button onclick="downloadPDF()" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Download PDF</button>
            <button onclick="printPage()" class="btn btn-primary"><i class="fas fa-print"></i> Print</button>
        </div>
        <?php endif; ?>

        <script>
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
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
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
            
            // Add current date
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            const today = new Date().toLocaleDateString('id-ID', options);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(10);
            doc.text(`Dicetak pada: ${today}`, 14, 22);
            
            // Add table
            doc.autoTable({ 
                html: '#tabelLaporan', 
                startY: 30,
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