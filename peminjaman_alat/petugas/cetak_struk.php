<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['id_petugas'])) {
    exit("Akses ditolak.");
}

$tipe = $_GET['tipe'] ?? '';
$id   = intval($_GET['id'] ?? 0);

if ($tipe == 'peminjaman') {
    $q = mysqli_query($koneksi, "SELECT peminjaman.*, peminjam.nama_lengkap, peminjam.no_telepon, alat.nama_alat 
                                 FROM peminjaman 
                                 JOIN peminjam ON peminjaman.id_user = peminjam.id_peminjam 
                                 JOIN alat ON peminjaman.id_alat = alat.id_alat 
                                 WHERE peminjaman.id_peminjaman = '$id'");
    $data = mysqli_fetch_assoc($q);
    $judul = "STRUK PEMINJAMAN ALAT";
} elseif ($tipe == 'pengembalian') {
    $q = mysqli_query($koneksi, "SELECT pengembalian.*, peminjaman.id_peminjaman, peminjam.nama_lengkap, alat.nama_alat, peminjaman.jumlah 
                                 FROM pengembalian 
                                 JOIN peminjaman ON pengembalian.id_peminjaman = peminjaman.id_peminjaman 
                                 JOIN peminjam ON peminjaman.id_user = peminjam.id_peminjam 
                                 JOIN alat ON peminjaman.id_alat = alat.id_alat 
                                 WHERE pengembalian.id_pengembalian = '$id'");
    $data = mysqli_fetch_assoc($q);
    $judul = "STRUK PENGEMBALIAN ALAT";
} else {
    exit("Tipe tidak valid.");
}

if (!$data) {
    exit("Data tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $judul; ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
            font-size: 12px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .line { border-bottom: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; font-size: 11px; }
        td { padding: 2px 0; }
        @media print {
            body { width: 100%; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="text-center">
        <h3 style="margin:0;">SYSTEM PEMINJAMAN</h3>
        <p style="margin:2px 0;">Bukti Transaksi Resmi</p>
        <small><?= date('d-m-Y H:i:s'); ?></small>
    </div>

    <div class="line"></div>
    <div class="text-center"><strong><?= $judul; ?></strong></div>
    <div class="line"></div>

    <table>
        <?php if ($tipe == 'peminjaman'): ?>
            <tr><td>No. Transaksi</td><td>: #PJ-<?= str_pad($data['id_peminjaman'], 3, '0', STR_PAD_LEFT); ?></td></tr>
            <tr><td>Peminjam</td><td>: <?= htmlspecialchars($data['nama_lengkap']); ?></td></tr>
            <tr><td>Nama Alat</td><td>: <?= htmlspecialchars($data['nama_alat']); ?></td></tr>
            <tr><td>Jumlah</td><td>: <?= $data['jumlah']; ?> unit</td></tr>
            <tr><td>Tgl Pinjam</td><td>: <?= date('d/m/Y', strtotime($data['tgl_pinjam'])); ?></td></tr>
            <tr><td>Jatuh Tempo</td><td>: <?= date('d/m/Y', strtotime($data['tgl_jatuh_tempo'])); ?></td></tr>
            <tr><td>Status</td><td>: <?= strtoupper($data['status']); ?></td></tr>
        <?php else: ?>
            <tr><td>No. Pengembalian</td><td>: #KB-<?= str_pad($data['id_pengembalian'], 3, '0', STR_PAD_LEFT); ?></td></tr>
            <tr><td>Ref Pinjam</td><td>: #PJ-<?= str_pad($data['id_peminjaman'], 3, '0', STR_PAD_LEFT); ?></td></tr>
            <tr><td>Peminjam</td><td>: <?= htmlspecialchars($data['nama_lengkap']); ?></td></tr>
            <tr><td>Nama Alat</td><td>: <?= htmlspecialchars($data['nama_alat']); ?> (<?= $data['jumlah']; ?> unit)</td></tr>
            <tr><td>Tgl Kembali</td><td>: <?= date('d/m/Y', strtotime($data['tgl_kembali'])); ?></td></tr>
            <tr><td>Kondisi</td><td>: <?= $data['kondisi_alat']; ?></td></tr>
            <tr><td>Denda Terlambat</td><td>: Rp <?= number_format($data['denda_keterlambatan'], 0, ',', '.'); ?></td></tr>
            <tr><td>Denda Kerusakan</td><td>: Rp <?= number_format($data['denda_kerusakan'], 0, ',', '.'); ?></td></tr>
            <tr><td><strong>Total Denda</strong></td><td>: <strong>Rp <?= number_format($data['total_denda'], 0, ',', '.'); ?></strong></td></tr>
            <tr><td>Status Bayar</td><td>: <?= $data['status_pembayaran']; ?></td></tr>
        <?php endif; ?>
    </table>

    <div class="line"></div>
    <div class="text-center">
        <p style="margin:5px 0;">Terima kasih atas kerjasamanya!</p>
        <small>Simpan struk ini sebagai bukti resmi</small>
    </div>

</body>
</html>