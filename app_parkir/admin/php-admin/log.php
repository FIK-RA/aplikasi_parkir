<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once '../../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

// QUERY MEMANTAU AKTIVITAS ANGGOTA (PETUGAS, ADMIN, OWNER)
$log_aktivitas = mysqli_query($koneksi, "
    SELECT l.*, u.nama_lengkap, u.role 
    FROM tb_log_aktivitas l 
    LEFT JOIN tb_user u ON l.id_user = u.id_user 
    ORDER BY l.waktu_aktivitas DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - Admin</title>
    <link rel="stylesheet" href="../css-admin/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../css-admin/user.css?v=<?= time(); ?>">
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>E-PARKIR ADMIN</h2>
            <button class="btn-close-sidebar" id="btnCloseSidebar">&times;</button>
        </div>
        <ul class="menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="user.php">User</a></li>
            <li><a href="tarif.php">Tarif Parkir</a></li>
            <li><a href="area_parkir.php">Area Parkir</a></li>
            <li><a href="kendaraan.php">Kendaraan</a></li>
            <li><a href="log.php" class="active">Log Aktivitas</a></li>
        </ul>
        <a href="../../logout.php" class="btn-logout">Logout</a>
    </div>

    <div class="content">
        <div class="header">
            <div class="header-left">
                <button class="menu-toggle" id="btnToggle" title="Buka Menu">☰</button>
                <div>
                    <h2>Log & Audit Sistem 📋</h2>
                    <p>Pantau seluruh riwayat aktivitas login, cetak struk, transaksi petugas, & rekap owner.</p>
                </div>
            </div>
        </div>

        <!-- Tabel Log Riwayat Anggota -->
        <div style="overflow-x: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Waktu / Tanggal</th>
                        <th>Nama Anggota</th>
                        <th>Role</th>
                        <th>Deskripsi Aktivitas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while($row = mysqli_fetch_assoc($log_aktivitas)): 
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><span style="color: #64748b; font-size: 13px;"><?= date('d M Y, H:i:s', strtotime($row['waktu_aktivitas'])); ?></span></td>
                        <td><b><?= htmlspecialchars($row['nama_lengkap'] ?? 'System'); ?></b></td>
                        <td>
                            <span style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                <?= strtoupper($row['role'] ?? 'SYSTEM'); ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['aktivitas']); ?></td>
                    </tr>
                    <?php endwhile; ?>
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