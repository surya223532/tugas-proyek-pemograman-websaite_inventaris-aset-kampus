<?php
// File: /siman/include/header.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard <?= ucfirst($_SESSION['role']) ?></title>
    <link rel="stylesheet" href="../assets/nadmin.css">
    <script src="../assets/admin.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    function toggleSubmenu(id) {
        var submenu = document.getElementById(id);
        submenu.style.display = submenu.style.display === "block" ? "none" : "block";
    }

    function showProfilePopup() {
        document.getElementById('profile-popup').style.display = 'block';
        document.getElementById('popup-overlay').style.display = 'block';
        console.log("Menampilkan popup dan overlay");
    }

    function closeProfilePopup() {
        document.getElementById('profile-popup').style.display = 'none';
        document.getElementById('popup-overlay').style.display = 'none';
        console.log("Menutup popup dan overlay");
    }
    </script>
</head>
<body>
    <button class="toggle-btn" id="sidebarToggle">
        ☰
    </button>