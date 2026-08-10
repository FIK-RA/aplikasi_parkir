<?php
session_set_cookie_params(0);
session_start();

if (!isset($_SESSION['id_petugas'])) {
    header("Location: ../login-admin.php?pesan=Akses Ditolak! Silakan login dulu.");
    exit;
}

require_once '../config/koneksi.php';

/**
 * Fungsi Konversi Sisa Hari ke Format (Bulan, Minggu, Hari)
 */
function hitungSisaWaktu($tgl_jatuh_tempo) {
    $today = new DateTime(date('Y-m-d'));
    $due   = new DateTime($tgl_jatuh_tempo);
    
    if ($today > $due) {
        $terlambat = $today->diff($due)->days;
        return "<span class='badge bg-danger'><i class='fas fa-exclamation-triangle'></i> Terlambat $terlambat hari</span>";
    }
    
    $total_hari = $today->diff($due)->days;
    
    if ($total_hari == 0) {
        return "<span class='badge bg-warning text-dark'><i class='fas fa-clock'></i> Jatuh Tempo Hari Ini</span>";
    }
    
    // Perhitungan hitung mundur
    $bulan = floor($total_hari / 30);
    $sisa_setelah_bulan = $total_hari % 30;
    $minggu = floor($sisa_setelah_bulan / 7);
    $hari   = $sisa_setelah_bulan % 7;
    
    $format = [];
    if ($bulan > 0)  $format[] = "$bulan bulan";
    if ($minggu > 0) $format[] = "$minggu minggu";
    if ($hari > 0)   $format[] = "$hari hari";
    
    $sisa_teks = implode(" ", $format);
    return "<span class='badge bg-info text-dark'><i class='fas fa-hourglass-half'></i> Sisa $sisa_teks</span>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pantau Pengembalian - Panel Petugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="fas fa-clipboard-check"></i> Panel Petugas</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="persetujuan.php"><i class="fas fa-check-circle"></i> Persetujuan Pinjaman</a></li>
                    <li class="nav-item"><a class="nav-link active" href="pantau_kembali.php"><i class="fas fa-box-open"></i> Pantau Pengembalian</a></li>
                    <li class="nav-item"><a class="nav-link" href="laporan.php"><i class="fas fa-print"></i> Cetak Laporan</a></li>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        <span class="text-white fw-bold">Halo, <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username_petugas'] ?? 'Petugas'); ?>!</span>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-danger btn-sm text-white" onclick="return confirm('Keluar dari aplikasi?');"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold text-success"><i class="fas fa-boxes text-success"></i> Pemantauan Alat Sedang Dipinjam</h4>
        </div>

        <!-- Tabel Monitoring Barang Dipinjam -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>Kode Pinjam</th>
                                <th>Nama Peminjam</th>
                                <th>Nama Alat</th>
                                <th>Jumlah</th>
                                <th>Tgl Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Sisa Waktu Pinjam</th>
                                <th>Aksi Process</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT peminjaman.*, peminjam.nama_lengkap, alat.nama_alat 
                                      FROM peminjaman 
                                      JOIN peminjam ON peminjaman.id_user = peminjam.id_peminjam 
                                      JOIN alat ON peminjaman.id_alat = alat.id_alat 
                                      WHERE peminjaman.status = 'disetujui' 
                                      ORDER BY peminjaman.tgl_jatuh_tempo ASC";
                            $result = mysqli_query($koneksi, $query);

                            if (mysqli_num_rows($result) > 0) :
                                while ($row = mysqli_fetch_assoc($result)) :
                            ?>
                            <tr>
                                <td class="text-center fw-bold">#PJ-<?= str_pad($row['id_peminjaman'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?= htmlspecialchars($row['nama_alat']); ?></td>
                                <td class="text-center fw-bold"><?= $row['jumlah']; ?> unit</td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_jatuh_tempo'])); ?></td>
                                <td class="text-center"><?= hitungSisaWaktu($row['tgl_jatuh_tempo']); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-primary btn-sm text-white" data-bs-toggle="modal" data-bs-target="#modalKembali<?= $row['id_peminjaman']; ?>">
                                        <i class="fas fa-undo"></i> Proses Pengembalian
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Proses Pengembalian -->
                            <div class="modal fade" id="modalKembali<?= $row['id_peminjaman']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="proses_kembali.php" method="POST">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title"><i class="fas fa-box-open me-2"></i>Pengembalian Alat #PJ-<?= str_pad($row['id_peminjaman'], 3, '0', STR_PAD_LEFT); ?></h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="id_peminjaman" value="<?= $row['id_peminjaman']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Nama Peminjam</label>
                                                    <input type="text" class="form-control" value="<?= htmlspecialchars($row['nama_lengkap']); ?>" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Nama Alat (Jumlah)</label>
                                                    <input type="text" class="form-control" value="<?= htmlspecialchars($row['nama_alat']); ?> (<?= $row['jumlah']; ?> unit)" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Tanggal Pengembalian</label>
                                                    <input type="date" name="tgl_kembali" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Kondisi Alat</label>
                                                    <select name="kondisi_alat" class="form-select" required>
                                                        <option value="Baik">Baik / Normal</option>
                                                        <option value="Rusak">Rusak</option>
                                                        <option value="Hilang">Hilang</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Simpan Pengembalian</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <?php 
                                endwhile;
                            else: 
                            ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                    Saat ini tidak ada alat yang sedang dipinjam.
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