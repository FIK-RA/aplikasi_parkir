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

$nama_user = $_SESSION['nama_lengkap'];
$pesan = "";

// 1. PROSES TAMBAH USER
if (isset($_POST['tambah'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password']; 
    $role     = $_POST['role'];
    $status   = $_POST['status_aktif'];

    $query = "INSERT INTO tb_user (nama_lengkap, username, password, role, status_aktif) 
              VALUES ('$nama', '$username', '$password', '$role', '$status')";
    if (mysqli_query($koneksi, $query)) {
        header("Location: user.php?pesan=sukses_tambah");
        exit();
    }
}

// 2. PROSES HAPUS USER
if (isset($_GET['hapus'])) {
    $id_user = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM tb_user WHERE id_user='$id_user'");
    header("Location: user.php?pesan=sukses_hapus");
    exit();
}

// READ DATA USER
$users = mysqli_query($koneksi, "SELECT * FROM tb_user ORDER BY id_user ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Sistem Parkir</title>
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
            <li><a href="user.php" class="active">User</a></li>
            <li><a href="tarif.php">Tarif Parkir</a></li>
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
                    <h2>Manajemen Data User 👥</h2>
                    <p>Tambah, Edit, dan Hapus akun pengguna sistem.</p>
                </div>
            </div>
        </div>

        <!-- Form Tambah User -->
        <div class="form-box">
            <h3 style="margin-bottom: 15px;">Tambah User Baru</h3>
            <form action="" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required>
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role">
                    <option value="admin">Admin</option>
                    <option value="petugas">Petugas</option>
                    <option value="owner">Owner</option>
                </select>
                <select name="status_aktif">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
                <button type="submit" name="tambah" class="btn btn-add">Simpan User</button>
            </form>
        </div>

        <!-- Tabel Data User -->
        <div style="overflow-x: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1; 
                    while($row = mysqli_fetch_assoc($users)): 
                        $id_terformat = sprintf("%04d", $row['id_user']);
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= $id_terformat; ?></td>
                        <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                        <td><?= htmlspecialchars($row['username']); ?></td>
                        <td><span style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;"><?= strtoupper($row['role']); ?></span></td>
                        <td>
                            <?php if($row['status_aktif'] == 1): ?>
                                <span style="color: #16a34a; font-weight: bold;">Aktif</span>
                            <?php else: ?>
                                <span style="color: #dc2626; font-weight: bold;">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="user_edit.php?id=<?= $row['id_user']; ?>" class="btn btn-edit">Edit</a>
                            <a href="user.php?hapus=<?= $row['id_user']; ?>" onclick="return confirm('Yakin hapus user ini?')" class="btn btn-delete">Hapus</a>
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
                if ($p == 'sukses_tambah') $pesan_teks = "User berhasil ditambahkan!";
                elseif ($p == 'sukses_edit' || $p == 'sukses') $pesan_teks = "Data user berhasil diperbarui!";
                elseif ($p == 'sukses_hapus') $pesan_teks = "User berhasil dihapus!";
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