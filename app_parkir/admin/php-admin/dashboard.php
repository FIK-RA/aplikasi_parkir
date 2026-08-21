<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 1. Jalur koneksi diperbaiki (naik 2 folder)
require_once '../../config/koneksi.php';

// 2. Jalur index.php diperbaiki
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$nama_user = $_SESSION['nama_lengkap'];

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
    <!-- 3. Jalur CSS diperbaiki mengarah ke folder css-admin -->
    <link rel="stylesheet" href="../css-admin/dashboard.css?v=<?= time(); ?>">
    <script>
        if (!sessionStorage.getItem('is_logged_in')) {
            window.location.href = '../../logout.php';
        }
    </script>
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>E-PARKIR ADMIN</h2>
            <button class="btn-close-sidebar" id="btnCloseSidebar">&times;</button>
        </div>
        <ul class="menu">
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="user.php">User</a></li>
            <li><a href="tarif.php">Tarif Parkir</a></li>
            <!-- 4. Penyesuaian nama file link sidebar -->
            <li><a href="area_parkir.php">Area Parkir</a></li>
            <li><a href="kendaraan.php">Kendaraan</a></li>
            <li><a href="log.php">Log Aktivitas</a></li>
        </ul>
        <a href="../../logout.php" class="btn-logout">Logout</a>
    </div>

    <div class="content">
        <div class="header">
            <div class="header-left">
                <button class="menu-toggle" id="btnToggle" title="Buka Menu">☰</button>
                <div>
                    <h2>Selamat Datang, <?= htmlspecialchars($nama_user); ?>! 👋</h2>
                    <p>Panel Kontrol Utama Administrasi Sistem Manajemen Parkir</p>
                </div>
            </div>
        </div>

        <h3 class="section-title">Menu Pengelolaan System</h3>
        
        <div class="grid-menu">
            <a href="user.php" class="card-link">
                <div class="card">
                    <h3>User</h3>
                    <p>Kelola data pengguna, petugas, dan akun owner. Total: <b><?= $total_user; ?> User</b>.</p>
                    <span class="card-action">Kelola User &rarr;</span>
                </div>
            </a>
            <a href="tarif.php" class="card-link">
                <div class="card">
                    <h3>Tarif Parkir</h3>
                    <p>Atur besaran biaya & tarif jam per jenis kendaraan. Total: <b><?= $total_tarif; ?> Tarif</b>.</p>
                    <span class="card-action">Atur Tarif &rarr;</span>
                </div>
            </a>
            <a href="area_parkir.php" class="card-link">
                <div class="card">
                    <h3>Area Parkir</h3>
                    <p>Kelola kapasitas dan daya tampung lokasi. Total: <b><?= $total_area; ?> Area</b>.</p>
                    <span class="card-action">Kelola Area &rarr;</span>
                </div>
            </a>
            <a href="kendaraan.php" class="card-link">
                <div class="card">
                    <h3>Kendaraan</h3>
                    <p>Master data daftar plat nomor & jenis kendaraan. Total: <b><?= $total_kendaraan; ?> Unit</b>.</p>
                    <span class="card-action">Kelola Kendaraan &rarr;</span>
                </div>
            </a>
            <a href="log.php" class="card-link">
                <div class="card">
                    <h3>Log Aktivitas</h3>
                    <p>Pantau seluruh riwayat log masuk & aktivitas pengguna. Total: <b><?= $total_log; ?> Log</b>.</p>
                    <span class="card-action">Lihat Log Aktivitas &rarr;</span>
                </div>
            </a>
        </div>
    </div>

    <script>
        const btnToggle = document.getElementById('btnToggle');
        const btnCloseSidebar = document.getElementById('btnCloseSidebar');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function openSidebar() {
            sidebar.classList.add('show');
            overlay.classList.add('show');
        }

        function closeSidebar() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        }

        btnToggle.addEventListener('click', openSidebar);
        btnCloseSidebar.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);
    </script>
</body>
</html>