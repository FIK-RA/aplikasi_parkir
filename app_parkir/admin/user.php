<?php
session_start();
require_once '../config/koneksi.php';

// Proteksi Halaman Admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$pesan = "";

// 1. PROSES TAMBAH USER
if (isset($_POST['tambah'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password']; // Disimpan langsung atau md5/password_hash
    $role     = $_POST['role'];
    $status   = $_POST['status_aktif'];

    $query = "INSERT INTO tb_user (nama_lengkap, username, password, role, status_aktif) 
              VALUES ('$nama', '$username', '$password', '$role', '$status')";
    if (mysqli_query($koneksi, $query)) {
        $pesan = "User berhasil ditambahkan!";
    }
}

// 2. PROSES EDIT USER
if (isset($_POST['edit'])) {
    $id_user  = $_POST['id_user'];
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $role     = $_POST['role'];
    $status   = $_POST['status_aktif'];

    if (!empty($_POST['password'])) {
        $pass = $_POST['password'];
        $query = "UPDATE tb_user SET nama_lengkap='$nama', username='$username', password='$pass', role='$role', status_aktif='$status' WHERE id_user='$id_user'";
    } else {
        $query = "UPDATE tb_user SET nama_lengkap='$nama', username='$username', role='$role', status_aktif='$status' WHERE id_user='$id_user'";
    }
    mysqli_query($koneksi, $query);
    $pesan = "Data user berhasil diperbarui!";
}

// 3. PROSES HAPUS USER
if (isset($_GET['hapus'])) {
    $id_user = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM tb_user WHERE id_user='$id_user'");
    header("Location: user.php");
    exit();
}

// READ DATA USER
$users = mysqli_query($koneksi, "SELECT * FROM tb_user ORDER BY id_user DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>CRUD User - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f6f9; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #2563eb; color: white; }
        .form-box { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .btn { padding: 8px 12px; border: none; cursor: pointer; text-decoration: none; display: inline-block; border-radius: 4px; }
        .btn-add { background: #16a34a; color: white; }
        .btn-edit { background: #eab308; color: white; }
        .btn-delete { background: #dc2626; color: white; }
    </style>
</head>
<body>
    <a href="dashboard.php">&larr; Kembali ke Dashboard</a>
    <h2>Kelola User (CRUD User)</h2>

    <?php if($pesan): ?><p style="color: green;"><?= $pesan; ?></p><?php endif; ?>

    <!-- Form Tambah User -->
    <div class="form-box">
        <h3>Tambah User Baru</h3>
        <form action="" method="POST">
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
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Lengkap</th>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($users)): ?>
            <tr>
                <td><?= $row['id_user']; ?></td>
                <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                <td><?= htmlspecialchars($row['username']); ?></td>
                <td><b><?= strtoupper($row['role']); ?></b></td>
                <td><?= $row['status_aktif'] == 1 ? 'Aktif' : 'Nonaktif'; ?></td>
                <td>
                    <a href="user_edit.php?id=<?= $row['id_user']; ?>" class="btn btn-edit">Edit</a>
                    <a href="user.php?hapus=<?= $row['id_user']; ?>" onclick="return confirm('Yakin hapus user ini?')" class="btn btn-delete">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>