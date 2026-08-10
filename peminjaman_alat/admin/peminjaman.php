<?php
session_set_cookie_params(0);
session_start();

if (!isset($_SESSION['id_admin'])) {
    header("Location: ../login-admin.php?pesan=Akses Ditolak! Silakan login dulu.");
    exit;
}
require_once '../config/koneksi.php';

// ==========================================
// 1. PROSES CRUD PEMINJAMAN
// ==========================================

// --- TAMBAH PEMINJAMAN ---
if (isset($_POST['tambah_peminjaman'])) {
    $id_user = $_POST['id_user'];
    $id_alat = $_POST['id_alat'];
    $tgl_pinjam = $_POST['tgl_pinjam'];
    $tgl_jatuh_tempo = $_POST['tgl_jatuh_tempo'];
    $jumlah = (int)$_POST['jumlah'];

    // Cek Stok Alat
    $q_stok = mysqli_query($koneksi, "SELECT stok FROM alat WHERE id_alat='$id_alat'");
    $d_stok = mysqli_fetch_assoc($q_stok);

    if ($d_stok['stok'] < $jumlah) {
        echo "<script>alert('Stok alat tidak mencukupi!'); window.location='peminjaman.php';</script>";
    } else {
        // Insert Peminjaman
        $query = "INSERT INTO peminjaman (id_user, id_alat, tgl_pinjam, tgl_jatuh_tempo, jumlah, status) 
                  VALUES ('$id_user', '$id_alat', '$tgl_pinjam', '$tgl_jatuh_tempo', '$jumlah', 'disetujui')";
        if (mysqli_query($koneksi, $query)) {
            // Kurangi Stok
            mysqli_query($koneksi, "UPDATE alat SET stok = stok - $jumlah WHERE id_alat='$id_alat'");
            // 1. Catat Log Stok Keluar
        catatLogStok($koneksi, $id_alat, 'keluar', $jumlah, "Dipinjam oleh user (ID: $id_user)");

        // 2. Catat Aktivitas
        catatAktifitas($koneksi, 'Admin', $_SESSION['username_admin'], "Menyetujui peminjaman alat ID $id_alat sebanyak $jumlah unit");

        echo "<script>alert('Peminjaman berhasil dicatat!'); window.location='peminjaman.php';</script>";
        }
    }
}

// --- EDIT PEMINJAMAN ---
if (isset($_POST['edit_peminjaman'])) {
    $id_peminjaman = $_POST['id_peminjaman'];
    $id_alat = $_POST['id_alat'];
    $jumlah_lama = (int)$_POST['jumlah_lama'];
    $jumlah_baru = (int)$_POST['jumlah_baru'];
    $tgl_pinjam = $_POST['tgl_pinjam'];
    $tgl_jatuh_tempo = $_POST['tgl_jatuh_tempo'];

    $selisih = $jumlah_baru - $jumlah_lama;

    // Cek ketersediaan stok jika jumlah bertambah
    $q_stok = mysqli_query($koneksi, "SELECT stok FROM alat WHERE id_alat='$id_alat'");
    $d_stok = mysqli_fetch_assoc($q_stok);

    if ($selisih > 0 && $d_stok['stok'] < $selisih) {
        echo "<script>alert('Stok tidak mencukupi untuk penambahan jumlah!'); window.location='peminjaman.php';</script>";
    } else {
        $query = "UPDATE peminjaman SET tgl_pinjam='$tgl_pinjam', tgl_jatuh_tempo='$tgl_jatuh_tempo', jumlah='$jumlah_baru' WHERE id_peminjaman='$id_peminjaman'";
        if (mysqli_query($koneksi, $query)) {
            // Adjust Stok
            mysqli_query($koneksi, "UPDATE alat SET stok = stok - ($selisih) WHERE id_alat='$id_alat'");
            echo "<script>alert('Data peminjaman berhasil diperbarui!'); window.location='peminjaman.php';</script>";
        }
    }
}

// --- HAPUS PEMINJAMAN ---
if (isset($_GET['hapus_peminjaman'])) {
    $id_peminjaman = $_GET['hapus_peminjaman'];

    // Ambil data peminjaman untuk mengembalikan stok
    $q_pinjam = mysqli_query($koneksi, "SELECT id_alat, jumlah FROM peminjaman WHERE id_peminjaman='$id_peminjaman'");
    $d_pinjam = mysqli_fetch_assoc($q_pinjam);

    if ($d_pinjam) {
        $id_alat = $d_pinjam['id_alat'];
        $jumlah = $d_pinjam['jumlah'];

        // Kembalikan stok
        mysqli_query($koneksi, "UPDATE alat SET stok = stok + $jumlah WHERE id_alat='$id_alat'");
        mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id_peminjaman='$id_peminjaman'");
        echo "<script>alert('Data peminjaman dihapus dan stok dikembalikan!'); window.location='peminjaman.php';</script>";
    }
}

