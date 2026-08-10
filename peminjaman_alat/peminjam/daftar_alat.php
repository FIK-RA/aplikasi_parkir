<?php
session_set_cookie_params(0);
session_start();

if (!isset($_SESSION['id_peminjam'])) {
    header("Location: ../login-admin.php?pesan=Akses Ditolak!");
    exit;
}

require_once '../config/koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Alat - Peminjam</title>
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
                    <li class="nav-item"><a class="nav-link active" href="daftar_alat.php"><i class="fas fa-list"></i> Daftar Alat</a></li>
                    <li class="nav-item"><a class="nav-link" href="pengajuan.php"><i class="fas fa-plus-circle"></i> Ajukan Pinjaman</a></li>
                    <li class="nav-item"><a class="nav-link" href="pengembalian.php"><i class="fas fa-undo"></i> Riwayat & Pengembalian</a></li>
                </ul>
                <span class="text-white fw-bold me-3">Halo, <?= htmlspecialchars($_SESSION['username_peminjam']); ?>!</span>
                <a href="logout.php" class="btn btn-danger btn-sm text-white"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <h3 class="fw-bold text-secondary mb-3"><i class="fas fa-boxes me-2"></i>Daftar Alat Tersedia</h3>

        <div class="row g-4">
            <?php
            $q_alat = mysqli_query($koneksi, "SELECT alat.*, kategori.nama_kategori 
                                              FROM alat 
                                              LEFT JOIN kategori ON alat.id_kategori = kategori.id_kategori 
                                              ORDER BY alat.nama_alat ASC");
            while ($r = mysqli_fetch_assoc($q_alat)):
            ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <span class="badge bg-secondary mb-2"><?= htmlspecialchars($r['nama_kategori'] ?? 'Umum'); ?></span>
                        <h5 class="card-title fw-bold text-dark"><?= htmlspecialchars($r['nama_alat']); ?></h5>
                        <p class="card-text text-muted mb-1">Kondisi: <strong><?= htmlspecialchars($r['kondisi']); ?></strong></p>
                        <p class="card-text">
                            Stok Tersedia: 
                            <span class="badge bg-<?= $r['stok'] > 0 ? 'success' : 'danger'; ?>">
                                <?= $r['stok']; ?> Unit
                            </span>
                        </p>
                    </div>
                    <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                        <?php if ($r['stok'] > 0): ?>
                            <a href="pengajuan.php?id_alat=<?= $r['id_alat']; ?>" class="btn btn-info text-white w-100 fw-bold">
                                <i class="fas fa-paper-plane me-1"></i> Pinjam Alat
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100 fw-bold" disabled>Stok Habis</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>