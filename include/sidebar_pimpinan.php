<?php
// File: /siman/include/sidebar_pimpinan.php
?>
<div class="sidebar" id="sidebar">
    <h2>Manajemen Aset</h2>
    <ul>
        <li><a href="../lap/laporan.php">Laporan & Statistik</a></li>
        <li class="submenu-item">
            <a href="javascript:void(0);" onclick="toggleSubmenu('pengaturan')">Pengaturan</a>
            <ul class="submenu" id="pengaturan">
                <li><a href="javascript:void(0);" onclick="showProfilePopup()">Profil</a></li>
                <li><a href="/siman/logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</div>