// ==========================================
// 2. PROSES PROSES PENGEMBALIAN & DENDA
// ==========================================

if (isset($_POST['proses_pengembalian'])) {
    $id_peminjaman = $_POST['id_peminjaman'];
    $tgl_kembali = $_POST['tgl_kembali'];
    $kondisi_alat = $_POST['kondisi_alat'];

    // Detail Peminjaman
    $q_pinjam = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_peminjaman='$id_peminjaman'");
    $d_pinjam = mysqli_fetch_assoc($q_pinjam);

    $id_alat = $d_pinjam['id_alat'];
    $jumlah = (int)$d_pinjam['jumlah'];
    $tgl_jt = new DateTime($d_pinjam['tgl_jatuh_tempo']);
    $tgl_km = new DateTime($tgl_kembali);

    // Hitung Selisih Hari Terlambat
    $hari_terlambat = 0;
    if ($tgl_km > $tgl_jt) {
        $diff = $tgl_jt->diff($tgl_km);
        $hari_terlambat = $diff->days;
    }

    // Perhitungan Denda Keterlambatan
    // Harga dasar = Rp 5.000 * jumlah alat
    $biaya_awal = 5000 * $jumlah;
    if ($hari_terlambat > 0) {
        $denda_keterlambatan = $biaya_awal + ($hari_terlambat * 2500);
    } else {
        $denda_keterlambatan = $biaya_awal; 
    }

    // Perhitungan Denda Kerusakan
    $denda_kerusakan = 0;
    if ($kondisi_alat == 'Rusak Ringan') {
        $denda_kerusakan = $denda_keterlambatan * 0.25;
    } elseif ($kondisi_alat == 'Rusak Berat') {
        $denda_kerusakan = $denda_keterlambatan * 0.50;
    }

    $total_denda = $denda_keterlambatan + $denda_kerusakan;

    // Simpan ke Tabel Pengembalian
    $q_kembali = "INSERT INTO pengembalian (id_peminjaman, tgl_kembali, kondisi_alat, denda_keterlambatan, denda_kerusakan, total_denda, status_pembayaran) 
                  VALUES ('$id_peminjaman', '$tgl_kembali', '$kondisi_alat', '$denda_keterlambatan', '$denda_kerusakan', '$total_denda', 'Belum Lunas')";

    if (mysqli_query($koneksi, $q_kembali)) {
        // Kembalikan Stok Alat
        mysqli_query($koneksi, "UPDATE alat SET stok = stok + $jumlah WHERE id_alat='$id_alat'");
        mysqli_query($koneksi, "UPDATE peminjaman SET status='selesai' WHERE id_peminjaman='$id_peminjaman'");

        // 1. Catat Log Stok Masuk
        catatLogStok($koneksi, $id_alat, 'masuk', $jumlah, "Pengembalian alat dari transaksi ID: $id_peminjaman");

        // 2. Catat Aktivitas
        catatAktifitas($koneksi, 'Admin', $_SESSION['username_admin'], "Memproses pengembalian alat ID $id_alat (Kondisi: $kondisi_alat)");

        echo "<script>alert('Pengembalian berhasil diproses!'); window.location='peminjaman.php';</script>";
    }
}

// ==========================================
// 3. PROSES STATUS PEMBAYARAN & HAPUS PENGEMBALIAN
// ==========================================

// --- UBAH STATUS PEMBAYARAN ---
if (isset($_GET['bayar_id'])) {
    $id_pengembalian = $_GET['bayar_id'];
    $status_baru = $_GET['status'];

    mysqli_query($koneksi, "UPDATE pengembalian SET status_pembayaran='$status_baru' WHERE id_pengembalian='$id_pengembalian'");
    echo "<script>window.location='peminjaman.php';</script>";
}

