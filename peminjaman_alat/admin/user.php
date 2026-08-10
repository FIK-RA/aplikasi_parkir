<?php
session_set_cookie_params(0);
session_start();
// Cek khusus session admin
if (!isset($_SESSION['id_admin'])) {
    header("Location: ../login-admin.php?pesan=Akses Ditolak! Silakan login dulu.");
    exit;
}
require_once '../config/koneksi.php';

// ==========================================
// 1. PROSES TAMBAH DATA (CREATE)
// ==========================================
if (isset($_POST['tambah'])) {
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password     = mysqli_real_escape_string($koneksi, $_POST['password']); // Idealnya di-hash (md5/bcrypt)
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $no_telepon   = mysqli_real_escape_string($koneksi, $_POST['no_telepon']);
    $alamat       = mysqli_real_escape_string($koneksi, $_POST['alamat']);

    $query = "INSERT INTO peminjam (username, password, nama_lengkap, no_telepon, alamat) 
              VALUES ('$username', '$password', '$nama_lengkap', '$no_telepon', '$alamat')";
    
    if (mysqli_query($koneksi, $query)) {
        // Catat Aktivitas
        catatAktifitas($koneksi, 'Admin', $_SESSION['username_admin'], "Menambahkan user peminjam baru: $username ($nama_lengkap)");

        echo "<script>alert('Data User berhasil ditambahkan!'); window.location='user.php';</script>";
    }
}

// ==========================================
// 2. PROSES EDIT DATA (UPDATE)
// ==========================================
if (isset($_POST['edit'])) {
    $id_peminjam  = $_POST['id_peminjam'];
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $no_telepon   = mysqli_real_escape_string($koneksi, $_POST['no_telepon']);
    $alamat       = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    
    // Jika kolom password diisi, maka update passwordnya. Jika kosong, biarkan password lama.
    if (!empty($_POST['password'])) {
        $password = mysqli_real_escape_string($koneksi, $_POST['password']);
        $query = "UPDATE peminjam SET username='$username', password='$password', nama_lengkap='$nama_lengkap', no_telepon='$no_telepon', alamat='$alamat' WHERE id_peminjam='$id_peminjam'";
    } else {
        $query = "UPDATE peminjam SET username='$username', nama_lengkap='$nama_lengkap', no_telepon='$no_telepon', alamat='$alamat' WHERE id_peminjam='$id_peminjam'";
    }

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data User berhasil diupdate!'); window.location='user.php';</script>";
    } else {
        echo "<script>alert('Gagal mengupdate data!'); window.location='user.php';</script>";
    }
}

// ==========================================
// 3. PROSES HAPUS DATA (DELETE)
// ==========================================
if (isset($_GET['hapus'])) {
    $id_peminjam = $_GET['hapus'];
    $query = "DELETE FROM peminjam WHERE id_peminjam='$id_peminjam'";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data User berhasil dihapus!'); window.location='user.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data! Pastikan user ini tidak memiliki relasi transaksi.'); window.location='user.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Data User - Admin</title>
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
            <h3 class="fw-bold text-secondary"><i class="fas fa-users"></i> Kelola Data User (Peminjam)</h3>
            <!-- Tombol Tambah memicu Modal -->
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-plus"></i> Tambah User Baru
            </button>
        </div>
        <hr>

        <!-- Tabel Tampil Data (READ) -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>No Telepon</th>
                                <th>Alamat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query_tampil = mysqli_query($koneksi, "SELECT * FROM peminjam ORDER BY id_peminjam DESC");
                            while ($row = mysqli_fetch_assoc($query_tampil)) :
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['username']); ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?= htmlspecialchars($row['no_telepon']); ?></td>
                                <td><?= htmlspecialchars($row['alamat']); ?></td>
                                <td class="text-center">
                                    <!-- Tombol Edit memicu Modal Edit -->
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_peminjam']; ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <!-- Tombol Hapus dengan Konfirmasi -->
                                    <a href="user.php?hapus=<?= $row['id_peminjam']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus user ini?');">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>

                            <!-- MODAL EDIT DATA -->
                            <div class="modal fade" id="modalEdit<?= $row['id_peminjam']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-warning">
                                            <h5 class="modal-title text-dark"><i class="fas fa-edit"></i> Edit Data User</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="">
                                            <div class="modal-body">
                                                <input type="hidden" name="id_peminjam" value="<?= $row['id_peminjam']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Username</label>
                                                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($row['username']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Password <small class="text-danger">(Kosongkan jika tidak ingin diganti)</small></label>
                                                    <input type="password" name="password" class="form-control">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Lengkap</label>
                                                    <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($row['nama_lengkap']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">No Telepon</label>
                                                    <input type="number" name="no_telepon" class="form-control" value="<?= htmlspecialchars($row['no_telepon']); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Alamat</label>
                                                    <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($row['alamat']); ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- AKHIR MODAL EDIT -->

                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH DATA BARU -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah User Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No Telepon</label>
                            <input type="number" name="no_telepon" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" name="tambah" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>