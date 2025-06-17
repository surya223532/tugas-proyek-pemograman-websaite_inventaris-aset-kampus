<?php
// File: /siman/include/auth_check.php
session_start();
include('koneksi.php');

if (!isset($_SESSION['email'])) {
    header("Location: /siman/login.php");
    exit();
}

// Mapping hak akses untuk setiap fitur
$allowed_roles = [
    'manajemen_pengguna.php' => ['admin'],
    'atur_aset.php' => ['admin', 'staf'],
    'kategori_aset.php' => ['admin', 'staf'],
    'lokasi_aset.php' => ['admin', 'staf'],
    'lihat_aset.php' => ['admin', 'staf', 'pimpinan'],
    'penyusutan.php' => ['admin', 'staf'],
    'laporan.php' => ['admin', 'staf', 'pimpinan'],
    'backup.php' => ['admin'],
    'restore.php' => ['admin'],
    'profil.php' => ['admin', 'staf', 'pimpinan']
];

$current_file = basename($_SERVER['PHP_SELF']);

// Jika file saat ini ada dalam mapping, cek role
if (array_key_exists($current_file, $allowed_roles)) {
    if (!in_array($_SESSION['role'], $allowed_roles[$current_file])) {
        header("Location: /siman/unauthorized.php");
        exit();
    }
}

function includeSidebar() {
    $role = $_SESSION['role'];
    $sidebar_file = __DIR__ . "/sidebar_$role.php";
    
    if (file_exists($sidebar_file)) {
        include($sidebar_file);
    } else {
        die("Error: Sidebar tidak ditemukan untuk role ini");
    }
}

function getPageTitle($filename) {
    $titles = [
        'atur_aset.php' => 'Atur Aset',
        'kategori_aset.php' => 'Kelola Kategori',
        'lokasi_aset.php' => 'Kelola Lokasi',
        'lihat_aset.php' => 'Daftar Aset',
        'penyusutan.php' => 'Kelola Penyusutan',
        'manajemen_pengguna.php' => 'Manajemen Pengguna',
        'laporan.php' => 'Laporan & Statistik',
        'backup.php' => 'Backup Data',
        'restore.php' => 'Restore Data',
        'profil.php' => 'Profil Pengguna'
    ];
    
    return $titles[$filename] ?? 'Sistem Manajemen Aset';
}
?>