<?php
session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../index.php");
    exit();
}

$id_parkir = (int)($_GET['id'] ?? 0);

// Update status cetak menjadi 'sudah'
mysqli_query($koneksi, "UPDATE tb_transaksi SET status_cetak='sudah' WHERE id_parkir='$id_parkir'");

$query = "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, k.warna, a.nama_area, u.nama_lengkap AS petugas, tr.tarif_per_jam 
          FROM tb_transaksi t
          JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
          JOIN tb_area_parkir a ON t.id_area = a.id_area
          JOIN tb_user u ON t.id_user = u.id_user
          LEFT JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif
          WHERE t.id_parkir = '$id_parkir'";

$res = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($res);

if (!$data) {
    echo "Data transaksi tidak ditemukan.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Parkir - #<?= $data['id_parkir']; ?></title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        body { 
            font-family: 'Courier New', Courier, monospace; 
            width: 280px; 
            padding: 5px; 
            margin: auto;
            color: #000;
        }
        .text-center { text-align: center; }
        .line { border-bottom: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; font-size: 12px; }
        td { vertical-align: top; }
        .barcode-container { margin-top: 10px; text-align: center; }
        svg#barcode { width: 100%; max-height: 50px; }
        @media print {
            body { width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="text-center">
        <h3 style="margin: 0;">E-PARKIR SYSTEM</h3>
        <p style="font-size: 11px; margin: 2px 0;">Tiket & Struk Resmi Parkir</p>
    </div>

    <div class="line"></div>

    <table>
        <tr><td>No. Tiket</td><td>: <b>#<?= $data['id_parkir']; ?></b></td></tr>
        <tr><td>Plat Nomor</td><td>: <b><?= $data['plat_nomor']; ?></b></td></tr>
        <tr><td>Jenis / Ciri</td><td>: <?= ucfirst($data['jenis_kendaraan']); ?> (<?= $data['warna']; ?>)</td></tr>
        <tr><td>Area Parkir</td><td>: <?= $data['nama_area']; ?></td></tr>
        <tr><td>Waktu Masuk</td><td>: <?= $data['waktu_masuk']; ?></td></tr>
        
        <?php if ($data['status'] == 'keluar'): ?>
            <tr><td>Waktu Keluar</td><td>: <?= $data['waktu_keluar']; ?></td></tr>
            <tr><td>Durasi Parkir</td><td>: <?= $data['durasi_jam']; ?> Jam</td></tr>
            <tr><td>Tarif / Jam</td><td>: Rp <?= number_format($data['tarif_per_jam'] ?? 0, 0, ',', '.'); ?></td></tr>
            <tr><td colspan="2"><div class="line"></div></td></tr>
            <tr><td><b>TOTAL BIAYA</b></td><td>: <b>Rp <?= number_format($data['biaya_total'] ?? 0, 0, ',', '.'); ?></b></td></tr>
        <?php endif; ?>
    </table>

    <div class="line"></div>

    <div class="text-center">
        <p style="font-size: 11px; margin: 2px 0;">Petugas: <?= $data['petugas']; ?></p>
        <p style="font-size: 10px; margin-top: 4px;">Simpan tiket ini secara aman.</p>
        
        <div class="barcode-container">
            <svg id="barcode"></svg>
        </div>
    </div>

    <script>
        JsBarcode("#barcode", "<?= $data['id_parkir']; ?>", {
            format: "CODE128",
            width: 2,
            height: 40,
            displayValue: true,
            fontSize: 12
        });

        // Trigger Cetak & Otomatis Tutup Tab
        window.onload = function() {
            window.print();
        };

        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>