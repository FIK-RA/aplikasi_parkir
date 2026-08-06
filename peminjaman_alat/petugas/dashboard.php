<?php
session_set_cookie_params(0);
session_start();
// Cek khusus session admin
if (!isset($_SESSION['id_petugas'])) {
    header("Location: ../login-admin.php?pesan=Akses Ditolak! Silakan login dulu.");
    exit;
}
require_once '../config/koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Petugas - Peminjaman Alat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <!-- Navbar Petugas -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-clipboard-check"></i> Panel Petugas</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="persetujuan.php"><i class="fas fa-check-circle"></i> Persetujuan Pinjaman</a></li>
                    <li class="nav-item"><a class="nav-link" href="pantau_kembali.php"><i class="fas fa-box-open"></i> Pantau Pengembalian</a></li>
                    <li class="nav-item"><a class="nav-link" href="laporan.php"><i class="fas fa-print"></i> Cetak Laporan</a></li>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        <span class="text-white fw-bold">Halo, <?= htmlspecialchars($_SESSION['username']); ?>!</span>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-danger btn-sm text-white"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-3">
            <div class="col-12">
                <h3 class="fw-bold text-secondary">Tugas Hari Ini</h3>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-success">Permintaan Peminjaman Menunggu Persetujuan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Peminjam</th>
                                <th>Alat</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th class="text-center">Aksi (Petugas)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#PJ-001</td>
                                <td>Budi Peminjam</td>
                                <td>Proyektor Epson</td>
                                <td>1</td>
                                <td><span class="badge bg-warning text-dark">Pending</span></td>
                                <td class="text-center">
                                    <a href="../persetujuan.php?id=1&aksi=setuju" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Setuju</a>
                                    <a href="../persetujuan.php?id=1&aksi=tolak" class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Tolak</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>