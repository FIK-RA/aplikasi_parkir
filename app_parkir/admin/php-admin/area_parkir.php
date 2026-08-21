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

$pesan = "";

// 1. TAMBAH AREA PARKIR
if (isset($_POST['tambah'])) {
    $nama_area = mysqli_real_escape_string($koneksi, $_POST['nama_area']);
    $jenis     = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $kapasitas = (int)$_POST['kapasitas'];

    $query = "INSERT INTO tb_area_parkir (nama_area, jenis_kendaraan, kapasitas) 
              VALUES ('$nama_area', '$jenis', '$kapasitas')";
    if (mysqli_query($koneksi, $query)) {
        header("Location: area_parkir.php?pesan=sukses_tambah");
        exit();
    }
}

// 2. EDIT AREA PARKIR
if (isset($_POST['edit'])) {
    $id_area   = (int)$_POST['id_area'];
    $nama_area = mysqli_real_escape_string($koneksi, $_POST['nama_area']);
    $jenis     = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $kapasitas = (int)$_POST['kapasitas'];

    $query = "UPDATE tb_area_parkir SET nama_area='$nama_area', jenis_kendaraan='$jenis', kapasitas='$kapasitas' WHERE id_area='$id_area'";
    if (mysqli_query($koneksi, $query)) {
        header("Location: area_parkir.php?pesan=sukses_edit");
        exit();
    }
}

// 3. HAPUS AREA PARKIR
if (isset($_GET['hapus'])) {
    $id_area = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM tb_area_parkir WHERE id_area='$id_area'");
    header("Location: area_parkir.php");
    exit();
}

// AMBIL DATA AREA
$areas = mysqli_query($koneksi, "SELECT * FROM tb_area_parkir ORDER BY id_area ASC");

// MODE EDIT
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $res = mysqli_query($koneksi, "SELECT * FROM tb_area_parkir WHERE id_area='$edit_id'");
    $edit_data = mysqli_fetch_assoc($res);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Area Parkir - Admin</title>
    <link rel="stylesheet" href="../css-admin/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../css-admin/user.css?v=<?= time(); ?>">
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <!-- Sidebar Admin -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>E-PARKIR ADMIN</h2>
            <button class="btn-close-sidebar" id="btnCloseSidebar">&times;</button>
        </div>
        <ul class="menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="user.php">User</a></li>
            <li><a href="tarif.php">Tarif Parkir</a></li>
            <li><a href="area_parkir.php" class="active">Area Parkir</a></li>
            <li><a href="kendaraan.php">Kendaraan</a></li>
            <li><a href="log.php">Log Aktivitas</a></li>
        </ul>
        <a href="../../logout.php" class="btn-logout">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="header">
            <div class="header-left">
                <button class="menu-toggle" id="btnToggle" title="Buka Menu">☰</button>
                <div>
                    <h2>Kelola Area Parkir 🅿️</h2>
                    <p>Atur blok lokasi, jenis kendaraan, dan daya tampung kapasitas parkir.</p>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['pesan'])): ?>
            <p style="color: #16a34a; font-weight: bold; margin-bottom: 15px; padding: 10px; background: #dcfce7; border-radius: 5px;">
                Berhasil memperbarui data area parkir!
            </p>
        <?php endif; ?>

        <!-- Form Tambah / Edit Area -->
        <div class="form-box">
            <h3><?= $edit_data ? 'Edit Area Parkir' : 'Tambah Area Parkir Baru'; ?></h3>
            <form action="" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id_area" value="<?= $edit_data['id_area']; ?>">
                <?php endif; ?>
                
                <input type="text" name="nama_area" placeholder="Nama Area (misal: Blok A)" value="<?= $edit_data['nama_area'] ?? ''; ?>" required>
                
                <select name="jenis_kendaraan" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="Motor" <?= ($edit_data['jenis_kendaraan'] ?? '') == 'Motor' ? 'selected' : ''; ?>>Motor</option>
                    <option value="Mobil" <?= ($edit_data['jenis_kendaraan'] ?? '') == 'Mobil' ? 'selected' : ''; ?>>Mobil</option>
                </select>

                <input type="number" name="kapasitas" placeholder="Kapasitas (Slot)" value="<?= $edit_data['kapasitas'] ?? ''; ?>" min="1" required>

                <?php if ($edit_data): ?>
                    <button type="submit" name="edit" class="btn btn-edit">Update Area</button>
                    <a href="area_parkir.php" class="btn" style="background: #64748b; color: white;">Batal</a>
                <?php else: ?>
                    <button type="submit" name="tambah" class="btn btn-add">Simpan Area</button>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabel Data Area -->
        <div style="overflow-x: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Area</th>
                        <th>Jenis Kendaraan</th>
                        <th>Kapasitas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while($row = mysqli_fetch_assoc($areas)): 
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><b><?= htmlspecialchars($row['nama_area']); ?></b></td>
                        <td>
                            <span style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                <?= htmlspecialchars($row['jenis_kendaraan']); ?>
                            </span>
                        </td>
                        <td><b><?= $row['kapasitas']; ?> Slot</b></td>
                        <td>
                            <a href="area_parkir.php?edit_id=<?= $row['id_area']; ?>" class="btn btn-edit">Edit</a>
                            <a href="area_parkir.php?hapus=<?= $row['id_area']; ?>" onclick="return confirm('Yakin hapus area ini?')" class="btn btn-delete">Hapus</a>
                        </td>
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