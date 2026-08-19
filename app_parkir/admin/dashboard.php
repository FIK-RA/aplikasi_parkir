<?php
session_start();

// Mencegah Cache Browser
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Panggil Koneksi Database (Keluar 1 folder ke config/koneksi.php)
require_once '../config/koneksi.php';

// Proteksi Halaman: Cek apakah user sudah login & rolenya ADMIN
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$nama_user = $_SESSION['nama_lengkap'];

// Query Statistik Ringkas Secara Dinamis dari Database
$total_user      = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_user"))['total'] ?? 0;
$total_tarif     = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_tarif"))['total'] ?? 0;
$total_area      = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_area_parkir"))['total'] ?? 0;
$total_kendaraan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_kendaraan"))['total'] ?? 0;
$total_log       = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_log_aktivitas"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Parkir</title>
    <!-- Menghubungkan ke file dashboard.css -->
    <link rel="stylesheet" href="dashboard.css">

    <!-- Script Pengecek Tab Tutup via SessionStorage -->
    <script>
        if (!sessionStorage.getItem('is_logged_in')) {
            window.location.href = '../logout.php';
        }
    </script>
</head>
<body>

    <!-- Sidebar Admin -->
    <div class="sidebar">
        <h2>E-PARKIR ADMIN</h2>
        <ul class="menu">
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="user.php">User</a></li>
            <li><a href="tarif.php">Tarif Parkir</a></li>
            <li><a href="area.php">Area Parkir</a></li>
            <li><a href="kendaraan.php">Kendaraan</a></li>
            <li><a href="log_aktivitas.php">Log Aktivitas</a></li>
        </ul>
        <a href="../logout.php" class="btn-logout">Logout</a>
    </div>

    <!-- Main Content Area -->
    <div class="content">
        <!-- Header -->
        <div class="header">
            <div>
                <h2>Selamat Datang, <?= htmlspecialchars($nama_user); ?>! 👋</h2>
                <p>Panel Kontrol Utama Administrasi Sistem Manajemen Parkir</p>
            </div>
        </div>

        <h3 class="section-title">Menu Pengelolaan System</h3>
        
        <!-- Grid Menu / Cards Navigation -->
        <div class="grid-menu">
            <div class="card">
                <h3>User</h3>
                <p>Kelola data pengguna, petugas, dan akun owner. Total saat ini: <b><?= $total_user; ?> User</b>.</p>
                <a href="user.php">Kelola User &rarr;</a>
            </div>

            <div class="card">
                <h3>Tarif Parkir</h3>
                <p>Atur besaran biaya & tarif jam per jenis kendaraan. Total: <b><?= $total_tarif; ?> Tarif</b>.</p>
                <a href="tarif.php">Atur Tarif &rarr;</a>
            </div>

            <div class="card">
                <h3>Area Parkir</h3>
                <p>Kelola kapasitas dan daya tampung lokasi. Total: <b><?= $total_area; ?> Area</b>.</p>
                <a href="area.php">Kelola Area &rarr;</a>
            </div>

            <div class="card">
                <h3>Kendaraan</h3>
                <p>Master data daftar plat nomor & jenis kendaraan. Total: <b><?= $total_kendaraan; ?> Unit</b>.</p>
                <a href="kendaraan.php">Kelola Kendaraan &rarr;</a>
            </div>

            <div class="card">
                <h3>Log Aktivitas</h3>
                <p>Pantau seluruh riwayat log masuk & aktivitas pengguna. Total: <b><?= $total_log; ?> Log</b>.</p>
                <a href="log_aktivitas.php">Lihat Log Aktivitas &rarr;</a>
            </div>
        </div>
    </div>

</body>
</html>