<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_peminjaman";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}

// Fungsi mencatat perubahan stok barang
function catatLogStok($koneksi, $id_alat, $jenis_transaksi, $jumlah, $keterangan) {
    $id_alat = (int)$id_alat;
    $jumlah = (int)$jumlah;
    $keterangan = mysqli_real_escape_string($koneksi, $keterangan);
    
    $query = "INSERT INTO log_stok (id_alat, jenis_transaksi, jumlah, keterangan) 
              VALUES ('$id_alat', '$jenis_transaksi', '$jumlah', '$keterangan')";
    mysqli_query($koneksi, $query);
}

// Fungsi mencatat aktivitas pengguna
function catatAktifitas($koneksi, $role, $nama_user, $deskripsi) {
    $role = mysqli_real_escape_string($koneksi, $role);
    $nama_user = mysqli_real_escape_string($koneksi, $nama_user);
    $deskripsi = mysqli_real_escape_string($koneksi, $deskripsi);
    
    $query = "INSERT INTO aktifitas (role, nama_user, deskripsi) 
              VALUES ('$role', '$nama_user', '$deskripsi')";
    mysqli_query($koneksi, $query);
}