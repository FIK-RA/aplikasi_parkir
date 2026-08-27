<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once '../../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'petugas') {
    header("Location: ../../index.php");
    exit();
}

$id_user_login = $_SESSION['id_user'];
$pesan = "";
$tipe_pesan = "";
$cetak_id_baru = null;

// 1. INPUT KENDARAAN MASUK
if (isset($_POST['parkir_masuk'])) {
    $plat_nomor = strtoupper(trim(mysqli_real_escape_string($koneksi, $_POST['plat_nomor'])));
    $jenis      = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $warna      = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $id_area    = (int)$_POST['id_area'];

    $q_area = mysqli_query($koneksi, "SELECT * FROM tb_area_parkir WHERE id_area='$id_area'");
    $d_area = mysqli_fetch_assoc($q_area);

    if ($d_area && $d_area['terisi'] < $d_area['kapasitas']) {
        $q_cek_knd = mysqli_query($koneksi, "SELECT id_kendaraan FROM tb_kendaraan WHERE plat_nomor='$plat_nomor'");
        if (mysqli_num_rows($q_cek_knd) > 0) {
            $id_kendaraan = mysqli_fetch_assoc($q_cek_knd)['id_kendaraan'];
        } else {
            mysqli_query($koneksi, "INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, id_user) VALUES ('$plat_nomor', '$jenis', '$warna', '$id_user_login')");
            $id_kendaraan = mysqli_insert_id($koneksi);
        }

        // Ambil ID Tarif Sesuai Jenis Kendaraan Dinamis Dari tb_tarif
        $q_tarif = mysqli_query($koneksi, "SELECT id_tarif FROM tb_tarif WHERE LOWER(jenis_kendaraan)='".strtolower($jenis)."' LIMIT 1");
        $d_tarif = mysqli_fetch_assoc($q_tarif);
        $id_tarif = $d_tarif['id_tarif'] ?? NULL;

        $query_ins = "INSERT INTO tb_transaksi (id_kendaraan, id_tarif, status, status_cetak, id_user, id_area) 
                      VALUES ('$id_kendaraan', '$id_tarif', 'masuk', 'belum', '$id_user_login', '$id_area')";
        if (mysqli_query($koneksi, $query_ins)) {
            $id_parkir_baru = mysqli_insert_id($koneksi);
            mysqli_query($koneksi, "UPDATE tb_area_parkir SET terisi = terisi + 1 WHERE id_area='$id_area'");
            $pesan = "Kendaraan berhasil masuk! ID Transaksi: #$id_parkir_baru";
            $tipe_pesan = "sukses";
            $cetak_id_baru = $id_parkir_baru;
        }
    } else {
        $pesan = "Gagal: Area parkir penuh!";
        $tipe_pesan = "error";
    }
}

// 2. PROSES SCAN BARCODE / KELUAR PARKIR (DINAMIS DENGAN TB_TARIF)
if (isset($_POST['scan_barcode']) || isset($_POST['parkir_keluar'])) {
    $id_parkir = (int)($_POST['scan_barcode'] ?? $_POST['id_parkir']);

    // JOIN ke tb_tarif & tb_kendaraan untuk kalkulasi dinamis
    $q_trx = mysqli_query($koneksi, "SELECT t.*, tr.tarif_per_jam, k.jenis_kendaraan 
                                     FROM tb_transaksi t 
                                     JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
                                     LEFT JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif 
                                     WHERE t.id_parkir='$id_parkir' AND t.status='masuk'");
    
    if (mysqli_num_rows($q_trx) > 0) {
        $d_trx = mysqli_fetch_assoc($q_trx);
        
        $waktu_masuk  = new DateTime($d_trx['waktu_masuk']);
        $waktu_keluar = new DateTime();
        $diff         = $waktu_masuk->diff($waktu_keluar);
        
        $durasi_jam = $diff->h + ($diff->days * 24);
        if ($diff->i > 0 || $durasi_jam == 0) { $durasi_jam += 1; } // Minimal 1 jam / pembulatan ke atas

        // Ambil tarif dinamis per jam (jika id_tarif null, fallback cari berdasarkan jenis kendaraan)
        $tarif_per_jam = $d_trx['tarif_per_jam'];
        if (!$tarif_per_jam) {
            $jenis_knd = strtolower($d_trx['jenis_kendaraan']);
            $q_fallback = mysqli_query($koneksi, "SELECT tarif_per_jam FROM tb_tarif WHERE LOWER(jenis_kendaraan)='$jenis_knd' LIMIT 1");
            $d_fallback = mysqli_fetch_assoc($q_fallback);
            $tarif_per_jam = $d_fallback['tarif_per_jam'] ?? 2000;
        }

        $biaya_total   = $durasi_jam * $tarif_per_jam;
        $wkt_keluar_str= $waktu_keluar->format('Y-m-d H:i:s');

        mysqli_query($koneksi, "UPDATE tb_transaksi SET waktu_keluar='$wkt_keluar_str', durasi_jam='$durasi_jam', biaya_total='$biaya_total', status='keluar' WHERE id_parkir='$id_parkir'");
        mysqli_query($koneksi, "UPDATE tb_area_parkir SET terisi = GREATEST(0, terisi - 1) WHERE id_area='".$d_trx['id_area']."'");

        $pesan = "Parkir keluar berhasil! Biaya: Rp " . number_format($biaya_total, 0, ',', '.');
        $tipe_pesan = "sukses";
        $cetak_id_baru = $id_parkir;
    } else {
        $pesan = "Barcode / ID Transaksi #$id_parkir tidak ditemukan atau sudah keluar!";
        $tipe_pesan = "error";
    }
}

// 3. PROSES HAPUS TRANSAKSI
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    
    $q_cek = mysqli_query($koneksi, "SELECT id_parkir, id_area, status, status_cetak FROM tb_transaksi WHERE id_parkir='$id_hapus'");
    if (mysqli_num_rows($q_cek) > 0) {
        $d_hapus = mysqli_fetch_assoc($q_cek);
        
        if (($d_hapus['status_cetak'] ?? 'belum') === 'sudah') {
            if ($d_hapus['status'] === 'masuk') {
                mysqli_query($koneksi, "UPDATE tb_area_parkir SET terisi = GREATEST(0, terisi - 1) WHERE id_area='".$d_hapus['id_area']."'");
            }
            
            mysqli_query($koneksi, "DELETE FROM tb_transaksi WHERE id_parkir='$id_hapus'");
            $pesan = "Transaksi #$id_hapus berhasil dihapus!";
            $tipe_pesan = "sukses";
        } else {
            $pesan = "Gagal: Transaksi #$id_hapus tidak dapat dihapus karena struk belum dicetak!";
            $tipe_pesan = "error";
        }
    }
}

