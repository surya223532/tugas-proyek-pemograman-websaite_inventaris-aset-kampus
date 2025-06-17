<?php
// File: /siman/include/sidebar_pimpinan.php
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
        <li class="<?= isActive('pimpinan.php', $current_page) ?>">
            <a href="../pimpinan/pimpinan.php">Dashboard</a>
        </li>
        <li class="<?= isActive('laporan.php', $current_page) ?>">
            <a href="../lap/laporan.php">Laporan & Statistik</a>
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