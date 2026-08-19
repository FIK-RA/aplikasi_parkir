<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'petugas') {
    header("Location: ../index.php");
    exit();
}

$id_user_login = $_SESSION['id_user'];
$pesan = "";

// 1. TRANSAKSI PARKIR MASUK
if (isset($_POST['parkir_masuk'])) {
    $plat_nomor = strtoupper(mysqli_real_escape_string($koneksi, $_POST['plat_nomor']));
    $jenis      = $_POST['jenis_kendaraan'];
    $warna      = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $id_area    = $_POST['id_area'];

    // Cek Kapasitas Area
    $q_area = mysqli_query($koneksi, "SELECT * FROM tb_area_parkir WHERE id_area='$id_area'");
    $d_area = mysqli_fetch_assoc($q_area);

    if ($d_area['terisi'] < $d_area['kapasitas']) {
        // Simpan / Ambil Kendaraan
        mysqli_query($koneksi, "INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, id_user) VALUES ('$plat_nomor', '$jenis', '$warna', '$id_user_login')");
        $id_kendaraan = mysqli_insert_id($koneksi);

        // Ambil ID Tarif sesuai Jenis Kendaraan
        $q_tarif = mysqli_query($koneksi, "SELECT id_tarif FROM tb_tarif WHERE jenis_kendaraan='$jenis' LIMIT 1");
        $d_tarif = mysqli_fetch_assoc($q_tarif);
        $id_tarif = $d_tarif['id_tarif'] ?? NULL;

        // Simpan Transaksi Parkir
        mysqli_query($koneksi, "INSERT INTO tb_transaksi (id_kendaraan, id_tarif, status, id_user, id_area) VALUES ('$id_kendaraan', '$id_tarif', 'masuk', '$id_user_login', '$id_area')");

        // Update Terisi (+1) di Area
        mysqli_query($koneksi, "UPDATE tb_area_parkir SET terisi = terisi + 1 WHERE id_area='$id_area'");
        $pesan = "Kendaraan berhasil masuk!";
    } else {
        $pesan = "Error: Area Parkir Penuh!";
    }
}

// 2. TRANSAKSI PARKIR KELUAR
if (isset($_POST['parkir_keluar'])) {
    $id_parkir = $_POST['id_parkir'];

    // Ambil detail transaksi
    $q_trx = mysqli_query($koneksi, "SELECT t.*, tr.tarif_per_jam FROM tb_transaksi t 
                                     JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif 
                                     WHERE t.id_parkir='$id_parkir' AND t.status='masuk'");
    if (mysqli_num_rows($q_trx) > 0) {
        $d_trx = mysqli_fetch_assoc($q_trx);
        
        $waktu_masuk = new DateTime($d_trx['waktu_masuk']);
        $waktu_keluar = new DateTime(); // Jam Sekarang
        $diff = $waktu_masuk->diff($waktu_keluar);
        
        // Hitung durasi minimal 1 jam
        $durasi_jam = $diff->h + ($diff->days * 24);
        if ($diff->i > 0 || $durasi_jam == 0) { $durasi_jam += 1; }

        $biaya_total = $durasi_jam * $d_trx['tarif_per_jam'];
        $wkt_keluar_str = $waktu_keluar->format('Y-m-d H:i:s');

        // Update Transaksi
        mysqli_query($koneksi, "UPDATE tb_transaksi SET waktu_keluar='$wkt_keluar_str', durasi_jam='$durasi_jam', biaya_total='$biaya_total', status='keluar' WHERE id_parkir='$id_parkir'");

        // Update Terisi (-1) di Area
        mysqli_query($koneksi, "UPDATE tb_area_parkir SET terisi = terisi - 1 WHERE id_area='".$d_trx['id_area']."'");

        // Redirect ke Cetak Struk
        header("Location: cetak_struk.php?id=$id_parkir");
        exit();
    }
}

// Data Area & Transaksi Aktif
$areas = mysqli_query($koneksi, "SELECT * FROM tb_area_parkir");
$transaksi_aktif = mysqli_query($koneksi, "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, a.nama_area FROM tb_transaksi t JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan JOIN tb_area_parkir a ON t.id_area = a.id_area WHERE t.status='masuk'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi Parkir - Petugas</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f1f5f9; }
        .container { display: flex; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; flex: 1; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #2563eb; color: white; }
    </style>
</head>
<body>
    <h2>Menu Transaksi Parkir</h2>
    <?php if($pesan): ?><p style="color: blue;"><?= $pesan; ?></p><?php endif; ?>

    <div class="container">
        <!-- Input Parkir Masuk -->
        <div class="card">
            <h3>Input Kendaraan Masuk</h3>
            <form action="" method="POST">
                <p><input type="text" name="plat_nomor" placeholder="Plat Nomor (Ex: B 1234 CD)" required></p>
                <p>
                    <select name="jenis_kendaraan" required>
                        <option value="motor">Motor</option>
                        <option value="mobil">Mobil</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </p>
                <p><input type="text" name="warna" placeholder="Warna Kendaraan"></p>
                <p>
                    <select name="id_area" required>
                        <?php while($ar = mysqli_fetch_assoc($areas)): ?>
                            <option value="<?= $ar['id_area']; ?>"><?= $ar['nama_area']; ?> (Sisa: <?= $ar['kapasitas'] - $ar['terisi']; ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </p>
                <button type="submit" name="parkir_masuk" style="background: green; color: white; padding: 10px;">Simpan Parkir Masuk</button>
            </form>
        </div>

        <!-- Kendaraan Aktif & Proses Keluar -->
        <div class="card">
            <h3>Kendaraan Sedang Parkir</h3>
            <table>
                <thead>
                    <tr>
                        <th>Plat Nomor</th>
                        <th>Jenis</th>
                        <th>Waktu Masuk</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($tr = mysqli_fetch_assoc($transaksi_aktif)): ?>
                    <tr>
                        <td><b><?= $tr['plat_nomor']; ?></b></td>
                        <td><?= $tr['jenis_kendaraan']; ?></td>
                        <td><?= $tr['waktu_masuk']; ?></td>
                        <td>
                            <form action="" method="POST">
                                <input type="hidden" name="id_parkir" value="<?= $tr['id_parkir']; ?>">
                                <button type="submit" name="parkir_keluar" style="background: red; color: white; padding: 5px;">Proses Keluar & Struk</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>