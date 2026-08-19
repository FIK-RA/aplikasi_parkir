<?php
session_start();

// Mencegah Cache Browser
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Panggil Koneksi (Keluar 1 folder ke config/koneksi.php)
require_once '../config/koneksi.php';

// Cek apakah user sudah login & rolenya ADMIN
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$nama_user = $_SESSION['nama_lengkap'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Sistem Parkir</title>
    <!-- Script Pengecek Tab Tutup via SessionStorage -->
    <script>
        if (!sessionStorage.getItem('is_logged_in')) {
            window.location.href = '../logout.php';
        }
    </script>
</head>
<body>
    <h1>Selamat Datang, <?= htmlspecialchars($nama_user); ?>! 👋</h1>
    <p>Ini adalah halaman kontrol admin.</p>
    <a href="../logout.php">Logout</a>
</body>
</html>