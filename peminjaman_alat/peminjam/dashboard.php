<?php
session_set_cookie_params(0);
session_start();

if (!isset($_SESSION['id_peminjam'])) {
    header("Location: ../login-admin.php?pesan=Akses Ditolak!");
    exit;
}

require_once '../config/koneksi.php';
$id_peminjam = $_SESSION['id_peminjam'];

// Fitur Hapus Peminjaman
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus_peminjaman') {
    $id_hapus = (int)$_GET['id'];
    
    // Cek status peminjaman & status pembayaran
    $cek = mysqli_query($koneksi, "SELECT peminjaman.status, pengembalian.status_pembayaran 
                                   FROM peminjaman 
                                   LEFT JOIN pengembalian ON peminjaman.id_peminjaman = pengembalian.id_peminjaman 
                                   WHERE peminjaman.id_peminjaman='$id_hapus' AND peminjaman.id_user='$id_peminjam'");
    $d_cek = mysqli_fetch_assoc($cek);

    if ($d_cek) {
        if ($d_cek['status'] == 'disetujui') {
            echo "<script>alert('Peminjaman sedang aktif / disetujui, tidak dapat dihapus!'); window.location='dashboard.php';</script>";
        } elseif ($d_cek['status_pembayaran'] == 'Belum Lunas') {
            echo "<script>alert('Pembayaran denda belum lunas! Data tidak dapat dihapus.'); window.location='dashboard.php';</script>";
        } else {
            mysqli_query($koneksi, "DELETE FROM pengembalian WHERE id_peminjaman='$id_hapus'");
            mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id_peminjaman='$id_hapus' AND id_user='$id_peminjam'");
            echo "<script>alert('Data peminjaman berhasil dihapus!'); window.location='dashboard.php';</script>";
        }
    }
}

// Hitung Ringkasan Statistik Peminjam
$q_pending   = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE id_user='$id_peminjam' AND status='pending'");
$q_dipinjam  = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE id_user='$id_peminjam' AND status='disetujui'");
$q_selesai   = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE id_user='$id_peminjam' AND status='selesai'");

$total_pending  = mysqli_fetch_assoc($q_pending)['total'];
$total_dipinjam = mysqli_fetch_assoc($q_dipinjam)['total'];
$total_selesai  = mysqli_fetch_assoc($q_selesai)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Peminjam - Sistem Sarpras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-info shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="fas fa-user-graduate"></i> Ruang Peminjam</a>
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
                        <span class="text-white fw-bold">Halo, <?= htmlspecialchars($_SESSION['username_peminjam'] ?? 'Peminjam'); ?>!</span>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-danger btn-sm text-white"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <div class="row mb-4">
            <div class="col-md-12">
                <h3 class="fw-bold text-secondary">Dashboard Peminjam</h3>
                <p class="text-muted">Selamat datang di Sistem Informasi Peminjaman Sarana & Prasarana.</p>
            </div>
        </div>

        <!-- Kartu Statistik -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card bg-warning text-dark border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-1">Menunggu Persetujuan</h6>
                            <h2 class="mb-0 fw-bold"><?= $total_pending; ?></h2>
                        </div>
                        <i class="fas fa-clock fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-1">Sedang Dipinjam</h6>
                            <h2 class="mb-0 fw-bold"><?= $total_dipinjam; ?></h2>
                        </div>
                        <i class="fas fa-box-open fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-primary text-white border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-1">Peminjaman Selesai</h6>
                            <h2 class="mb-0 fw-bold"><?= $total_selesai; ?></h2>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Status Peminjaman Saya -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-history me-2"></i>Status Peminjaman Saya</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th>ID Transaksi</th>
                                <th>Alat</th>
                                <th>Jumlah</th>
                                <th>Tanggal Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q_peminjaman = mysqli_query($koneksi, "SELECT peminjaman.*, alat.nama_alat, pengembalian.status_pembayaran 
                                                                    FROM peminjaman 
                                                                    JOIN alat ON peminjaman.id_alat = alat.id_alat 
                                                                    LEFT JOIN pengembalian ON peminjaman.id_peminjaman = pengembalian.id_peminjaman
                                                                    WHERE peminjaman.id_user = '$id_peminjam' 
                                                                    ORDER BY peminjaman.id_peminjaman DESC");

                            if (mysqli_num_rows($q_peminjaman) > 0):
                                while ($r = mysqli_fetch_assoc($q_peminjaman)):
                                    $badge = 'secondary';
                                    if ($r['status'] == 'pending') $badge = 'warning text-dark';
                                    elseif ($r['status'] == 'disetujui') $badge = 'success';
                                    elseif ($r['status'] == 'ditolak') $badge = 'danger';
                                    elseif ($r['status'] == 'selesai') $badge = 'info text-white';

                                    // Cek apakah tombol hapus harus dinonaktifkan
                                    $is_disabled = ($r['status_pembayaran'] == 'Belum Lunas' || $r['status'] == 'disetujui');
                            ?>
                            <tr>
                                <td class="text-center fw-bold">#PJ-<?= str_pad($r['id_peminjaman'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?= htmlspecialchars($r['nama_alat']); ?></td>
                                <td class="text-center"><?= $r['jumlah']; ?> Unit</td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($r['tgl_pinjam'])); ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($r['tgl_jatuh_tempo'])); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $badge; ?>"><?= ucfirst($r['status']); ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($is_disabled): ?>
                                        <button class="btn btn-secondary btn-sm" disabled title="Tidak dapat dihapus karena belum lunas atau sedang dipinjam">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    <?php else: ?>
                                        <a href="dashboard.php?aksi=hapus_peminjaman&id=<?= $r['id_peminjaman']; ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus peminjaman ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else: 
                            ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">Belum ada riwayat peminjaman.</td>
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