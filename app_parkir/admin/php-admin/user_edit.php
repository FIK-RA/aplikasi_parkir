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

// Ambil ID User dari URL
if (!isset($_GET['id'])) {
    header("Location: user.php");
    exit();
}

$id_user = (int)$_GET['id'];

// Fetch data user yang akan diedit
$query_user = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE id_user = '$id_user'");
$data = mysqli_fetch_assoc($query_user);

if (!$data) {
    header("Location: user.php");
    exit();
}

// PROSES EDIT USER
if (isset($_POST['update'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $role     = $_POST['role'];
    $status   = $_POST['status_aktif'];
    $password = $_POST['password'];

    // Jika password diisi, update password juga
    if (!empty($password)) {
        $query_update = "UPDATE tb_user SET 
                            nama_lengkap = '$nama', 
                            username = '$username', 
                            password = '$password', 
                            role = '$role', 
                            status_aktif = '$status' 
                         WHERE id_user = '$id_user'";
    } else {
        // Jika password kosong, jangan ubah password lama
        $query_update = "UPDATE tb_user SET 
                            nama_lengkap = '$nama', 
                            username = '$username', 
                            role = '$role', 
                            status_aktif = '$status' 
                         WHERE id_user = '$id_user'";
    }

    if (mysqli_query($koneksi, $query_update)) {
        header("Location: user.php?pesan=sukses_edit");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Sistem Parkir</title>
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
                    <h2>Edit Data User ✏️</h2>
                    <p>Ubah rincian akun pengguna ID: <?= sprintf("%04d", $data['id_user']); ?></p>
                </div>
            </div>
        </div>

        <!-- Form Edit User -->
        <div class="form-box">
            <form action="" method="POST" style="display: flex; flex-direction: column; gap: 15px; max-width: 500px;">
                <div>
                    <label style="display:block; margin-bottom:5px;">Nama Lengkap:</label>
                    <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($data['nama_lengkap']); ?>" required style="width: 100%;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:5px;">Username:</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($data['username']); ?>" required style="width: 100%;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:5px;">Password (Kosongkan jika tidak ingin mengubah):</label>
                    <input type="password" name="password" placeholder="Password Baru (opsional)" style="width: 100%;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:5px;">Role:</label>
                    <select name="role" style="width: 100%;">
                        <option value="admin" <?= $data['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="petugas" <?= $data['role'] == 'petugas' ? 'selected' : ''; ?>>Petugas</option>
                        <option value="owner" <?= $data['role'] == 'owner' ? 'selected' : ''; ?>>Owner</option>
                    </select>
                </div>

                <div>
                    <label style="display:block; margin-bottom:5px;">Status Aktif:</label>
                    <select name="status_aktif" style="width: 100%;">
                        <option value="1" <?= $data['status_aktif'] == 1 ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?= $data['status_aktif'] == 0 ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="submit" name="update" class="btn btn-add">Simpan Perubahan</button>
                    <a href="user.php" class="btn" style="background: #64748b; color: white; text-decoration: none; padding: 10px 15px; border-radius: 4px; display: inline-block;">Batal</a>
                </div>
            </form>
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