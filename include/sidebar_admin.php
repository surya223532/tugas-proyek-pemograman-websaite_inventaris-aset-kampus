<?php
// File: /siman/include/sidebar_admin.php
?>
<div class="sidebar" id="sidebar">
    <h2>Manajemen Aset</h2>
    <ul>
        <li><a href="../fitur/manajemen_pengguna.php">Manajemen Pengguna</a></li>
        <li class="submenu-item">
            <a href="javascript:void(0);" onclick="toggleSubmenu('aset-lengkap')">Manajemen Aset</a>
            <ul class="submenu" id="aset-lengkap">
                <li><a href="../fitur/atur_aset.php">Atur Aset</a></li>
                <li><a href="../fitur/kategori_aset.php">Kategori Aset</a></li>
                <li><a href="../fitur/lokasi_aset.php">Lokasi Aset</a></li>
                <li><a href="../fitur/lihat_aset.php">Lihat Aset</a></li>
            </ul>
        </li>
        <li><a href="../fitur/penyusutan.php">Kelola Penyusutan</a></li>
        <li><a href="../lap/laporan.php">Laporan & Statistik</a></li>
        <li class="submenu-item">
            <a href="javascript:void(0);" onclick="toggleSubmenu('backup-restore')">Backup dan Restore</a>
            <ul class="submenu" id="backup-restore">
                <li><a href="../backup/backup.php">Backup Data</a></li>
                <li><a href="../backup/restore.php">Restore Data</a></li>
            </ul>
        </li>
        <li class="submenu-item">
            <a href="javascript:void(0);" onclick="toggleSubmenu('pengaturan')">Pengaturan</a>
            <ul class="submenu" id="pengaturan">
                <li><a href="javascript:void(0);" onclick="showProfilePopup()">Profil</a></li>
                <li><a href="/siman/logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</div>