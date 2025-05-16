<?php
session_start();
include('../include/koneksi.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_aset = $_POST['id_aset'];
    $tahun = $_POST['tahun'];

    // Ambil data aset dari database
    $query_aset = "SELECT * FROM aset WHERE id_aset = '$id_aset'";
    $result_aset = mysqli_query($conn, $query_aset);
    $aset = mysqli_fetch_assoc($result_aset);

    if ($aset) {
        $nilai_awal = $aset['nilai_awal'];
        $tanggal_perolehan = $aset['tanggal_perolehan'];
        $tahun_perolehan = date('Y', strtotime($tanggal_perolehan));
        $masa_manfaat = $aset['masa_manfaat']; // Mengambil masa manfaat dari data aset
        $umur = max(0, $tahun - $tahun_perolehan);

        // Hitung nilai penyusutan per tahun
        $nilai_susut = $masa_manfaat > 0 ? $nilai_awal / $masa_manfaat : 0;

        // Hitung nilai sisa berdasarkan umur
        if ($umur >= $masa_manfaat) {
            $nilai_sisa = 0;
        } elseif ($umur >= 1) {
            $nilai_sisa = $nilai_awal - ($nilai_susut * $umur);
        } else {
            $nilai_sisa = $nilai_awal;
        }

        // Cek apakah penyusutan untuk aset & tahun ini sudah ada
        $cek = mysqli_query($conn, "SELECT * FROM penyusutan WHERE id_aset = '$id_aset' AND tahun = '$tahun'");
        if (mysqli_num_rows($cek) > 0) {
            $_SESSION['error'] = "Penyusutan untuk aset ini di tahun $tahun sudah ada.";
        } else {
            // Simpan data penyusutan
            $query_insert = "INSERT INTO penyusutan (id_aset, tahun, nilai_susut, nilai_sisa)
                             VALUES ('$id_aset', '$tahun', '$nilai_susut', '$nilai_sisa')";
            if (mysqli_query($conn, $query_insert)) {
                $_SESSION['success'] = "Data penyusutan berhasil disimpan.";
            } else {
                $_SESSION['error'] = "Gagal menyimpan data penyusutan.";
            }
        }
    } else {
        $_SESSION['error'] = "Data aset tidak ditemukan.";
    }
} else {
    $_SESSION['error'] = "Permintaan tidak valid.";
}

header('Location: penyusutan.php');
exit();
