<?php
ob_start();
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once '../../config/koneksi.php';

$tab_id = $_GET['tab_id'] ?? '';

// Validasi Sesi Multi-Tab & Fallback Sesi Global
if (!empty($tab_id) && isset($_SESSION['tabs'][$tab_id])) {
    $user_session = $_SESSION['tabs'][$tab_id];
} elseif (isset($_SESSION['id_user'])) {
    $user_session = $_SESSION;
} else {
    header("Location: ../../index.php");
    exit();
}

if (strtolower($user_session['role'] ?? '') !== 'petugas') {
    header("Location: ../../index.php");
    exit();
}

$nama_user = $user_session['nama_lengkap'];

// 1. Query Total Kendaraan Sedang Parkir (Status: masuk)
$q_aktif = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tb_transaksi WHERE status = 'masuk'");
$parkir_aktif = mysqli_fetch_assoc($q_aktif)['total'] ?? 0;

// 2. Query Total Kendaraan Keluar Hari Ini (Status: keluar & tanggal hari ini)
$q_selesai = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tb_transaksi WHERE status = 'keluar' AND DATE(waktu_keluar) = CURDATE()");
$selesai_hari_ini = mysqli_fetch_assoc($q_selesai)['total'] ?? 0;

// 3. Query Total Pendapatan Hari Ini
$q_pendapatan = mysqli_query($koneksi, "SELECT SUM(biaya_total) AS total FROM tb_transaksi WHERE status = 'keluar' AND DATE(waktu_keluar) = CURDATE()");
$total_pendapatan_hari_ini = mysqli_fetch_assoc($q_pendapatan)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas - E-Parkir</title>
    <link rel="stylesheet" href="../css-petugas/dashboard.css?v=<?= time(); ?>">
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <!-- Sidebar Petugas -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>E-PARKIR PETUGAS</h2>
            <button class="btn-close-sidebar" id="btnCloseSidebar">&times;</button>
        </div>
        <ul class="menu">
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="transaksi.php">Transaksi Parkir</a></li>
        </ul>
        <a href="../../logout.php" class="btn-logout">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="header">
            <div class="header-left">
                <button class="menu-toggle" id="btnToggle" title="Buka Menu">☰</button>
                <div>
                    <h2>Selamat Datang, <?= htmlspecialchars($nama_user); ?>! 👷‍♂️</h2>
                    <p>Panel Transaksi & Pelayanan Entry-Exit Parkir Kendaraan</p>
                </div>
            </div>
        </div>

        <h3 style="margin-bottom: 20px; color: #065f46;">Ringkasan Operasional Hari Ini</h3>
        
        <div class="grid-menu">
            <div class="card">
                <h3>Kendaraan Sedang Parkir</h3>
                <div class="number"><?= number_format($parkir_aktif, 0, ',', '.'); ?> Unit</div>
                <p style="font-size: 12px; color: #64748b; margin-top: 5px;">Masih berada di dalam area</p>
            </div>
            <div class="card">
                <h3>Kendaraan Keluar Hari Ini</h3>
                <div class="number"><?= number_format($selesai_hari_ini, 0, ',', '.'); ?> Unit</div>
                <p style="font-size: 12px; color: #64748b; margin-top: 5px;">Transaksi selesai diproses</p>
            </div>
            <div class="card">
                <h3>Pendapatan Hari Ini</h3>
                <div class="number">Rp <?= number_format($total_pendapatan_hari_ini, 0, ',', '.'); ?></div>
                <p style="font-size: 12px; color: #64748b; margin-top: 5px;">Total transaksi terkumpul</p>
            </div>
        </div>

        <div style="margin-top: 30px; background: white; padding: 20px; border-radius: 12px; border: 1px solid #d1fae5;">
            <h4 style="color: #065f46; margin-bottom: 10px;">Aksi Cepat</h4>
            <a href="transaksi.php" style="display: inline-block; background: #10b981; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600;">+ Input / Out Transaksi Parkir</a>
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

        // Pertahankan tab_id pada setiap navigasi menu
        const tabId = new URLSearchParams(window.location.search).get('tab_id');
        if (tabId) {
            document.querySelectorAll('a').forEach(link => {
                const url = new URL(link.href, window.location.origin);
                if (url.origin === window.location.origin && !link.href.includes('logout.php')) {
                    url.searchParams.set('tab_id', tabId);
                    link.href = url.toString();
                }
            });
        }
    </script>
</body>
</html>