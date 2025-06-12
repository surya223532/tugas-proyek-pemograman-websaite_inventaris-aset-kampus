<?php
include('../include/koneksi.php');

if (isset($_GET['id'])) {
    $id_penyusutan = $_GET['id'];

    // Query untuk menghapus data berdasarkan id_penyusutan
    $query = "DELETE FROM penyusutan WHERE id_penyusutan = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_penyusutan);
    mysqli_stmt_execute($stmt);

    // Redirect kembali ke halaman penyusutan
    header('Location: penyusutan.php');
    exit();
}
?>
