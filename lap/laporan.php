<?php
session_start();
include('../include/koneksi.php'); // koneksi ke database 
$allowed_roles = ['admin', 'staf','pimpinan'];

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: login.php"); // Jika bukan role yang diizinkan, arahkan kembali ke login
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Aset</title>
    <link rel="stylesheet" type="text/css" href="../assets/style_pelaporan.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- jsPDF dan AutoTable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
</head>
<body>

<h2>Laporan Aset dan Penyusutan</h2>

<?php
include('../include/koneksi.php');

$query = "
    SELECT 
        a.id_aset, 
        a.nama_aset, 
        a.tanggal_perolehan, 
        a.nilai_awal, 
        a.status, 
        k.nama_kategori, 
        l.nama_lokasi, 
        p.nilai_susut,
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
    echo "<table id='tabelLaporan'>
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
                <td>" . $row['id_aset'] . "</td>
                <td>" . $row['nama_aset'] . "</td>
                <td>" . $row['tanggal_perolehan'] . "</td>
                <td>Rp " . number_format($row['nilai_awal'], 0, ',', '.') . "</td>
                <td>" . $row['status'] . "</td>
                <td>" . $row['nama_kategori'] . "</td>
                <td>" . $row['nama_lokasi'] . "</td>
                <td>Rp " . number_format($row['nilai_susut'], 0, ',', '.') . "</td>
                <td>" . $row['masa_manfaat'] . " Tahun</td>
              </tr>";
    }

    $persentasePenyusutan = ($totalNilaiAset > 0) ? ($totalPenyusutan / $totalNilaiAset) * 100 : 0;

    echo "<tr>
            <td colspan='7' style='text-align: right; font-weight: bold;'>Total Penyusutan</td>
            <td colspan='2'>Rp " . number_format($totalPenyusutan, 0, ',', '.') . "</td>
          </tr>";
    echo "<tr>
            <td colspan='7' style='text-align: right; font-weight: bold;'>Total Nilai Aset</td>
            <td colspan='2'>Rp " . number_format($totalNilaiAset, 0, ',', '.') . "</td>
          </tr>";
    echo "<tr>
            <td colspan='7' style='text-align: right; font-weight: bold;'>Persentase Penyusutan</td>
            <td colspan='2'>" . number_format($persentasePenyusutan, 2) . "%</td>
          </tr>";

    echo "</tbody></table>";

    $json_labels = json_encode($labels);
    $json_nilai_awal = json_encode($nilai_awal_list);
    $json_nilai_susut = json_encode($nilai_susut_list);
} else {
    echo "<p>Tidak ada data ditemukan.</p>";
}
$conn->close();
?>

<?php if (!empty($labels)) : ?>
<!-- Canvas Grafik -->
<canvas id="grafikAset" width="800" height="400"></canvas>

<!-- Navigasi & Tombol -->
<div style="text-align: center; margin-top: 20px;">
    <button onclick="window.history.back()">Kembali</button>
    <button onclick="downloadPDF()">Download PDF</button>
    <button onclick="printPage()">Print</button>
</div>
<?php endif; ?>

<script>
    const labels = <?= $json_labels ?? '[]' ?>;
    const nilaiAwal = <?= $json_nilai_awal ?? '[]' ?>;
    const nilaiSusut = <?= $json_nilai_susut ?? '[]' ?>;

    const ctx = document.getElementById('grafikAset').getContext('2d');
    const grafikAset = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Nilai Awal',
                    data: nilaiAwal,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                },
                {
                    label: 'Nilai Penyusutan',
                    data: nilaiSusut,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: {
                    display: true,
                    text: 'Perbandingan Nilai Aset dan Penyusutan'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });

    function downloadPDF() {
        window.jsPDF = window.jspdf.jsPDF;
        const doc = new jsPDF();
        doc.text("Laporan Aset dan Penyusutan", 20, 10);
        doc.autoTable({ html: '#tabelLaporan', startY: 20 });
        doc.save('laporan_aset.pdf');
    }

    function printPage() {
        window.print();
    }
</script>

</body>
</html>
