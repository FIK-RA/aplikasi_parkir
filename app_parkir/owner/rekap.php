<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../index.php");
    exit();
}

$tgl_mulai   = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');

$query = "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, u.nama_lengkap AS petugas
          FROM tb_transaksi t
          JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
          JOIN tb_user u ON t.id_user = u.id_user
          WHERE DATE(t.waktu_masuk) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
          ORDER BY t.waktu_masuk DESC";

$result = mysqli_query($koneksi, $query);

// Total Pendapatan
$q_total = mysqli_query($koneksi, "SELECT SUM(biaya_total) as total_omset FROM tb_transaksi WHERE status='keluar' AND DATE(waktu_masuk) BETWEEN '$tgl_mulai' AND '$tgl_selesai'");
$d_total = mysqli_fetch_assoc($q_total);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Transaksi - Owner</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f8fafc; }
        .filter-box { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { border: 1px solid #cbd5e1; padding: 10px; text-align: left; }
        th { background: #0f172a; color: white; }
        .summary { background: #22c55e; color: white; padding: 15px; border-radius: 8px; margin-top: 15px; width: 300px; }
    </style>
</head>
<body>
    <h2>Laporan Rekap Transaksi Parkir</h2>

    <div class="filter-box">
        <form action="" method="GET">
            <label>Mulai Tanggal:</label>
            <input type="date" name="tgl_mulai" value="<?= $tgl_mulai; ?>" required>
            
            <label>Sampai Tanggal:</label>
            <input type="date" name="tgl_selesai" value="<?= $tgl_selesai; ?>" required>
            
            <button type="submit" style="padding: 6px 12px; background: #2563eb; color: white; border: none;">Filter Laporan</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Parkir</th>
                <th>Plat Nomor</th>
                <th>Jenis</th>
                <th>Waktu Masuk</th>
                <th>Waktu Keluar</th>
                <th>Durasi</th>
                <th>Total Biaya</th>
                <th>Petugas</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td>#<?= $row['id_parkir']; ?></td>
                <td><b><?= $row['plat_nomor']; ?></b></td>
                <td><?= ucfirst($row['jenis_kendaraan']); ?></td>
                <td><?= $row['waktu_masuk']; ?></td>
                <td><?= $row['waktu_keluar'] ?? 'Masih Parkir'; ?></td>
                <td><?= $row['durasi_jam'] ? $row['durasi_jam'].' Jam' : '-'; ?></td>
                <td>Rp <?= number_format($row['biaya_total'] ?? 0); ?></td>
                <td><?= $row['petugas']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="summary">
        <h3>Total Pendapatan:</h3>
        <h2>Rp <?= number_format($d_total['total_omset'] ?? 0); ?></h2>
    </div>
</body>
</html>