<?php
session_set_cookie_params(0);
session_start();

// Cek khusus session petugas
if (!isset($_SESSION['id_petugas'])) {
    header("Location: ../login-admin.php?pesan=Akses Ditolak! Silakan login dulu.");
    exit;
}

require_once '../config/koneksi.php';

// 1. Hitung Statistik Ringkasan Sesuai ENUM Database ('pending', 'disetujui', 'selesai')
$q_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'pending'");
$total_pending = mysqli_fetch_assoc($q_pending)['total'] ?? 0;

$q_dipinjam = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'disetujui'");
$total_dipinjam = mysqli_fetch_assoc($q_dipinjam)['total'] ?? 0;

$q_selesai = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'selesai'");
$total_selesai = mysqli_fetch_assoc($q_selesai)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas - Peminjaman Alat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <!-- Navbar Petugas -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="fas fa-clipboard-check"></i> Panel Petugas</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="persetujuan.php"><i class="fas fa-check-circle"></i> Persetujuan Pinjaman</a></li>
                    <li class="nav-item"><a class="nav-link" href="pantau_kembali.php"><i class="fas fa-box-open"></i> Pantau Pengembalian</a></li>
                    <li class="nav-item"><a class="nav-link" href="laporan.php"><i class="fas fa-print"></i> Cetak Laporan</a></li>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        <span class="text-white fw-bold">Halo, <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username_petugas'] ?? 'Petugas'); ?>!</span>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-danger btn-sm text-white" onclick="return confirm('Apakah Anda yakin ingin keluar?');">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <!-- Alert Notifikasi -->
        <?php if (isset($_GET['pesan'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-1"></i> <?= htmlspecialchars($_GET['pesan']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Cards Ringkasan -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card bg-warning text-dark border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-1">Menunggu Persetujuan</h6>
                            <h2 class="display-6 fw-bold mb-0"><?= $total_pending; ?></h2>
                        </div>
                        <i class="fas fa-clock fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-primary text-white border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-1">Sedang Dipinjam</h6>
                            <h2 class="display-6 fw-bold mb-0"><?= $total_dipinjam; ?></h2>
                        </div>
                        <i class="fas fa-boxes fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-1">Selesai Dikembalikan</h6>
                            <h2 class="display-6 fw-bold mb-0"><?= $total_selesai; ?></h2>
                        </div>
                        <i class="fas fa-check-double fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Permintaan Peminjaman Pending -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-success"><i class="fas fa-list-alt"></i> Permintaan Peminjaman Menunggu Persetujuan</h5>
                <a href="persetujuan.php" class="btn btn-sm btn-outline-success">Lihat Semua</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Kode Pinjam</th>
                                <th>Peminjam</th>
                                <th>Alat</th>
                                <th>Jumlah</th>
                                <th>Tgl Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th>Aksi (Petugas)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query Join sesuai struktur tabel peminjam, alat, dan peminjaman
                            $query_pending = "SELECT peminjaman.*, peminjam.nama_lengkap, alat.nama_alat 
                                              FROM peminjaman 
                                              JOIN peminjam ON peminjaman.id_user = peminjam.id_peminjam 
                                              JOIN alat ON peminjaman.id_alat = alat.id_alat 
                                              WHERE peminjaman.status = 'pending' 
                                              ORDER BY peminjaman.id_peminjaman DESC";
                            $result_pending = mysqli_query($koneksi, $query_pending);

                            if (mysqli_num_rows($result_pending) > 0) :
                                while ($row = mysqli_fetch_assoc($result_pending)) :
                            ?>
                            <tr>
                                <td class="text-center fw-bold">#PJ-<?= str_pad($row['id_peminjaman'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?= htmlspecialchars($row['nama_alat']); ?></td>
                                <td class="text-center"><?= $row['jumlah']; ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_jatuh_tempo'])); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">Pending</span>
                                </td>
                                <td class="text-center">
                                    <a href="persetujuan.php?id=<?= $row['id_peminjaman']; ?>&aksi=setuju" 
                                       class="btn btn-sm btn-success text-white me-1" 
                                       onclick="return confirm('Setujui peminjaman ini?');">
                                        <i class="fas fa-check"></i> Setuju
                                    </a>
                                    <a href="persetujuan.php?id=<?= $row['id_peminjaman']; ?>&aksi=tolak" 
                                       class="btn btn-sm btn-danger text-white" 
                                       onclick="return confirm('Tolak peminjaman ini?');">
                                        <i class="fas fa-times"></i> Tolak
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else: 
                            ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i>
                                    Tidak ada permintaan peminjaman yang menunggu persetujuan saat ini.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>