<?php
session_start();
require_once '../config/koneksi.php';

$id_parkir = $_GET['id'] ?? 0;

$query = "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, a.nama_area, u.nama_lengkap AS petugas, tr.tarif_per_jam 
          FROM tb_transaksi t
          JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
          JOIN tb_area_parkir a ON t.id_area = a.id_area
          JOIN tb_user u ON t.id_user = u.id_user
          LEFT JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif
          WHERE t.id_parkir = '$id_parkir'";

$data = mysqli_fetch_assoc(mysqli_query($koneksi, $query));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Parkir - #<?= $data['id_parkir']; ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; width: 300px; padding: 10px; }
        .text-center { text-align: center; }
        .line { border-bottom: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; font-size: 13px; }
    </style>
</head>
<body onload="window.print()">
    <div class="text-center">
        <h3>E-PARKIR SYSTEM</h3>
        <p>Struk Pembayaran Parkir</p>
    </div>
    <div class="line"></div>
    <table>
        <tr><td>Plat Nomor</td><td>: <b><?= $data['plat_nomor']; ?></b></td></tr>
        <tr><td>Jenis</td><td>: <?= ucfirst($data['jenis_kendaraan']); ?></td></tr>
        <tr><td>Area</td><td>: <?= $data['nama_area']; ?></td></tr>
        <tr><td>Waktu Masuk</td><td>: <?= $data['waktu_masuk']; ?></td></tr>
        <tr><td>Waktu Keluar</td><td>: <?= $data['waktu_keluar']; ?></td></tr>
        <tr><td>Durasi</td><td>: <?= $data['durasi_jam']; ?> Jam</td></tr>
        <tr><td>Tarif / Jam</td><td>: Rp <?= number_format($data['tarif_per_jam']); ?></td></tr>
        <tr><td colspan="2"><div class="line"></div></td></tr>
        <tr><td><b>TOTAL BIAYA</b></td><td>: <b>Rp <?= number_format($data['biaya_total']); ?></b></td></tr>
    </table>
    <div class="line"></div>
    <div class="text-center">
        <p>Petugas: <?= $data['petugas']; ?></p>
        <p>-- Terima Kasih --</p>
    </div>
</body>
</html>