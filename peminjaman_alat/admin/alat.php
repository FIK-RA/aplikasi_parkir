<?php
session_set_cookie_params(0);
session_start();
// Cek khusus session admin
if (!isset($_SESSION['id_admin'])) {
    header("Location: ../login-admin.php?pesan=Akses Ditolak! Silakan login dulu.");
    exit;
}
require_once '../config/koneksi.php';

// --- PROSES TAMBAH ALAT ---
if (isset($_POST['tambah_alat'])) {
    $id_kategori = $_POST['id_kategori'];
    $nama_alat = mysqli_real_escape_string($koneksi, $_POST['nama_alat']);
    $stok = (int)$_POST['stok'];
    $kondisi = $_POST['kondisi'];

    $query = "INSERT INTO alat (id_kategori, nama_alat, stok, kondisi) VALUES ('$id_kategori', '$nama_alat', '$stok', '$kondisi')";
    if (mysqli_query($koneksi, $query)) {
        $id_alat = mysqli_insert_id($koneksi);

        // 1. Catat Log Stok Masuk Awal
        catatLogStok($koneksi, $id_alat, 'masuk', $stok, "Input stok awal alat baru");

        // 2. Catat Aktivitas Admin
        catatAktifitas($koneksi, 'Admin', $_SESSION['username_admin'], "Menambahkan alat baru: $nama_alat dengan stok $stok");

        echo "<script>alert('Alat berhasil ditambahkan!'); window.location='alat.php';</script>";
    }
}

// --- PROSES EDIT ALAT ---
if (isset($_POST['edit_alat'])) {
    $id_alat = $_POST['id_alat'];
    $id_kategori = $_POST['id_kategori'];
    $nama_alat = mysqli_real_escape_string($koneksi, $_POST['nama_alat']);
    $stok = $_POST['stok'];
    $kondisi = $_POST['kondisi'];

    $query = "UPDATE alat SET id_kategori='$id_kategori', nama_alat='$nama_alat', stok='$stok', kondisi='$kondisi' WHERE id_alat='$id_alat'";
    mysqli_query($koneksi, $query) ? 
        print("<script>alert('Alat berhasil diubah!'); window.location='alat.php';</script>") : 
        print("<script>alert('Gagal mengubah alat!');</script>");
}

// --- PROSES HAPUS ALAT ---
if (isset($_GET['hapus'])) {
    $id_alat = $_GET['hapus'];
    $query = "DELETE FROM alat WHERE id_alat='$id_alat'";
    mysqli_query($koneksi, $query) ? 
        print("<script>alert('Alat berhasil dihapus!'); window.location='alat.php';</script>") : 
        print("<script>alert('Gagal menghapus alat!');</script>");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Data Alat - Admin</title>
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
            <h3 class="fw-bold text-secondary"><i class="fas fa-toolbox"></i> Data Inventaris Alat</h3>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="fas fa-plus"></i> Tambah Alat</button>
        </div>
        
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Kategori</th>
                                <th>Nama Alat</th>
                                <th>Stok</th>
                                <th>Kondisi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            // Query menggunakan JOIN untuk mengambil nama_kategori
                            $query = mysqli_query($koneksi, "
                                SELECT alat.*, kategori.nama_kategori 
                                FROM alat 
                                LEFT JOIN kategori ON alat.id_kategori = kategori.id_kategori 
                                ORDER BY alat.id_alat DESC
                            ");
                            
                            while ($row = mysqli_fetch_assoc($query)) :
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori'); ?></span></td>
                                <td><?= htmlspecialchars($row['nama_alat']); ?></td>
                                <td class="text-center"><?= $row['stok']; ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['kondisi']); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_alat']; ?>"><i class="fas fa-edit"></i> Edit</button>
                                    <a href="alat.php?hapus=<?= $row['id_alat']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus alat ini?');"><i class="fas fa-trash"></i> Hapus</a>
                                </td>
                            </tr>

                            <!-- Modal Edit Alat -->
                            <div class="modal fade" id="modalEdit<?= $row['id_alat']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-warning">
                                            <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Alat</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id_alat" value="<?= $row['id_alat']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Kategori</label>
                                                    <select name="id_kategori" class="form-select" required>
                                                        <option value="">Pilih Kategori...</option>
                                                        <?php
                                                        $kategori_query = mysqli_query($koneksi, "SELECT * FROM kategori");
                                                        while ($kat = mysqli_fetch_assoc($kategori_query)) {
                                                            $selected = ($kat['id_kategori'] == $row['id_kategori']) ? 'selected' : '';
                                                            echo "<option value='{$kat['id_kategori']}' $selected>{$kat['nama_kategori']}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Alat</label>
                                                    <input type="text" name="nama_alat" class="form-control" value="<?= htmlspecialchars($row['nama_alat']); ?>" required>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Stok</label>
                                                        <input type="number" name="stok" class="form-control" value="<?= $row['stok']; ?>" required min="0">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Kondisi</label>
                                                        <select name="kondisi" class="form-select" required>
                                                            <option value="Baik" <?= ($row['kondisi'] == 'Baik') ? 'selected' : ''; ?>>Baik</option>
                                                            <option value="Rusak Ringan" <?= ($row['kondisi'] == 'Rusak Ringan') ? 'selected' : ''; ?>>Rusak Ringan</option>
                                                            <option value="Rusak Berat" <?= ($row['kondisi'] == 'Rusak Berat') ? 'selected' : ''; ?>>Rusak Berat</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                <button type="submit" name="edit_alat" class="btn btn-primary">Simpan Perubahan</button>
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
    </div>

    <!-- Modal Tambah Alat -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Alat</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kategori Alat</label>
                            <select name="id_kategori" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Kategori --</option>
                                <?php
                                // Ambil data kategori dari database untuk dropdown tambah
                                $kategori_query = mysqli_query($koneksi, "SELECT * FROM kategori");
                                while ($kat = mysqli_fetch_assoc($kategori_query)) {
                                    echo "<option value='{$kat['id_kategori']}'>{$kat['nama_kategori']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Alat</label>
                            <input type="text" name="nama_alat" class="form-control" required placeholder="Contoh: Router TP-Link">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stok</label>
                                <input type="number" name="stok" class="form-control" required min="1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kondisi</label>
                                <select name="kondisi" class="form-select" required>
                                    <option value="Baik">Baik</option>
                                    <option value="Rusak Ringan">Rusak Ringan</option>
                                    <option value="Rusak Berat">Rusak Berat</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" name="tambah_alat" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>