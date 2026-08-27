<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once '../../config/koneksi.php';

// Validasi Akses Admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$pesan_error = "";

// 1. PROSES TAMBAH KENDARAAN
if (isset($_POST['tambah'])) {
    $plat_nomor = strtoupper(trim(mysqli_real_escape_string($koneksi, $_POST['plat_nomor'])));
    $warna      = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $jenis      = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $id_user    = $_SESSION['id_user'];

    if (!preg_match('/^[A-Z]{1,2}\s?[0-9]{1,4}\s?[A-Z]{1,3}$/', $plat_nomor)) {
        $pesan_error = "Format Plat Nomor tidak valid! Contoh: B 1234 ABC, AB 123 CD, atau D 1 A.";
    } else {
        $query = "INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, id_user) 
                  VALUES ('$plat_nomor', '$jenis', '$warna', '$id_user')";
        if (mysqli_query($koneksi, $query)) {
            header("Location: kendaraan.php?pesan=sukses_tambah");
            exit();
        } else {
            $pesan_error = "Gagal menyimpan data: " . mysqli_error($koneksi);
        }
    }
}

// 2. PROSES EDIT KENDARAAN
if (isset($_POST['edit'])) {
    $id_kendaraan = (int)$_POST['id_kendaraan'];
    $plat_nomor   = strtoupper(trim(mysqli_real_escape_string($koneksi, $_POST['plat_nomor'])));
    $warna        = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $jenis        = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);

    if (!preg_match('/^[A-Z]{1,2}\s?[0-9]{1,4}\s?[A-Z]{1,3}$/', $plat_nomor)) {
        $pesan_error = "Format Plat Nomor tidak valid! Contoh: B 1234 ABC.";
    } else {
        $query = "UPDATE tb_kendaraan SET plat_nomor='$plat_nomor', jenis_kendaraan='$jenis', warna='$warna' WHERE id_kendaraan='$id_kendaraan'";
        if (mysqli_query($koneksi, $query)) {
            header("Location: kendaraan.php?pesan=sukses_edit");
            exit();
        } else {
            $pesan_error = "Gagal memperbarui data: " . mysqli_error($koneksi);
        }
    }
}

// 3. PROSES HAPUS KENDARAAN (Aman setelah validasi session)
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM tb_kendaraan WHERE id_kendaraan='$id'");
    header("Location: kendaraan.php?pesan=sukses_hapus");
    exit();
}

// MODE EDIT DATA
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $res = mysqli_query($koneksi, "SELECT * FROM tb_kendaraan WHERE id_kendaraan='$edit_id'");
    $edit_data = mysqli_fetch_assoc($res);
}

