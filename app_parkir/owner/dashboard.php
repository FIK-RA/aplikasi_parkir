<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// PERBAIKAN: Naik 1 tingkat ke folder config
require_once '../config/koneksi.php';

// Validasi Akses Owner
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../index.php");
    exit();
}

$nama_user = $_SESSION['nama_lengkap'];

// Stat 1: Total Omset Bulan Ini
$q_omset_bulan = mysqli_query($koneksi, "SELECT SUM(biaya_total) AS total FROM tb_transaksi WHERE status='keluar' AND MONTH(waktu_keluar) = MONTH(CURRENT_DATE()) AND YEAR(waktu_keluar) = YEAR(CURRENT_DATE())");
$omset_bulan = mysqli_fetch_assoc($q_omset_bulan)['total'] ?? 0;

// Stat 2: Total Transaksi Selesai Hari Ini
$q_trx_today = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tb_transaksi WHERE status='keluar' AND DATE(waktu_keluar) = CURDATE()");
$trx_today = mysqli_fetch_assoc($q_trx_today)['total'] ?? 0;

// Stat 3: Total Kendaraan Parkir Aktif Saat Ini
$q_aktif = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tb_transaksi WHERE status='masuk'");
$parkir_aktif = mysqli_fetch_assoc($q_aktif)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Owner - E-Parkir</title>
    <link rel="stylesheet" href="css-owner/dashboard.css?v=<?= time(); ?>">
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <!-- Sidebar Owner -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>E-PARKIR OWNER</h2>
            <button class="btn-close-sidebar" id="btnCloseSidebar">&times;</button>
        </div>
        <ul class="menu">
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="rekap_transaksi.php">Rekap Transaksi</a></li>
        </ul>
        <a href="../logout.php" class="btn-logout">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="header">
            <div class="header-left">
                <button class="menu-toggle" id="btnToggle" title="Buka Menu">☰</button>
                <div>
                    <h2>Selamat Datang, <?= htmlspecialchars($nama_user); ?>! 👋</h2>
                    <p>Ringkasan Eksekutif & Monitoring Laporan Pendapatan Parkir</p>
                </div>
            </div>
        </div>

        <div class="grid-summary">
            <div class="card card-omset">
                <h3>Omset Bulan Ini</h3>
                <div class="number">Rp <?= number_format($omset_bulan, 0, ',', '.'); ?></div>
            </div>
            <div class="card card-transaksi">
                <h3>Transaksi Selesai Hari Ini</h3>
                <div class="number"><?= number_format($trx_today, 0, ',', '.'); ?> Transaksi</div>
            </div>
            <div class="card card-mobil">
                <h3>Kendaraan Parkir Aktif</h3>
                <div class="number"><?= number_format($parkir_aktif, 0, ',', '.'); ?> Unit</div>
            </div>
        </div>

        <div style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <h3 style="color: #0f172a; margin-bottom: 10px;">Laporan & Analisis Rekap</h3>
            <p style="color: #64748b; font-size: 14px; margin-bottom: 15px;">Akses laporan lengkap pendapatan parkir berdasarkan rentang tanggal dan cetak laporan resmi.</p>
            <a href="rekap_transaksi.php" class="btn-filter" style="text-decoration: none; display: inline-block;">Buka Rekap Transaksi 📊</a>
        </div>
    </div>

    <script>
        const btnToggle = document.getElementById('btnToggle');
        const btnCloseSidebar = document.getElementById('btnCloseSidebar');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function openSidebar() { sidebar.classList.add('show'); overlay.classList.add('show'); }
        function closeSidebar() { sidebar.classList.remove('show'); overlay.classList.remove('show'); }

        btnToggle.addEventListener('click', openSidebar);
        btnCloseSidebar.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);
    </script>
</body>
</html>