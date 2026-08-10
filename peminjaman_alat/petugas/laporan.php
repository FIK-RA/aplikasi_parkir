<?php
session_set_cookie_params(0);
session_start();

if (!isset($_SESSION['id_petugas'])) {
    header("Location: ../login-admin.php");
    exit;
}

require_once '../config/koneksi.php';

// ==========================================
// LOGIKA HAPUS RIWAYAT
// ==========================================

// 1. Hapus Riwayat Peminjaman
if (isset($_GET['hapus_pinjam'])) {
    $id_peminjaman = mysqli_real_escape_string($koneksi, $_GET['hapus_pinjam']);
    
    // Hapus data terkait di pengembalian terlebih dahulu agar tidak terhalang Foreign Key
    mysqli_query($koneksi, "DELETE FROM pengembalian WHERE id_peminjaman='$id_peminjaman'");
    
    if (mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id_peminjaman='$id_peminjaman'")) {
        echo "<script>alert('Riwayat peminjaman berhasil dihapus!'); window.location='laporan.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus riwayat peminjaman!'); window.location='laporan.php';</script>";
    }
}

// 2. Hapus Riwayat Pengembalian
if (isset($_GET['hapus_kembali'])) {
    $id_pengembalian = mysqli_real_escape_string($koneksi, $_GET['hapus_kembali']);
    
    if (mysqli_query($koneksi, "DELETE FROM pengembalian WHERE id_pengembalian='$id_pengembalian'")) {
        echo "<script>alert('Riwayat pengembalian berhasil dihapus!'); window.location='laporan.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus riwayat pengembalian!'); window.location='laporan.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan - Panel Petugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* CSS Khusus Mode Cetak / Export PDF */
        @media print {
            @page {
                size: A4 portrait;
                margin: 15mm;
            }
            body {
                background-color: #fff !important;
                font-size: 11px !important;
                color: #000 !important;
            }
            .no-print { 
                display: none !important; 
            }
            .container, .container-fluid {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .card { 
                border: none !important; 
                box-shadow: none !important; 
                margin-bottom: 20px !important;
            }
            .card-body {
                padding: 0 !important;
            }
            .table-responsive { 
                overflow: visible !important; 
                display: block !important;
                width: 100% !important;
            }
            .table {
                width: 100% !important;
                margin-bottom: 0 !important;
                border-collapse: collapse !important;
                table-layout: auto !important;
            }
            .table th, .table td {
                padding: 5px 6px !important;
                font-size: 10px !important;
                border: 1px solid #333 !important;
                word-wrap: break-word;
            }
            .table thead {
                background-color: #f2f2f2 !important;
                -webkit-print-color-adjust: exact;
            }
            .tab-pane {
                display: block !important;
                opacity: 1 !important;
                visibility: visible !important;
                page-break-inside: avoid;
            }
            .tab-content > .tab-pane:not(.active) {
                margin-top: 30px;
            }
            .badge {
                border: 1px solid #000 !important;
                color: #000 !important;
                background: transparent !important;
            }
        }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm no-print">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="fas fa-clipboard-check"></i> Panel Petugas</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="persetujuan.php"><i class="fas fa-check-circle"></i> Persetujuan Pinjaman</a></li>
                    <li class="nav-item"><a class="nav-link" href="pantau_kembali.php"><i class="fas fa-box-open"></i> Pantau Pengembalian</a></li>
                    <li class="nav-item"><a class="nav-link active" href="laporan.php"><i class="fas fa-print"></i> Cetak Laporan</a></li>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        <span class="text-white fw-bold">Halo, <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Petugas'); ?>!</span>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-danger btn-sm text-white"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h4 class="fw-bold text-success"><i class="fas fa-file-invoice me-2"></i> Laporan & Riwayat Transaksi</h4>
            <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print me-1"></i> Cetak Halaman (PDF)</button>
        </div>

        <!-- Header Khusus Saat Dicetak -->
        <div class="d-none d-print-block text-center mb-4">
            <h3 class="fw-bold">LAPORAN PEMINJAMAN & PENGEMBALIAN ALAT</h3>
            <p class="mb-0">Sistem Informasi Peminjaman Sarana dan Prasarana</p>
            <hr style="border: 1px solid #000;">
        </div>

        <!-- Nav Tabs -->
        <ul class="nav nav-tabs no-print mb-3" id="laporanTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="pinjam-tab" data-bs-toggle="tab" data-bs-target="#pinjam" type="button" role="tab"><i class="fas fa-history me-1"></i> Riwayat Peminjaman</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="kembali-tab" data-bs-toggle="tab" data-bs-target="#kembali" type="button" role="tab"><i class="fas fa-undo me-1"></i> Riwayat Pengembalian</button>
            </li>
        </ul>

        <div class="tab-content" id="laporanTabContent">
            
            <!-- TAB 1: RIWAYAT PEMINJAMAN -->
            <div class="tab-pane fade show active" id="pinjam" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list me-2"></i>Data Riwayat Peminjaman Alat</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-success text-center">
                                    <tr>
                                        <th>Kode Pinjam</th>
                                        <th>Peminjam</th>
                                        <th>Alat</th>
                                        <th>Jumlah</th>
                                        <th>Tgl Pinjam</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Status</th>
                                        <th class="no-print">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $q_pinjam = mysqli_query($koneksi, "SELECT peminjaman.*, peminjam.nama_lengkap, alat.nama_alat 
                                                                        FROM peminjaman 
                                                                        JOIN peminjam ON peminjaman.id_user = peminjam.id_peminjam 
                                                                        JOIN alat ON peminjaman.id_alat = alat.id_alat 
                                                                        ORDER BY peminjaman.id_peminjaman DESC");
                                    while ($r_pinjam = mysqli_fetch_assoc($q_pinjam)):
                                    ?>
                                    <tr>
                                        <td class="text-center fw-bold">#PJ-<?= str_pad($r_pinjam['id_peminjaman'], 3, '0', STR_PAD_LEFT); ?></td>
                                        <td><?= htmlspecialchars($r_pinjam['nama_lengkap']); ?></td>
                                        <td><?= htmlspecialchars($r_pinjam['nama_alat']); ?></td>
                                        <td class="text-center"><?= $r_pinjam['jumlah']; ?></td>
                                        <td class="text-center"><?= date('d/m/Y', strtotime($r_pinjam['tgl_pinjam'])); ?></td>
                                        <td class="text-center"><?= date('d/m/Y', strtotime($r_pinjam['tgl_jatuh_tempo'])); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $r_pinjam['status'] == 'selesai' ? 'success' : ($r_pinjam['status'] == 'disetujui' ? 'primary' : ($r_pinjam['status'] == 'pending' ? 'warning' : 'danger')); ?>">
                                                <?= ucfirst($r_pinjam['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center no-print">
                                            <a href="cetak_struk.php?tipe=peminjaman&id=<?= $r_pinjam['id_peminjaman']; ?>" target="_blank" class="btn btn-sm btn-outline-dark me-1" title="Cetak Struk">
                                                <i class="fas fa-receipt"></i> Struk
                                            </a>
                                            <a href="laporan.php?hapus_pinjam=<?= $r_pinjam['id_peminjaman']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus riwayat peminjaman ini?');" title="Hapus Data">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: RIWAYAT PENGEMBALIAN -->
            <div class="tab-pane fade" id="kembali" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-receipt me-2"></i>Data Riwayat Pengembalian & Denda</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-primary text-center">
                                    <tr>
                                        <th>Kode Kembali</th>
                                        <th>Kode Pinjam</th>
                                        <th>Peminjam</th>
                                        <th>Tgl Kembali</th>
                                        <th>Kondisi</th>
                                        <th>Total Denda</th>
                                        <th>Status Pembayaran</th>
                                        <th class="no-print">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $q_kembali = mysqli_query($koneksi, "SELECT pengembalian.*, peminjaman.id_peminjaman, peminjam.nama_lengkap 
                                                                         FROM pengembalian 
                                                                         JOIN peminjaman ON pengembalian.id_peminjaman = peminjaman.id_peminjaman 
                                                                         JOIN peminjam ON peminjaman.id_user = peminjam.id_peminjam 
                                                                         ORDER BY pengembalian.id_pengembalian DESC");
                                    while ($r_kembali = mysqli_fetch_assoc($q_kembali)):
                                    ?>
                                    <tr>
                                        <td class="text-center fw-bold">#KB-<?= str_pad($r_kembali['id_pengembalian'], 3, '0', STR_PAD_LEFT); ?></td>
                                        <td class="text-center">#PJ-<?= str_pad($r_kembali['id_peminjaman'], 3, '0', STR_PAD_LEFT); ?></td>
                                        <td><?= htmlspecialchars($r_kembali['nama_lengkap']); ?></td>
                                        <td class="text-center"><?= date('d/m/Y', strtotime($r_kembali['tgl_kembali'])); ?></td>
                                        <td class="text-center"><?= htmlspecialchars($r_kembali['kondisi_alat']); ?></td>
                                        <td class="text-end fw-bold">Rp <?= number_format($r_kembali['total_denda'], 0, ',', '.'); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $r_kembali['status_pembayaran'] == 'Lunas' ? 'success' : 'danger'; ?>">
                                                <?= $r_kembali['status_pembayaran']; ?>
                                            </span>
                                        </td>
                                        <td class="text-center no-print">
                                            <a href="cetak_struk.php?tipe=pengembalian&id=<?= $r_kembali['id_pengembalian']; ?>" target="_blank" class="btn btn-sm btn-outline-dark me-1" title="Cetak Struk">
                                                <i class="fas fa-receipt"></i> Struk
                                            </a>
                                            <a href="laporan.php?hapus_kembali=<?= $r_kembali['id_pengembalian']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus riwayat pengembalian ini?');" title="Hapus Data">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>