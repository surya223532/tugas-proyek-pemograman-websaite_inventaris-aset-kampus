<?php
session_start();
include('../include/koneksi.php');

header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

if (isset($_GET['lokasi_id'])) {
    $lokasi_id = (int)$_GET['lokasi_id'];
    $query = "SELECT id_ruangan, nama_ruangan FROM ruangan WHERE id_lokasi = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $lokasi_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $ruangan = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ruangan[] = [
            'id' => $row['id_ruangan'],
            'nama' => htmlspecialchars($row['nama_ruangan'])
        ];
    }
    
    echo json_encode($ruangan);
} else {
    echo json_encode(['error' => 'Parameter lokasi_id tidak ditemukan']);
}
?>