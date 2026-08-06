<?php
session_start();
require_once '../config/koneksi.php';

$id_peminjaman = $_GET['id'];
$aksi          = $_GET['aksi']; // 'setuju' atau 'tolak'

if ($aksi == 'tolak') {
    // Update status peminjaman menjadi "ditolak"
    mysqli_query($koneksi, "UPDATE peminjaman SET status='ditolak' WHERE id_peminjaman='$id_peminjaman'");
} elseif ($aksi == 'setuju') {
    // 1. Ambil detail peminjaman untuk kurangi stok
    $p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_peminjaman='$id_peminjaman'"));
    $id_alat = $p['id_alat'];
    $jumlah  = $p['jumlah'];

    // 2. Update status peminjaman menjadi "disetujui"
    mysqli_query($koneksi, "UPDATE peminjaman SET status='disetujui' WHERE id_peminjaman='$id_peminjaman'");

    // 3. Kurangi stok alat di database
    mysqli_query($koneksi, "UPDATE alat SET stok = stok - $jumlah WHERE id_alat='$id_alat'");
}

header("Location: dashboard.php");
?>