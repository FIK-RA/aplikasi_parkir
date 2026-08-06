<?php
session_start();
require_once '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user        = $_SESSION['id_user'];
    $id_alat        = $_POST['id_alat'];
    $jumlah         = $_POST['jumlah'];
    $tgl_pinjam     = $_POST['tgl_pinjam'];
    $tgl_jatuh_tempo= $_POST['tgl_jatuh_tempo'];

    // [Keputusan] Apakah Stok Alat Tersedia?
    $cek_stok = mysqli_query($koneksi, "SELECT stok FROM alat WHERE id_alat='$id_alat'");
    $data_alat = mysqli_fetch_assoc($cek_stok);

    if ($data_alat['stok'] < $jumlah) {
        // Jika TIDAK -> Tampilkan pesan Stok Habis / Tidak Cukup
        echo "<script>alert('Stok alat tidak mencukupi!'); window.location='pinjam.php';</script>";
    } else {
        // Jika YA -> Simpan data peminjaman dengan status "pending"
        $query = "INSERT INTO peminjaman (id_user, id_alat, tgl_pinjam, tgl_jatuh_tempo, jumlah, status) 
                  VALUES ('$id_user', '$id_alat', '$tgl_pinjam', '$tgl_jatuh_tempo', '$jumlah', 'pending')";
        mysqli_query($koneksi, $query);
        echo "<script>alert('Pengajuan peminjaman berhasil, menunggu persetujuan petugas!'); window.location='dashboard.php';</script>";
    }
}
?>