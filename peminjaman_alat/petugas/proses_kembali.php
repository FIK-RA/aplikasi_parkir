<?php
session_set_cookie_params(0);
session_start();

// Cek akses Petugas atau Admin
if (!isset($_SESSION['id_petugas']) && !isset($_SESSION['id_admin'])) {
    header("Location: ../login-admin.php?pesan=Akses Ditolak!");
    exit;
}

require_once '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['proses_pengembalian']) || isset($_POST['id_peminjaman']))) {
    $id_peminjaman = mysqli_real_escape_string($koneksi, $_POST['id_peminjaman']);
    $tgl_kembali   = mysqli_real_escape_string($koneksi, $_POST['tgl_kembali']);
    $kondisi_alat  = mysqli_real_escape_string($koneksi, $_POST['kondisi_alat']);

    // Ambil data peminjaman
    $q_pinjam = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_peminjaman='$id_peminjaman'");
    $d_pinjam = mysqli_fetch_assoc($q_pinjam);

    if ($d_pinjam) {
        $id_alat = $d_pinjam['id_alat'];
        $jumlah  = (int)$d_pinjam['jumlah'];
        $tgl_jt  = new DateTime($d_pinjam['tgl_jatuh_tempo']);
        $tgl_km  = new DateTime($tgl_kembali);

        // Hitung keterlambatan hari
        $hari_terlambat = 0;
        if ($tgl_km > $tgl_jt) {
            $diff = $tgl_jt->diff($tgl_km);
            $hari_terlambat = $diff->days;
        }

        // Perhitungan Denda Keterlambatan
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

        // Simpan ke tabel pengembalian
        $q_kembali = "INSERT INTO pengembalian (id_peminjaman, tgl_kembali, kondisi_alat, denda_keterlambatan, denda_kerusakan, total_denda, status_pembayaran) 
                      VALUES ('$id_peminjaman', '$tgl_kembali', '$kondisi_alat', '$denda_keterlambatan', '$denda_kerusakan', '$total_denda', 'Belum Lunas')";

        if (mysqli_query($koneksi, $q_kembali)) {
            // 1. Kembalikan stok alat
            mysqli_query($koneksi, "UPDATE alat SET stok = stok + $jumlah WHERE id_alat='$id_alat'");
            
            // 2. Ubah status peminjaman jadi selesai
            mysqli_query($koneksi, "UPDATE peminjaman SET status='selesai' WHERE id_peminjaman='$id_peminjaman'");

            // 3. Catat Log Stok Masuk
            $ket_log = "Pengembalian alat dari transaksi ID: $id_peminjaman (Oleh Petugas)";
            mysqli_query($koneksi, "INSERT INTO log_stok (id_alat, jenis_transaksi, jumlah, keterangan) VALUES ('$id_alat', 'masuk', '$jumlah', '$ket_log')");

            // 4. Catat Log Aktifitas
            $nama_user = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Petugas';
            $desk_act  = "Memproses pengembalian alat ID $id_alat (Kondisi: $kondisi_alat)";
            mysqli_query($koneksi, "INSERT INTO aktifitas (role, nama_user, deskripsi) VALUES ('Petugas', '$nama_user', '$desk_act')");

            echo "<script>alert('Pengembalian berhasil diproses!'); window.location='pantau_kembali.php';</script>";
        } else {
            echo "<script>alert('Gagal memproses pengembalian!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Data peminjaman tidak ditemukan!'); window.history.back();</script>";
    }
} else {
    header("Location: dashboard.php");
    exit;
}
?>