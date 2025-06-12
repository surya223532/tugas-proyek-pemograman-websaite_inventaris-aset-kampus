<?php
session_start();
include('../include/koneksi.php'); // Koneksi ke database

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /siman/login.php");
    exit();
}

// Set header agar file langsung di-download sebagai .sql
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="backup_' . date('Ymd_His') . '.sql"');

// Ambil semua tabel
$query = "SHOW TABLES";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_row($result)) {
    $table = $row[0];

    // Struktur tabel
    $createTableResult = mysqli_query($conn, "SHOW CREATE TABLE `$table`");
    $createTable = mysqli_fetch_assoc($createTableResult)['Create Table'];

    echo "\n-- ---------------------------------------------------\n";
    echo "-- Struktur tabel untuk `$table`\n";
    echo "-- ---------------------------------------------------\n\n";
    echo "DROP TABLE IF EXISTS `$table`;\n";
    echo $createTable . ";\n\n";

    // Data isi tabel
    $selectQuery = mysqli_query($conn, "SELECT * FROM `$table`");
    if (mysqli_num_rows($selectQuery) > 0) {
        echo "-- Dumping data untuk tabel `$table`\n\n";
    }

    while ($data = mysqli_fetch_assoc($selectQuery)) {
        $columns = array_map(function($col) {
            return "`" . $col . "`";
        }, array_keys($data));

        $values = array_map(function($val) use ($conn) {
            if ($val === null) return "NULL";
            return "'" . mysqli_real_escape_string($conn, $val) . "'";
        }, array_values($data));

        $columnsList = implode(", ", $columns);
        $valuesList = implode(", ", $values);

        echo "INSERT INTO `$table` ($columnsList) VALUES ($valuesList);\n";
    }

    echo "\n\n";
}

exit();
?>
