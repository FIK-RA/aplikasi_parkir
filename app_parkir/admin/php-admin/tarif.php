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

// EDIT ATUR TARIF PARKIR
if (isset($_POST['simpan_tarif'])) {
    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $tarif = (int)$_POST['tarif_per_jam'];

    // Cek apakah jenis kendaraan sudah ada
    $check = mysqli_query($koneksi, "SELECT * FROM tb_tarif WHERE jenis_kendaraan='$jenis'");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($koneksi, "UPDATE tb_tarif SET tarif_per_jam='$tarif' WHERE jenis_kendaraan='$jenis'");
    } else {
        mysqli_query($koneksi, "INSERT INTO tb_tarif (jenis_kendaraan, tarif_per_jam) VALUES ('$jenis', '$tarif')");
    }
    header("Location: tarif.php?pesan=sukses");
    exit();
}

$tarif_list = mysqli_query($koneksi, "SELECT * FROM tb_tarif ORDER BY id_tarif ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarif Parkir - Admin</title>
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
            <li><a href="tarif.php" class="active">Tarif Parkir</a></li>
            <li><a href="area_parkir.php">Area Parkir</a></li>
            <li><a href="kendaraan.php">Kendaraan</a></li>
            <li><a href="log.php">Log Aktivitas</a></li>
        </ul>
        <a href="../../logout.php" class="btn-logout">Logout</a>
    </div>

    <div class="content">
        <div class="header">
            <div class="header-left">
                <button class="menu-toggle" id="btnToggle" title="Buka Menu">☰</button>
                <div>
                    <h2>Atur Tarif Parkir 💵</h2>
                    <p>Atur besaran biaya parkir per jam (Motor: Rp 2.000/jam, Mobil: Rp 5.000/jam).</p>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['pesan'])): ?>
            <p style="color: #16a34a; font-weight: bold; margin-bottom: 15px; padding: 10px; background: #dcfce7; border-radius: 5px;">
                Tarif berhasil diperbarui!
            </p>
        <?php endif; ?>

        <!-- Form Setting Tarif -->
        <div class="form-box">
            <h3>Update Tarif Per Jam</h3>
            <form action="" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                <select name="jenis_kendaraan" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="Motor">Motor</option>
                    <option value="Mobil">Mobil</option>
                </select>
                <input type="number" name="tarif_per_jam" placeholder="Nominal Tarif (misal: 2000)" required>
                <button type="submit" name="simpan_tarif" class="btn btn-add">Simpan Tarif</button>
            </form>
        </div>

        <!-- Tabel Daftar Tarif -->
        <div style="overflow-x: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis Kendaraan</th>
                        <th>Tarif Per Jam</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while($row = mysqli_fetch_assoc($tarif_list)): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><b><?= htmlspecialchars($row['jenis_kendaraan']); ?></b></td>
                        <td><b style="color: #16a34a;">Rp <?= number_format($row['tarif_per_jam'], 0, ',', '.'); ?> / jam</b></td>
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