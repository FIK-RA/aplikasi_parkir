<?php
session_set_cookie_params(0);
session_start();

if (!isset($_SESSION['id_peminjam'])) {
    header("Location: ../login-admin.php?pesan=Akses Ditolak!");
    exit;
}

require_once '../config/koneksi.php';
$id_peminjam = $_SESSION['id_peminjam'];

// Fitur Hapus Riwayat Pengembalian
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus_pengembalian') {
    $id_hapus = (int)$_GET['id'];

    $cek = mysqli_query($koneksi, "SELECT pengembalian.status_pembayaran 
                                   FROM pengembalian 
                                   JOIN peminjaman ON pengembalian.id_peminjaman = peminjaman.id_peminjaman
                                   WHERE pengembalian.id_pengembalian='$id_hapus' AND peminjaman.id_user='$id_peminjam'");
    $d_cek = mysqli_fetch_assoc($cek);

    if ($d_cek) {
        if ($d_cek['status_pembayaran'] == 'Belum Lunas') {
            echo "<script>alert('Pembayaran denda belum lunas! Riwayat pengembalian tidak dapat dihapus.'); window.location='pengembalian.php';</script>";
        } else {
            mysqli_query($koneksi, "DELETE FROM pengembalian WHERE id_pengembalian='$id_hapus'");
            echo "<script>alert('Riwayat pengembalian berhasil dihapus!'); window.location='pengembalian.php';</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat & Pengembalian - Peminjam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-info shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="fas fa-user-graduate"></i> Ruang Peminjam</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="daftar_alat.php"><i class="fas fa-list"></i> Daftar Alat</a></li>
                    <li class="nav-item"><a class="nav-link" href="pengajuan.php"><i class="fas fa-plus-circle"></i> Ajukan Pinjaman</a></li>
                    <li class="nav-item"><a class="nav-link active" href="pengembalian.php"><i class="fas fa-undo"></i> Riwayat & Pengembalian</a></li>
                </ul>
                <span class="text-white fw-bold me-3">Halo, <?= htmlspecialchars($_SESSION['username_peminjam']); ?>!</span>
                <a href="logout.php" class="btn btn-danger btn-sm text-white"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <h3 class="fw-bold text-secondary mb-4"><i class="fas fa-undo-alt me-2"></i>Status Pengembalian Alat</h3>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-info text-center">
                            <tr>
                                <th>Kode Pinjam</th>
                                <th>Alat</th>
                                <th>Tgl Kembali</th>
                                <th>Kondisi</th>
                                <th>Total Denda</th>
                                <th>Status Pembayaran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q_kembali = mysqli_query($koneksi, "SELECT pengembalian.*, peminjaman.id_peminjaman, alat.nama_alat 
                                                                 FROM pengembalian 
                                                                 JOIN peminjaman ON pengembalian.id_peminjaman = peminjaman.id_peminjaman 
                                                                 JOIN alat ON peminjaman.id_alat = alat.id_alat 
                                                                 WHERE peminjaman.id_user = '$id_peminjam' 
                                                                 ORDER BY pengembalian.id_pengembalian DESC");

                            if (mysqli_num_rows($q_kembali) > 0):
                                while ($r = mysqli_fetch_assoc($q_kembali)):
                                    $is_disabled = ($r['status_pembayaran'] == 'Belum Lunas');
                            ?>
                            <tr>
                                <td class="text-center fw-bold">#PJ-<?= str_pad($r['id_peminjaman'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?= htmlspecialchars($r['nama_alat']); ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($r['tgl_kembali'])); ?></td>
                                <td class="text-center"><?= htmlspecialchars($r['kondisi_alat']); ?></td>
                                <td class="text-end fw-bold">Rp <?= number_format($r['total_denda'], 0, ',', '.'); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $r['status_pembayaran'] == 'Lunas' ? 'success' : 'danger'; ?>">
                                        <?= $r['status_pembayaran']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($is_disabled): ?>
                                        <button class="btn btn-secondary btn-sm" disabled title="Selesaikan pembayaran denda terlebih dahulu">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    <?php else: ?>
                                        <a href="pengembalian.php?aksi=hapus_pengembalian&id=<?= $r['id_pengembalian']; ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus riwayat pengembalian ini?')">
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
                                <td colspan="7" class="text-center text-muted py-3">Belum ada catatan pengembalian.</td>
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