// --- HAPUS PENGEMBALIAN ---
if (isset($_GET['hapus_pengembalian'])) {
    $id_pengembalian = $_GET['hapus_pengembalian'];

    // Cek status pembayaran
    $q_cek = mysqli_query($koneksi, "SELECT status_pembayaran FROM pengembalian WHERE id_pengembalian='$id_pengembalian'");
    $d_cek = mysqli_fetch_assoc($q_cek);

    if ($d_cek['status_pembayaran'] == 'Lunas') {
        mysqli_query($koneksi, "DELETE FROM pengembalian WHERE id_pengembalian='$id_pengembalian'");
        echo "<script>alert('Data pengembalian berhasil dihapus!'); window.location='peminjaman.php';</script>";
    } else {
        echo "<script>alert('Gagal! Data pengembalian belum Lunas tidak dapat dihapus.'); window.location='peminjaman.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Peminjaman & Pengembalian - Admin</title>
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

    <div class="container mt-4 mb-5">

        <!-- ==================== TABEL CRUD PEMINJAMAN ==================== -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold text-secondary"><i class="fas fa-hand-holding"></i> Data Peminjaman Aktif</h3>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPinjam"><i class="fas fa-plus"></i> Tambah Peminjaman</button>
        </div>

        <div class="card shadow-sm border-0 mb-5">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Nama Peminjam</th>
                                <th>Nama Alat (Kategori)</th>
                                <th>Tgl Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Jumlah</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $q_peminjaman = mysqli_query($koneksi, "
                                SELECT p.*, peminjam.nama_lengkap, alat.nama_alat, kategori.nama_kategori 
                                FROM peminjaman p
                                JOIN peminjam ON p.id_user = peminjam.id_peminjam
                                JOIN alat ON p.id_alat = alat.id_alat
                                LEFT JOIN kategori ON alat.id_kategori = kategori.id_kategori
                                WHERE p.status != 'selesai'
                                ORDER BY p.id_peminjaman DESC
                            ");

                            while ($row = mysqli_fetch_assoc($q_peminjaman)) :
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?= htmlspecialchars($row['nama_alat']); ?> <span class="badge bg-info text-dark"><?= htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori'); ?></span></td>
                                <td class="text-center"><?= $row['tgl_pinjam']; ?></td>
                                <td class="text-center"><?= $row['tgl_jatuh_tempo']; ?></td>
                                <td class="text-center"><?= $row['jumlah']; ?></td>
                                <td class="text-center">
                                    <button class="btn btn-success btn-sm me-1" data-bs-toggle="modal" data-bs-target="#modalKembali<?= $row['id_peminjaman']; ?>"><i class="fas fa-undo"></i> Pengembalian</button>
                                    <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#modalEditPinjam<?= $row['id_peminjaman']; ?>"><i class="fas fa-edit"></i> Edit</button>
                                    <a href="peminjaman.php?hapus_peminjaman=<?= $row['id_peminjaman']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus peminjaman ini? Stok akan dikembalikan.');"><i class="fas fa-trash"></i> Hapus</a>
                                </td>
                            </tr>

                            <!-- Modal Edit Peminjaman -->
                            <div class="modal fade" id="modalEditPinjam<?= $row['id_peminjaman']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-warning">
                                            <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Peminjaman</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id_peminjaman" value="<?= $row['id_peminjaman']; ?>">
                                                <input type="hidden" name="id_alat" value="<?= $row['id_alat']; ?>">
                                                <input type="hidden" name="jumlah_lama" value="<?= $row['jumlah']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Tanggal Pinjam</label>
                                                    <input type="date" name="tgl_pinjam" class="form-control" value="<?= $row['tgl_pinjam']; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Tanggal Jatuh Tempo</label>
                                                    <input type="date" name="tgl_jatuh_tempo" class="form-control" value="<?= $row['tgl_jatuh_tempo']; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Jumlah Pinjam</label>
                                                    <input type="number" name="jumlah_baru" class="form-control" value="<?= $row['jumlah']; ?>" min="1" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                <button type="submit" name="edit_peminjaman" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Pengembalian Alat -->
                            <div class="modal fade" id="modalKembali<?= $row['id_peminjaman']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title"><i class="fas fa-undo"></i> Proses Pengembalian Alat</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id_peminjaman" value="<?= $row['id_peminjaman']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Peminjam</label>
                                                    <input type="text" class="form-control" value="<?= htmlspecialchars($row['nama_lengkap']); ?>" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Alat yang Dipinjam</label>
                                                    <input type="text" class="form-control" value="<?= htmlspecialchars($row['nama_alat']); ?> (<?= $row['jumlah']; ?> Unit)" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Tanggal Kembali</label>
                                                    <input type="date" name="tgl_kembali" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Kondisi Alat Saat Dikembalikan</label>
                                                    <select name="kondisi_alat" class="form-select" required>
                                                        <option value="Baik">Baik (Denda 0%)</option>
                                                        <option value="Rusak Ringan">Rusak Ringan (+25% Denda)</option>
                                                        <option value="Rusak Berat">Rusak Berat (+50% Denda)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="proses_pengembalian" class="btn btn-success">Proses Kembalikan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ==================== TABEL CRUD PENGEMBALIAN ==================== -->
        <h3 class="fw-bold text-secondary mb-3"><i class="fas fa-file-invoice-dollar"></i> Data Pengembalian & Denda</h3>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Peminjam</th>
                                <th>Alat</th>
                                <th>Tgl Kembali</th>
                                <th>Kondisi</th>
                                <th>Denda Keterlambatan</th>
                                <th>Denda Kerusakan</th>
                                <th>Total Denda</th>
                                <th>Status Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no_k = 1;
                            $q_pengembalian = mysqli_query($koneksi, "
                                SELECT k.*, p.jumlah, peminjam.nama_lengkap, alat.nama_alat 
                                FROM pengembalian k
                                JOIN peminjaman p ON k.id_peminjaman = p.id_peminjaman
                                JOIN peminjam ON p.id_user = peminjam.id_peminjam
                                JOIN alat ON p.id_alat = alat.id_alat
                                ORDER BY k.id_pengembalian DESC
                            ");

                            while ($row_k = mysqli_fetch_assoc($q_pengembalian)) :
                            ?>
                            <tr>
                                <td class="text-center"><?= $no_k++; ?></td>
                                <td><?= htmlspecialchars($row_k['nama_lengkap']); ?></td>
                                <td><?= htmlspecialchars($row_k['nama_alat']); ?> (<?= $row_k['jumlah']; ?>)</td>
                                <td class="text-center"><?= $row_k['tgl_kembali']; ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $row_k['kondisi_alat'] == 'Baik' ? 'bg-success' : ($row_k['kondisi_alat'] == 'Rusak Ringan' ? 'bg-warning text-dark' : 'bg-danger'); ?>">
                                        <?= $row_k['kondisi_alat']; ?>
                                    </span>
                                </td>
                                <td>Rp <?= number_format($row_k['denda_keterlambatan'], 0, ',', '.'); ?></td>
                                <td>Rp <?= number_format($row_k['denda_kerusakan'], 0, ',', '.'); ?></td>
                                <td class="fw-bold">Rp <?= number_format($row_k['total_denda'], 0, ',', '.'); ?></td>
                                <td class="text-center">
                                    <?php if ($row_k['status_pembayaran'] == 'Lunas') : ?>
                                        <span class="badge bg-success">Lunas</span>
                                    <?php else : ?>
                                        <span class="badge bg-danger">Belum Lunas</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <!-- Toggle Status Pembayaran -->
                                    <?php if ($row_k['status_pembayaran'] == 'Belum Lunas') : ?>
                                        <a href="peminjaman.php?bayar_id=<?= $row_k['id_pengembalian']; ?>&status=Lunas" class="btn btn-outline-success btn-sm me-1" title="Tandai Lunas"><i class="fas fa-check"></i> Bayar</a>
                                    <?php else : ?>
                                        <a href="peminjaman.php?bayar_id=<?= $row_k['id_pengembalian']; ?>&status=Belum Lunas" class="btn btn-outline-warning btn-sm me-1" title="Batalkan Lunas"><i class="fas fa-times"></i> Batal Lunas</a>
                                    <?php endif; ?>

                                    <!-- Tombol Hapus (Disabled jika Belum Lunas) -->
                                    <?php if ($row_k['status_pembayaran'] == 'Lunas') : ?>
                                        <a href="peminjaman.php?hapus_pengembalian=<?= $row_k['id_pengembalian']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data pengembalian ini?');"><i class="fas fa-trash"></i> Hapus</a>
                                    <?php else : ?>
                                        <button class="btn btn-secondary btn-sm" disabled title="Pembayaran harus Lunas untuk menghapus"><i class="fas fa-trash"></i> Hapus</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Tambah Peminjaman -->
    <div class="modal fade" id="modalTambahPinjam" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Peminjaman Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pilih Peminjam (User)</label>
                            <select name="id_user" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Peminjam --</option>
                                <?php
                                $q_user = mysqli_query($koneksi, "SELECT * FROM peminjam");
                                while ($u = mysqli_fetch_assoc($q_user)) {
                                    echo "<option value='{$u['id_peminjam']}'>{$u['nama_lengkap']} ({$u['username']})</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pilih Alat & Kategori</label>
                            <select name="id_alat" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Alat --</option>
                                <?php
                                $q_alat = mysqli_query($koneksi, "
                                    SELECT alat.*, kategori.nama_kategori 
                                    FROM alat 
                                    LEFT JOIN kategori ON alat.id_kategori = kategori.id_kategori 
                                    WHERE stok > 0
                                ");
                                while ($a = mysqli_fetch_assoc($q_alat)) {
                                    $kat = $a['nama_kategori'] ?? 'Tanpa Kategori';
                                    echo "<option value='{$a['id_alat']}'>{$a['nama_alat']} [Kategori: {$kat}] - (Stok: {$a['stok']})</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Pinjam</label>
                                <input type="date" name="tgl_pinjam" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Jatuh Tempo</label>
                                <input type="date" name="tgl_jatuh_tempo" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Pinjam</label>
                            <input type="number" name="jumlah" class="form-control" min="1" value="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" name="tambah_peminjaman" class="btn btn-primary">Simpan Peminjaman</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>