$areas = mysqli_query($koneksi, "SELECT * FROM tb_area_parkir");
$semua_transaksi = mysqli_query($koneksi, "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, a.nama_area 
                                           FROM tb_transaksi t 
                                           JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan 
                                           JOIN tb_area_parkir a ON t.id_area = a.id_area 
                                           ORDER BY t.id_parkir DESC LIMIT 50");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Parkir - Petugas</title>
    <link rel="stylesheet" href="../css-petugas/dashboard.css?v=<?= time(); ?>">
    <style>
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .box { background: white; padding: 20px; border-radius: 10px; border: 1px solid #d1fae5; }
        .box h3 { color: #065f46; margin-bottom: 15px; border-bottom: 2px solid #a7f3d0; padding-bottom: 8px; }
        .input-group { margin-bottom: 12px; }
        .input-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px; color: #334155; }
        .input-group input, .input-group select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; }
        .btn-green { background: #10b981; color: white; border: none; padding: 10px 15px; border-radius: 6px; font-weight: bold; cursor: pointer; width: 100%; }
        .btn-green:hover { background: #059669; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: white; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
        th { background: #065f46; color: white; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .badge-masuk { background: #fef08a; color: #854d0e; }
        .badge-keluar { background: #bbf7d0; color: #166534; }
        .badge-belum { background: #fee2e2; color: #991b1b; }
        .badge-sudah { background: #dcfce7; color: #166534; }
        
        .btn-action { display: inline-block; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; border: none; }
        .btn-print { background: #0284c7; color: white; }
        .btn-delete { background: #ef4444; color: white; margin-left: 4px; cursor: pointer; }
        .btn-disabled { background: #94a3b8; color: white; margin-left: 4px; cursor: not-allowed; opacity: 0.6; }
    </style>
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>E-PARKIR PETUGAS</h2>
            <button class="btn-close-sidebar" id="btnCloseSidebar">&times;</button>
        </div>
        <ul class="menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="transaksi.php" class="active">Transaksi Parkir</a></li>
        </ul>
        <a href="../../logout.php" class="btn-logout">Logout</a>
    </div>

    <div class="content">
        <div class="header">
            <div class="header-left">
                <button class="menu-toggle" id="btnToggle" title="Buka Menu">☰</button>
                <div>
                    <h2>Kelola Transaksi Parkir 🎫</h2>
                    <p>Input kedatangan kendaraan dan scan barcode selesai parkir.</p>
                </div>
            </div>
        </div>

        <div class="form-grid">
            <div class="box">
                <h3>1. Input Parkir Masuk 🚗</h3>
                <form action="" method="POST">
                    <div class="input-group">
                        <label>Nomor Plat Kendaraan</label>
                        <input type="text" name="plat_nomor" placeholder="Contoh: B 1234 ABC" style="text-transform: uppercase;" required>
                    </div>
                    <div class="input-group">
                        <label>Jenis Kendaraan</label>
                        <select name="jenis_kendaraan" required>
                            <option value="Motor">Motor</option>
                            <option value="Mobil">Mobil</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Warna / Ciri Fisik</label>
                        <input type="text" name="warna" placeholder="Misal: Hitam Glossy" required>
                    </div>
                    <div class="input-group">
                        <label>Area Parkir</label>
                        <select name="id_area" required>
                            <?php while($a = mysqli_fetch_assoc($areas)): ?>
                                <option value="<?= $a['id_area']; ?>">
                                    <?= $a['nama_area']; ?> (Sisa Slot: <?= $a['kapasitas'] - $a['terisi']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" name="parkir_masuk" class="btn-green">Simpan & Masuk Parkir</button>
                </form>
            </div>

            <div class="box" style="background: #ecfdf5;">
                <h3>2. Scan Barcode Parkir Keluar 🔍</h3>
                <p style="font-size: 12px; color: #047857; margin-bottom: 15px;">
                    Arahkan **Barcode Scanner** ke kolom input di bawah ini.
                </p>
                <form action="" method="POST">
                    <div class="input-group">
                        <label style="color: #065f46; font-size: 14px;">Scan Kode Barcode / ID Struk:</label>
                        <input type="text" name="scan_barcode" placeholder="Klik disini & Scan Struk..." autofocus style="font-size: 16px; font-weight: bold; border: 2px solid #10b981;" required>
                    </div>
                    <button type="submit" class="btn-green" style="background: #047857;">Proses Parkir Keluar</button>
                </form>
            </div>
        </div>

        <div class="box" style="overflow-x: auto;">
            <h3>Daftar Transaksi Parkir</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Plat Nomor</th>
                        <th>Jenis & Area</th>
                        <th>Waktu Masuk</th>
                        <th>Waktu Keluar</th>
                        <th>Status Parkir</th>
                        <th>Status Struk</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($semua_transaksi)): ?>
                    <?php $is_printed = ($row['status_cetak'] ?? 'belum') === 'sudah'; ?>
                    <tr>
                        <td><b>#<?= $row['id_parkir']; ?></b></td>
                        <td><b style="font-family: monospace; font-size: 14px;"><?= $row['plat_nomor']; ?></b></td>
                        <td><?= $row['jenis_kendaraan']; ?> (<?= $row['nama_area']; ?>)</td>
                        <td><?= $row['waktu_masuk']; ?></td>
                        <td><?= $row['waktu_keluar'] ?? '-'; ?></td>
                        <td>
                            <span class="badge <?= $row['status'] == 'masuk' ? 'badge-masuk' : 'badge-keluar'; ?>">
                                <?= $row['status']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $is_printed ? 'badge-sudah' : 'badge-belum'; ?>">
                                <?= $is_printed ? 'Sudah Dicetak' : 'Belum Dicetak'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="cetak_struk.php?id=<?= $row['id_parkir']; ?>" target="_blank" class="btn-action btn-print">Cetak Struk 🖨️</a>
                            
                            <?php if ($is_printed): ?>
                                <a href="transaksi.php?hapus=<?= $row['id_parkir']; ?>" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi #<?= $row['id_parkir']; ?>?');" 
                                   class="btn-action btn-delete">Hapus 🗑️</a>
                            <?php else: ?>
                                <button disabled title="Cetak struk terlebih dahulu untuk menghapus transaksi ini" class="btn-action btn-disabled">Hapus 🗑️</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- HTML Pop-up Mengambang Toast -->
    <div id="toastAlert" class="toast-popup">
        <span class="toast-close" onclick="closeToast()">&times;</span>
        <svg class="checkmark-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
            <circle class="checkmark-circle" cx="26" cy="26" r="23" fill="none"/>
            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
        </svg>
        <p id="toastMessage"></p>
    </div>

    <script>
        function showToast(message, isError = false) {
            const toast = document.getElementById('toastAlert');
            if (!toast) return;
            
            document.getElementById('toastMessage').innerText = message;
            
            if (isError) {
                toast.classList.add('toast-error');
            } else {
                toast.classList.remove('toast-error');
            }

            toast.style.display = 'flex';

            const timer = setTimeout(() => {
                closeToast();
            }, 3000);

            toast.dataset.timer = timer;
        }

        function closeToast() {
            const toast = document.getElementById('toastAlert');
            if (toast) {
                if (toast.dataset.timer) clearTimeout(toast.dataset.timer);
                toast.style.display = 'none';
            }
        }

        <?php if (!empty($pesan)): ?>
            document.addEventListener("DOMContentLoaded", function() {
                showToast("<?= htmlspecialchars($pesan); ?>", <?= $tipe_pesan === 'error' ? 'true' : 'false'; ?>);
            });
        <?php endif; ?>

        const btnToggle = document.getElementById('btnToggle');
        const btnCloseSidebar = document.getElementById('btnCloseSidebar');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function openSidebar() { sidebar.classList.add('show'); overlay.classList.add('show'); }
        function closeSidebar() { sidebar.classList.remove('show'); overlay.classList.remove('show'); }

        btnToggle.addEventListener('click', openSidebar);
        btnCloseSidebar.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);

        <?php if (!empty($cetak_id_baru)): ?>
            window.open('cetak_struk.php?id=<?= $cetak_id_baru; ?>', '_blank');
        <?php endif; ?>
    </script>
</body>
</html>