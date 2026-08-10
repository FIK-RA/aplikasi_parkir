<?php
session_set_cookie_params(0);
session_start();

// Cek khusus session admin
if (!isset($_SESSION['id_admin'])) {
    header("Location: ../login-admin.php?pesan=Akses Ditolak! Silakan login dulu.");
    exit;
}

require_once '../config/koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Log Stok Sistem - Admin</title>
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
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
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
                        <a class="nav-link dropdown-toggle active" href="#" data-bs-toggle="dropdown"><i class="fas fa-history"></i> Log & Aktifitas</a>
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

    <div class="container mt-4 mb-5">
        <h3 class="fw-bold text-secondary mb-3"><i class="fas fa-boxes"></i> Log Perubahan Stok</h3>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Waktu</th>
                                <th>Nama Alat</th>
                                <th>Jenis</th>
                                <th>Jumlah</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $q = mysqli_query($koneksi, "SELECT log_stok.*, alat.nama_alat 
                                                        FROM log_stok 
                                                        JOIN alat ON log_stok.id_alat = alat.id_alat 
                                                        ORDER BY log_stok.id_log DESC");
                            while ($r = mysqli_fetch_assoc($q)) :
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td class="text-center"><?= $r['created_at']; ?></td>
                                <td><?= htmlspecialchars($r['nama_alat']); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $r['jenis_transaksi'] == 'masuk' ? 'success' : 'danger'; ?>">
                                        <?= strtoupper($r['jenis_transaksi']); ?>
                                    </span>
                                </td>
                                <td class="text-center"><?= $r['jumlah']; ?></td>
                                <td><?= htmlspecialchars($r['keterangan']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>