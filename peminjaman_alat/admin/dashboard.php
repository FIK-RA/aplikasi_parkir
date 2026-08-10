<?php
session_set_cookie_params(0);
session_start();
// Cek khusus session admin
if (!isset($_SESSION['id_admin'])) {
    header("Location: ../login-admin.php?pesan=Akses Ditolak! Silakan login dulu.");
    exit;
}
require_once '../config/koneksi.php';

// --- MENGAMBIL DATA DINAMIS DARI DATABASE ---
$query_pengguna = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjam");
$data_pengguna = mysqli_fetch_assoc($query_pengguna);
$total_pengguna = $data_pengguna['total'];

$query_alat = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM alat");
$data_alat = mysqli_fetch_assoc($query_alat);
$total_alat = $data_alat['total'];

$query_transaksi = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman");
$data_transaksi = mysqli_fetch_assoc($query_transaksi);
$total_transaksi = $data_transaksi['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Peminjaman Alat</title>
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
        <h3 class="fw-bold text-secondary">Dashboard Admin</h3>
        <hr>
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card bg-primary text-white shadow border-0"><div class="card-body">
                    <h6><i class="fas fa-users"></i> Total Pengguna</h6>
                    <h2><?= $total_pengguna; ?></h2>
                </div></div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card bg-success text-white shadow border-0"><div class="card-body">
                    <h6><i class="fas fa-box"></i> Total Alat</h6>
                    <h2><?= $total_alat; ?></h2>
                </div></div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card bg-info text-white shadow border-0"><div class="card-body">
                    <h6><i class="fas fa-exchange-alt"></i> Total Transaksi</h6>
                    <h2><?= $total_transaksi; ?></h2>
                </div></div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>