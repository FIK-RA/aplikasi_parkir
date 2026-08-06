<?php
session_set_cookie_params(0);
session_start();
require_once 'config/koneksi.php';

$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$password = $_POST['password'];
$role_login = $_POST['role_login'];

if ($role_login == 'admin') {
    $cek_admin = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$username' AND password='$password'");
    if (mysqli_num_rows($cek_admin) > 0) {
        $data = mysqli_fetch_assoc($cek_admin);
        // Gunakan session khusus admin
        $_SESSION['id_admin'] = $data['id_admin'];
        $_SESSION['username_admin'] = $data['username'];
        header("Location: admin/dashboard.php");
        exit;
    } else {
        header("Location: login-admin.php?pesan=Username atau Password Admin Salah!");
        exit;
    }

} elseif ($role_login == 'petugas') {
    $cek_petugas = mysqli_query($koneksi, "SELECT * FROM petugas WHERE username='$username' AND password='$password'");
    if (mysqli_num_rows($cek_petugas) > 0) {
        $data = mysqli_fetch_assoc($cek_petugas);
        // Gunakan session khusus petugas
        $_SESSION['id_petugas'] = $data['id_petugas'];
        $_SESSION['username_petugas'] = $data['username'];
        header("Location: petugas/dashboard.php");
        exit;
    } else {
        header("Location: login-petugas.php?pesan=Username atau Password Petugas Salah!");
        exit;
    }

} elseif ($role_login == 'peminjam') {
    $cek_peminjam = mysqli_query($koneksi, "SELECT * FROM peminjam WHERE username='$username' AND password='$password'");
    if (mysqli_num_rows($cek_peminjam) > 0) {
        $data = mysqli_fetch_assoc($cek_peminjam);
        // Gunakan session khusus peminjam
        $_SESSION['id_peminjam'] = $data['id_peminjam'];
        $_SESSION['username_peminjam'] = $data['username'];
        header("Location: peminjam/dashboard.php");
        exit;
    } else {
        header("Location: login-anggota.php?pesan=Username atau Password Peminjam Salah!");
        exit;
    }
}
?>