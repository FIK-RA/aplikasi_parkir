<?php
session_set_cookie_params(0);
session_start();
// Cek khusus session admin
if (!isset($_SESSION['id_admin'])) {
    header("Location: ../login-admin.php?pesan=Akses Ditolak! Silakan login dulu.");
    exit;
}
require_once '../config/koneksi.php';

// --- PROSES TAMBAH KATEGORI ---
if (isset($_POST['tambah_kategori'])) {
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    $query = "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kategori')";
    mysqli_query($koneksi, $query) ? 
        print("<script>alert('Kategori berhasil ditambahkan!'); window.location='kategori.php';</script>") : 
        print("<script>alert('Gagal menambah kategori!');</script>");
}

// --- PROSES EDIT KATEGORI ---
if (isset($_POST['edit_kategori'])) {
    $id_kategori = $_POST['id_kategori'];
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    $query = "UPDATE kategori SET nama_kategori='$nama_kategori' WHERE id_kategori='$id_kategori'";
    mysqli_query($koneksi, $query) ? 
        print("<script>alert('Kategori berhasil diubah!'); window.location='kategori.php';</script>") : 
        print("<script>alert('Gagal mengubah kategori!');</script>");
}

// --- PROSES HAPUS KATEGORI ---
if (isset($_GET['hapus'])) {
    $id_kategori = $_GET['hapus'];
    // Gunakan try-catch (atau cek error) karena RESTRICT bisa menolak penghapusan jika kategori masih dipakai
    $query = "DELETE FROM kategori WHERE id_kategori='$id_kategori'";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Kategori berhasil dihapus!'); window.location='kategori.php';</script>";
    } else {
        echo "<script>alert('Gagal! Kategori ini tidak bisa dihapus karena masih digunakan oleh Alat.'); window.location='kategori.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Kategori - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <!-- Navbar Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="#"><i class="fas fa-user-shield text-warning"></i> Panel Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Menu Khusus Admin -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="user.php"><i class="fas fa-users"></i> Data User</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fas fa-box"></i> Inventaris</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="alat.php">Data Alat</a></li>
                            <li><a class="dropdown-item" href="kategori.php">Kategori Alat</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="peminjaman.php"><i class="fas fa-exchange-alt"></i> Peminjaman</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fas fa-history"></i> Log & Aktifitas</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="aktifitas.php">Kelola Aktifitas</a></li>
                            <li><a class="dropdown-item" href="log.php">Log Sistem</a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item me-3">
                        <span class="text-white fw-bold">Halo, <?= htmlspecialchars($_SESSION['username_admin']); ?>!</span>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-danger btn-sm text-white"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold text-secondary"><i class="fas fa-tags"></i> Data Kategori Alat</h3>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="fas fa-plus"></i> Tambah Kategori</button>
        </div>
        
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark text-center">
                        <tr>
                            <th width="10%">No</th>
                            <th>Nama Kategori</th>
                            <th width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id_kategori DESC");
                        while ($row = mysqli_fetch_assoc($query)) :
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['nama_kategori']); ?></td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_kategori']; ?>"><i class="fas fa-edit"></i> Edit</button>
                                <a href="kategori.php?hapus=<?= $row['id_kategori']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus kategori ini?');"><i class="fas fa-trash"></i> Hapus</a>
                            </td>
                        </tr>

                        <!-- Modal Edit Kategori -->
                        <div class="modal fade" id="modalEdit<?= $row['id_kategori']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-warning">
                                        <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Kategori</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="id_kategori" value="<?= $row['id_kategori']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Nama Kategori</label>
                                                <input type="text" name="nama_kategori" class="form-control" value="<?= htmlspecialchars($row['nama_kategori']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            <button type="submit" name="edit_kategori" class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Kategori -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Kategori</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori</label>
                            <input type="text" name="nama_kategori" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" name="tambah_kategori" class="btn btn-primary">Simpan Kategori</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>