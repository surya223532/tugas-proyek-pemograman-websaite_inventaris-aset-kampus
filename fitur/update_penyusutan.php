<?php
session_start();
include('../include/koneksi.php');

// Cek apakah data POST ada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_aset'])) {
    $id_aset = $_POST['id_aset'];
    $nama_aset = $_POST['nama_aset'];
    $tanggal_perolehan = $_POST['tanggal_perolehan'];
    $nilai_awal = $_POST['nilai_awal'];
    $nilai_susut = $_POST['nilai_susut'];

    // Validasi input
    if (!empty($id_aset) && !empty($nama_aset) && !empty($tanggal_perolehan) && !empty($nilai_awal) && !empty($nilai_susut)) {
        // Cek koneksi database
        if (!$conn) {
            die("Koneksi gagal: " . mysqli_connect_error());
        }

        // Query untuk mengupdate data aset
        $query_update = "UPDATE aset SET nama_aset = ?, tanggal_perolehan = ?, nilai_awal = ?, nilai_susut = ? WHERE id_aset = ?";
        $stmt_update = mysqli_prepare($conn, $query_update);

        if ($stmt_update) {
            // Bind parameter dan eksekusi query
            mysqli_stmt_bind_param($stmt_update, 'ssddi', $nama_aset, $tanggal_perolehan, $nilai_awal, $nilai_susut, $id_aset);
            if (mysqli_stmt_execute($stmt_update)) {
                // Jika berhasil, arahkan kembali ke halaman kelola_penyusutan.php
                header('Location: penyusutan.php');
                exit();
            } else {
                echo "Gagal mengupdate data aset.";
            }
        } else {
            echo "Error preparing update query: " . mysqli_error($conn);
        }
    } else {
        echo "Semua data harus diisi.";
    }
} else {
    echo "Data tidak valid.";
}
?>
