<?php
session_set_cookie_params(0);
session_start();
// Cek khusus session admin
if (!isset($_SESSION['id_peminjam'])) {
    header("Location: ../login-admin.php?pesan=Akses Ditolak! Silakan login dulu.");
    exit;
}
require_once '../config/koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ruang Peminjam - Peminjaman Alat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <!-- Navbar Peminjam -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-info shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-user-graduate"></i> Ruang Peminjam</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="daftar_alat.php"><i class="fas fa-list"></i> Daftar Alat</a></li>
                    <li class="nav-item"><a class="nav-link" href="pengajuan.php"><i class="fas fa-plus-circle"></i> Ajukan Pinjaman</a></li>
                    <li class="nav-item"><a class="nav-link" href="pengembalian.php"><i class="fas fa-undo"></i> Riwayat & Pengembalian</a></li>
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
        <h3 class="fw-bold text-secondary">Status Peminjaman Saya</h3>
        <p class="text-muted">Pantau status alat yang sedang Anda pinjam di sini.</p>
        
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Alat</th>
                            <th>Tanggal Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data ini khusus milik Peminjam yang sedang login -->
                        <tr>
                            <td>#PJ-001</td>
                            <td>Proyektor Epson</td>
                            <td>2026-08-01</td>
                            <td>2026-08-03</td>
                            <td><span class="badge bg-success">Disetujui / Dipinjam</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>