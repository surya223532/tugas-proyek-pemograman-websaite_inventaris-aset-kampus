<?php
// File: /siman/include/sidebar_admin.php
$current_page = basename($_SERVER['PHP_SELF']);

function isActive($page_names, $current_page) {
    if (is_array($page_names)) {
        return in_array($current_page, $page_names) ? 'active' : '';
    }
    return ($current_page == $page_names) ? 'active' : '';
}
?>

<div class="sidebar" id="sidebar">
    <h2>Manajemen Aset</h2>
    <ul>
        <li class="<?= isActive('admin.php', $current_page) ?>">
            <a href='../adm/admin.php'>Dashboard</a>
        </li>
        <li class="<?= isActive('manajemen_pengguna.php', $current_page) ?>">
            <a href='../fitur/manajemen_pengguna.php'>Manajemen Pengguna</a>
        </li>
        <li class="submenu-item <?= isActive(['atur_aset.php', 'kategori_aset.php', 'lokasi_aset.php', 'lihat_aset.php'], $current_page) ?>">
            <a href="javascript:void(0);" onclick="toggleSubmenu('aset-lengkap')">Manajemen Aset</a>
            <ul class="submenu" id="aset-lengkap">
                <li class="<?= isActive('atur_aset.php', $current_page) ?>"><a href="../fitur/atur_aset.php">Atur Aset</a></li>
                <li class="<?= isActive('kategori_aset.php', $current_page) ?>"><a href="../fitur/kategori_aset.php">Kategori Aset</a></li>
                <li class="<?= isActive('lokasi_aset.php', $current_page) ?>"><a href="../fitur/lokasi_aset.php">Lokasi Aset</a></li>
                <li class="<?= isActive('lihat_aset.php', $current_page) ?>"><a href="../fitur/lihat_aset.php">Lihat Aset</a></li>
            </ul>
        </li>
        <li class="<?= isActive('penyusutan.php', $current_page) ?>">
            <a href="../fitur/penyusutan.php">Kelola Penyusutan</a>
        </li>
        <li class="<?= isActive('laporan.php', $current_page) ?>">
            <a href="../lap/laporan.php">Laporan & Statistik</a>
        </li>
        <li class="submenu-item <?= isActive(['backup.php', 'restore.php'], $current_page) ?>">
            <a href="javascript:void(0);" onclick="toggleSubmenu('backup-restore')">Backup dan Restore</a>
            <ul class="submenu" id="backup-restore">
                <li class="<?= isActive('backup.php', $current_page) ?>"><a href="../backup/backup.php">Backup Data</a></li>
                <li class="<?= isActive('restore.php', $current_page) ?>"><a href="../backup/restore.php">Restore Data</a></li>
            </ul>
        </li>
        <li class="submenu-item <?= isActive(['profile_popup', 'logout.php'], $current_page) ?>">
            <a href="javascript:void(0);" onclick="toggleSubmenu('pengaturan')">Pengaturan</a>
            <ul class="submenu" id="pengaturan">
                <li><a href="javascript:void(0);" onclick="showProfilePopup()">Profil</a></li>
                <li class="<?= isActive('logout.php', $current_page) ?>"><a href="/siman/logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</div>