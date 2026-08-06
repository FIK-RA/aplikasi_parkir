<?php
session_start();
require_once '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_peminjaman = $_POST['id_peminjaman'];
    $tgl_kembali   = $_POST['tgl_kembali'];
    $kondisi_alat  = $_POST['kondisi_alat']; // 'Baik', 'Rusak', 'Hilang'

    // Ambil data peminjaman
    $q = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_peminjaman='$id_peminjaman'");
    $data = mysqli_fetch_assoc($q);

    $tgl_jatuh_tempo = $data['tgl_jatuh_tempo'];
    $denda_terlambat = 0;
    $denda_kerusakan = 0;

    // [Keputusan 1] Apakah Tanggal Kembali melewati Tanggal Jatuh Tempo?
    if (strtotime($tgl_kembali) > strtotime($tgl_jatuh_tempo)) {
        // Hitung jumlah hari terlambat
        $selisih = strtotime($tgl_kembali) - strtotime($tgl_jatuh_tempo);
        $hari_terlambat = floor($selisih / (60 * 60 * 24));
        
        // Hitung Denda (Misal: Rp 5.000 / hari)
        $denda_terlambat = $hari_terlambat * 5000;
    }

    // [Keputusan 2] Apakah Barang Rusak atau Hilang?
    if ($kondisi_alat == 'Rusak' || $kondisi_alat == 'Hilang') {
        // Tambahkan denda kerusakan (Misal: Rp 50.000)
        $denda_kerusakan = 50000;
    }

    // Total Denda
    $total_denda = $denda_terlambat + $denda_kerusakan;

    // 1. Simpan data Pengembalian ke database
    mysqli_query($koneksi, "INSERT INTO pengembalian (id_peminjaman, tgl_kembali, kondisi_alat, denda_keterlambatan, denda_kerusakan, total_denda) 
                            VALUES ('$id_peminjaman', '$tgl_kembali', '$kondisi_alat', '$denda_terlambat', '$denda_kerusakan', '$total_denda')");

    // 2. Update status Peminjaman menjadi "selesai"
    mysqli_query($koneksi, "UPDATE peminjaman SET status='selesai' WHERE id_peminjaman='$id_peminjaman'");

    // 3. Tambahkan kembali stok alat di database
    $id_alat = $data['id_alat'];
    $jumlah  = $data['jumlah'];
    mysqli_query($koneksi, "UPDATE alat SET stok = stok + $jumlah WHERE id_alat='$id_alat'");

    echo "<script>alert('Pengembalian Berhasil! Total Denda: Rp " . number_format($total_denda, 0, ',', '.') . "'); window.location='dashboard.php';</script>";
}
?>