// AMBIL DATA TERPISAH (TABEL MOBIL & MOTOR)
$mobil_list = mysqli_query($koneksi, "SELECT * FROM tb_kendaraan WHERE LOWER(jenis_kendaraan)='mobil' ORDER BY id_kendaraan DESC");
$motor_list = mysqli_query($koneksi, "SELECT * FROM tb_kendaraan WHERE LOWER(jenis_kendaraan)='motor' ORDER BY id_kendaraan DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Kendaraan - Admin</title>
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
            <li><a href="area_parkir.php">Area Parkir</a></li>
            <li><a href="kendaraan.php" class="active">Kendaraan</a></li>
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
                    <h2>Master Kendaraan 🚗🛵</h2>
                    <p>Registrasi identitas kendaraan (Plat Nomor & Warna/Ciri) terpisah per kategori.</p>
                </div>
            </div>
        </div>

        <?php if (!empty($pesan_error)): ?>
            <p style="color: #dc2626; font-weight: bold; margin-bottom: 15px; padding: 10px; background: #fee2e2; border-radius: 5px;">
                <?= $pesan_error; ?>
            </p>
        <?php endif; ?>

        <!-- Form Tambah / Edit Kendaraan -->
        <div class="form-box">
            <h3><?= $edit_data ? 'Edit Data Kendaraan' : 'Tambah Registrasi Kendaraan Baru'; ?></h3>
            <form action="" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id_kendaraan" value="<?= $edit_data['id_kendaraan']; ?>">
                <?php endif; ?>

                <input type="text" 
                       name="plat_nomor" 
                       id="plat_nomor"
                       placeholder="Nomor Plat (Contoh: B 1234 ABC)" 
                       value="<?= $edit_data['plat_nomor'] ?? ''; ?>" 
                       style="text-transform: uppercase;"
                       oninput="this.value = this.value.toUpperCase();"
                       pattern="^[A-Za-z]{1,2}\s?[0-9]{1,4}\s?[A-Za-z]{1,3}$"
                       title="Format: 1-2 Huruf Depan, 1-4 Angka Tengah, 1-3 Huruf Belakang. Contoh: B 1234 ABC"
                       required>

                <input type="text" name="warna" placeholder="Warna / Ciri Fisik (Misal: Hitam)" value="<?= $edit_data['warna'] ?? ''; ?>" required>
                
                <select name="jenis_kendaraan" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="Mobil" <?= (($edit_data['jenis_kendaraan'] ?? '') == 'Mobil' || ($edit_data['jenis_kendaraan'] ?? '') == 'mobil') ? 'selected' : ''; ?>>Mobil</option>
                    <option value="Motor" <?= (($edit_data['jenis_kendaraan'] ?? '') == 'Motor' || ($edit_data['jenis_kendaraan'] ?? '') == 'motor') ? 'selected' : ''; ?>>Motor</option>
                </select>

                <?php if ($edit_data): ?>
                    <button type="submit" name="edit" class="btn btn-edit">Update Kendaraan</button>
                    <a href="kendaraan.php" class="btn" style="background: #64748b; color: white;">Batal</a>
                <?php else: ?>
                    <button type="submit" name="tambah" class="btn btn-add">Simpan Kendaraan</button>
                <?php endif; ?>
            </form>
        </div>

        <!-- TABEL 1: DAFTAR MOBIL -->
        <h3 style="margin: 20px 0 10px 0; color: #1e293b;">Daftar Kendaraan: Mobil 🚗</h3>
        <div style="overflow-x: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 30px;">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nomor Plat</th>
                        <th>Warna / Ciri</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1; 
                    if (mysqli_num_rows($mobil_list) > 0):
                        while($row = mysqli_fetch_assoc($mobil_list)): 
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><b style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-family: monospace; font-size: 14px; letter-spacing: 1px;"><?= strtoupper($row['plat_nomor']); ?></b></td>
                        <td><?= htmlspecialchars($row['warna']); ?></td>
                        <td>
                            <a href="kendaraan.php?edit_id=<?= $row['id_kendaraan']; ?>" class="btn btn-edit">Edit</a>
                            <a href="kendaraan.php?hapus=<?= $row['id_kendaraan']; ?>" onclick="return confirm('Hapus data kendaraan ini?')" class="btn btn-delete">Hapus</a>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                    <tr><td colspan="4" style="text-align:center; color:#94a3b8;">Belum ada data mobil.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- TABEL 2: DAFTAR MOTOR -->
        <h3 style="margin: 20px 0 10px 0; color: #1e293b;">Daftar Kendaraan: Motor 🛵</h3>
        <div style="overflow-x: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nomor Plat</th>
                        <th>Warna / Ciri</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1; 
                    if (mysqli_num_rows($motor_list) > 0):
                        while($row = mysqli_fetch_assoc($motor_list)): 
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><b style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-family: monospace; font-size: 14px; letter-spacing: 1px;"><?= strtoupper($row['plat_nomor']); ?></b></td>
                        <td><?= htmlspecialchars($row['warna']); ?></td>
                        <td>
                            <a href="kendaraan.php?edit_id=<?= $row['id_kendaraan']; ?>" class="btn btn-edit">Edit</a>
                            <a href="kendaraan.php?hapus=<?= $row['id_kendaraan']; ?>" onclick="return confirm('Hapus data kendaraan ini?')" class="btn btn-delete">Hapus</a>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                    <tr><td colspan="4" style="text-align:center; color:#94a3b8;">Belum ada data motor.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pop-up Mengambang Toast -->
    <div id="toastAlert" class="toast-popup">
        <span class="toast-close" onclick="closeToast()">&times;</span>
        <svg class="checkmark-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
            <circle class="checkmark-circle" cx="26" cy="26" r="23" fill="none"/>
            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
        </svg>
        <p id="toastMessage"></p>
    </div>

    <script>
        function showToast(message) {
            const toast = document.getElementById('toastAlert');
            if (!toast) return;
            
            document.getElementById('toastMessage').innerText = message;
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

        <?php if (isset($_GET['pesan'])): ?>
            <?php 
                $p = $_GET['pesan'];
                $pesan_teks = "";
                if ($p == 'sukses_tambah') $pesan_teks = "Data berhasil ditambahkan!";
                elseif ($p == 'sukses_edit' || $p == 'sukses') $pesan_teks = "Data berhasil diperbarui!";
                elseif ($p == 'sukses_hapus') $pesan_teks = "Data berhasil dihapus!";
            ?>
            <?php if (!empty($pesan_teks)): ?>
                document.addEventListener("DOMContentLoaded", function() {
                    showToast("<?= $pesan_teks; ?>");
                });
            <?php endif; ?>
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
    </script>
</body>
</html>