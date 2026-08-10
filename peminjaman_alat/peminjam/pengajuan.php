<?php
session_set_cookie_params(0);
session_start();

if (!isset($_SESSION['id_peminjam'])) {
    header("Location: ../login-admin.php?pesan=Akses Ditolak!");
    exit;
}

require_once '../config/koneksi.php';

// Proses Simpan Pengajuan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user         = $_SESSION['id_peminjam'];
    $id_alat         = mysqli_real_escape_string($koneksi, $_POST['id_alat']);
    $jumlah          = (int)$_POST['jumlah'];
    $tgl_pinjam      = mysqli_real_escape_string($koneksi, $_POST['tgl_pinjam']);
    $tgl_jatuh_tempo = mysqli_real_escape_string($koneksi, $_POST['tgl_jatuh_tempo']);

    // Cek Stok Alat
    $cek_stok = mysqli_query($koneksi, "SELECT stok FROM alat WHERE id_alat='$id_alat'");
    $data_alat = mysqli_fetch_assoc($cek_stok);

    if ($data_alat['stok'] < $jumlah) {
        echo "<script>alert('Stok alat tidak mencukupi!'); window.location='pengajuan.php';</script>";
    } else {
        $query = "INSERT INTO peminjaman (id_user, id_alat, tgl_pinjam, tgl_jatuh_tempo, jumlah, status) 
                  VALUES ('$id_user', '$id_alat', '$tgl_pinjam', '$tgl_jatuh_tempo', '$jumlah', 'pending')";
        
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Pengajuan peminjaman berhasil dikirim! Menunggu persetujuan petugas.'); window.location='dashboard.php';</script>";
        } else {
            echo "<script>alert('Gagal mengajukan peminjaman!'); window.location='pengajuan.php';</script>";
        }
    }
}

$selected_id = $_GET['id_alat'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Peminjaman - Peminjam</title>
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
                    <li class="nav-item"><a class="nav-link active" href="pengajuan.php"><i class="fas fa-plus-circle"></i> Ajukan Pinjaman</a></li>
                    <li class="nav-item"><a class="nav-link" href="pengembalian.php"><i class="fas fa-undo"></i> Riwayat & Pengembalian</a></li>
                </ul>
                <span class="text-white fw-bold me-3">Halo, <?= htmlspecialchars($_SESSION['username_peminjam']); ?>!</span>
                <a href="logout.php" class="btn btn-danger btn-sm text-white"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-info text-white py-3">
                        <h5 class="fw-bold mb-0"><i class="fas fa-paper-plane me-2"></i>Form Pengajuan Peminjaman</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Pilih Alat</label>
                                <select name="id_alat" class="form-select" required>
                                    <option value="">-- Pilih Alat --</option>
                                    <?php
                                    $q_alat = mysqli_query($koneksi, "SELECT * FROM alat WHERE stok > 0 ORDER BY nama_alat ASC");
                                    while ($a = mysqli_fetch_assoc($q_alat)):
                                    ?>
                                        <option value="<?= $a['id_alat']; ?>" <?= ($a['id_alat'] == $selected_id) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($a['nama_alat']); ?> (Stok: <?= $a['stok']; ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Jumlah Unit</label>
                                <input type="number" name="jumlah" class="form-control" min="1" value="1" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Tanggal Pinjam</label>
                                    <input type="date" name="tgl_pinjam" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Tanggal Jatuh Tempo</label>
                                    <input type="date" name="tgl_jatuh_tempo" class="form-control" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-info text-white fw-bold w-100 mt-2">
                                <i class="fas fa-paper-plane me-1"></i> Kirim Pengajuan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>