<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../index.php");
    exit();
}

$tgl_mulai   = mysqli_real_escape_string($koneksi, $_GET['tgl_mulai'] ?? date('Y-m-01'));
$tgl_selesai = mysqli_real_escape_string($koneksi, $_GET['tgl_selesai'] ?? date('Y-m-d'));

// Query menyambung dengan Rekap (Berdasarkan Waktu Keluar)
$query = "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, u.nama_lengkap AS petugas
          FROM tb_transaksi t
          JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
          JOIN tb_user u ON t.id_user = u.id_user
          WHERE t.status='keluar' AND DATE(t.waktu_keluar) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
          ORDER BY t.waktu_keluar ASC";

$result = mysqli_query($koneksi, $query);

$q_total = mysqli_query($koneksi, "SELECT SUM(biaya_total) as total_omset FROM tb_transaksi WHERE status='keluar' AND DATE(waktu_keluar) BETWEEN '$tgl_mulai' AND '$tgl_selesai'");
$d_total = mysqli_fetch_assoc($q_total);
$total_omset = $d_total['total_omset'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Parkir Periode (<?= $tgl_mulai; ?> - <?= $tgl_selesai; ?>)</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; color: #000; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 20px; }
        .header p { margin: 4px 0 0 0; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 8px; font-size: 12px; text-align: left; }
        th { background: #f2f2f2; }
        .summary-box { margin-top: 20px; float: right; width: 250px; font-size: 13px; }
        .summary-box td { border: none; padding: 4px; }
        .signature { margin-top: 60px; float: right; width: 200px; text-align: center; font-size: 12px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>LAPORAN REKAP PENDAPATAN PARKIR</h2>
        <p>Periode Tanggal: <b><?= date('d/m/Y', strtotime($tgl_mulai)); ?></b> s/d <b><?= date('d/m/Y', strtotime($tgl_selesai)); ?></b></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Tiket</th>
                <th>Plat Nomor</th>
                <th>Jenis</th>
                <th>Waktu Masuk</th>
                <th>Waktu Keluar</th>
                <th>Durasi</th>
                <th>Biaya Total</th>
                <th>Petugas</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (mysqli_num_rows($result) > 0):
                while($row = mysqli_fetch_assoc($result)): 
            ?>
            <tr>
                <td><?= $no++; ?></td>
                <td>#<?= $row['id_parkir']; ?></td>
                <td><b><?= strtoupper($row['plat_nomor']); ?></b></td>
                <td><?= ucfirst($row['jenis_kendaraan']); ?></td>
                <td><?= $row['waktu_masuk']; ?></td>
                <td><?= $row['waktu_keluar'] ?? '-'; ?></td>
                <td><?= $row['durasi_jam'] ? $row['durasi_jam'].' Jam' : '-'; ?></td>
                <td>Rp <?= number_format($row['biaya_total'] ?? 0, 0, ',', '.'); ?></td>
                <td><?= htmlspecialchars($row['petugas']); ?></td>
            </tr>
            <?php 
                endwhile; 
            else:
            ?>
            <tr><td colspan="9" style="text-align: center;">Tidak ada data transaksi.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="summary-box">
        <table>
            <tr>
                <td><b>Total Omset Periode:</b></td>
                <td style="text-align: right;"><b>Rp <?= number_format($total_omset, 0, ',', '.'); ?></b></td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <div class="signature">
        <p>Dicetak pada: <?= date('d/m/Y H:i'); ?></p>
        <br><br><br>
        <p><b>( Owner / Pimpinan )</b></p>
    </div>

    <script>
        // Trigger dialog print setelah halaman selesai dimuat sepenuhnya
        window.onload = function() {
            window.print();
        };

        // Menutup tab otomatis setelah proses print Selesai atau di-Cancel
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>