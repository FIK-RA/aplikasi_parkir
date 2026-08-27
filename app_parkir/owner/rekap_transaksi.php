<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../index.php");
    exit();
}

$tgl_mulai   = mysqli_real_escape_string($koneksi, $_GET['tgl_mulai'] ?? date('Y-m-01'));
$tgl_selesai = mysqli_real_escape_string($koneksi, $_GET['tgl_selesai'] ?? date('Y-m-d'));

// Query Data Transaksi Selesai (Keluar) Berdasarkan Tanggal Waktu Keluar
$query = "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, u.nama_lengkap AS petugas
          FROM tb_transaksi t
          JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
          JOIN tb_user u ON t.id_user = u.id_user
          WHERE t.status='keluar' AND DATE(t.waktu_keluar) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
          ORDER BY t.waktu_keluar DESC";

$result = mysqli_query($koneksi, $query);

// 1. Total Pendapatan / Omset Periode Dinamis
$q_total = mysqli_query($koneksi, "SELECT SUM(biaya_total) as total_omset FROM tb_transaksi WHERE status='keluar' AND DATE(waktu_keluar) BETWEEN '$tgl_mulai' AND '$tgl_selesai'");
$d_total = mysqli_fetch_assoc($q_total);
$total_omset = $d_total['total_omset'] ?? 0;

// 2. Count Kendaraan Motor Periode Ini
$q_motor = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_transaksi t JOIN tb_kendaraan k ON t.id_kendaraan=k.id_kendaraan WHERE t.status='keluar' AND LOWER(k.jenis_kendaraan)='motor' AND DATE(t.waktu_keluar) BETWEEN '$tgl_mulai' AND '$tgl_selesai'");
$total_motor = mysqli_fetch_assoc($q_motor)['total'] ?? 0;

// 3. Count Kendaraan Mobil Periode Ini
$q_mobil = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_transaksi t JOIN tb_kendaraan k ON t.id_kendaraan=k.id_kendaraan WHERE t.status='keluar' AND LOWER(k.jenis_kendaraan)='mobil' AND DATE(t.waktu_keluar) BETWEEN '$tgl_mulai' AND '$tgl_selesai'");
$total_mobil = mysqli_fetch_assoc($q_mobil)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Transaksi - Owner</title>
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
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="rekap_transaksi.php" class="active">Rekap Transaksi</a></li>
        </ul>
        <a href="../logout.php" class="btn-logout">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="header">
            <div class="header-left">
                <button class="menu-toggle" id="btnToggle" title="Buka Menu">☰</button>
                <div>
                    <h2>Laporan Rekap Transaksi 📊</h2>
                    <p>Filter dan analisis data transaksi parkir berdasarkan periode.</p>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="filter-box">
            <form action="" method="GET" class="filter-form">
                <div class="filter-group">
                    <label>Mulai Tanggal:</label>
                    <input type="date" name="tgl_mulai" value="<?= $tgl_mulai; ?>" required>
                </div>
                <div class="filter-group">
                    <label>Sampai Tanggal:</label>
                    <input type="date" name="tgl_selesai" value="<?= $tgl_selesai; ?>" required>
                </div>
                <button type="submit" class="btn-filter">Filter Laporan</button>
                <a href="cetak_laporan.php?tgl_mulai=<?= $tgl_mulai; ?>&tgl_selesai=<?= $tgl_selesai; ?>" target="_blank" class="btn-print">Cetak PDF / Print 🖨️</a>
            </form>
        </div>

        <!-- Summary Cards Periode -->
        <div class="grid-summary">
            <div class="card card-omset">
                <h3>Total Pendapatan (Omset)</h3>
                <div class="number">Rp <?= number_format($total_omset, 0, ',', '.'); ?></div>
            </div>
            <div class="card card-motor">
                <h3>Total Motor</h3>
                <div class="number"><?= number_format($total_motor, 0, ',', '.'); ?> Unit</div>
            </div>
            <div class="card card-mobil">
                <h3>Total Mobil</h3>
                <div class="number"><?= number_format($total_mobil, 0, ',', '.'); ?> Unit</div>
            </div>
        </div>

        <!-- Tabel Transaksi -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID Parkir</th>
                        <th>Plat Nomor</th>
                        <th>Jenis Kendaraan</th>
                        <th>Waktu Masuk</th>
                        <th>Waktu Keluar</th>
                        <th>Durasi</th>
                        <th>Total Biaya</th>
                        <th>Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><b>#<?= $row['id_parkir']; ?></b></td>
                            <td><b style="font-family: monospace; font-size: 14px;"><?= strtoupper($row['plat_nomor']); ?></b></td>
                            <td><?= ucfirst($row['jenis_kendaraan']); ?></td>
                            <td><?= $row['waktu_masuk']; ?></td>
                            <td><?= $row['waktu_keluar'] ?? '-'; ?></td>
                            <td><?= $row['durasi_jam'] ? $row['durasi_jam'].' Jam' : '-'; ?></td>
                            <td><b style="color: #16a34a;">Rp <?= number_format($row['biaya_total'] ?? 0, 0, ',', '.'); ?></b></td>
                            <td><?= htmlspecialchars($row['petugas']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align:center; color:#94a3b8;">Tidak ada data transaksi pada periode tanggal ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
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