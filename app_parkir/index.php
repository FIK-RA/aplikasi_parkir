<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'config/koneksi.php';

$pesan_error = "";

// 1. Redirect jika session sudah ada
if (isset($_SESSION['id_user'])) {
    $role = $_SESSION['role'];
    if ($role == 'admin') {
        header("Location: admin/php-admin/dashboard.php");
    } elseif ($role == 'petugas') {
        header("Location: petugas/transaksi.php");
    } elseif ($role == 'owner') {
        header("Location: owner/rekap.php");
    }
    exit();
}

// 2. Proses Login
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password']; 

    if (!empty($username) && !empty($password)) {
        $query = "SELECT * FROM tb_user WHERE username = '$username' LIMIT 1";
        $result = mysqli_query($koneksi, $query);

        if (mysqli_num_rows($result) === 1) {
            $data_user = mysqli_fetch_assoc($result);

            if ($data_user['status_aktif'] == 1) {
                
                if ($password === $data_user['password'] || password_verify($password, $data_user['password'])) {
                    
                    $_SESSION['id_user']      = $data_user['id_user'];
                    $_SESSION['nama_lengkap'] = $data_user['nama_lengkap'];
                    $_SESSION['username']     = $data_user['username'];
                    $_SESSION['role']         = $data_user['role'];

                    $id_u = $data_user['id_user'];
                    $log_query = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ('$id_u', 'User berhasil login')";
                    mysqli_query($koneksi, $log_query);

                    // Penentuan Jalur Redirect berdasarkan Struktur Folder Baru
                    if ($data_user['role'] === 'admin') {
                        $redirect_path = 'admin/php-admin/dashboard.php';
                    } elseif ($data_user['role'] === 'petugas') {
                        $redirect_path = 'petugas/transaksi.php';
                    } elseif ($data_user['role'] === 'owner') {
                        $redirect_path = 'owner/rekap.php';
                    } else {
                        $redirect_path = 'index.php';
                    }

                    echo "<script>
                            sessionStorage.setItem('is_logged_in', 'true');
                            window.location.href = '$redirect_path';
                          </script>";
                    exit();

                } else {
                    $pesan_error = "Password yang kamu masukkan salah!";
                }

            } else {
                $pesan_error = "Akun kamu dinonaktifkan! Hubungi Admin.";
            }

        } else {
            $pesan_error = "Username tidak ditemukan!";
        }

    } else {
        $pesan_error = "Username dan Password wajib diisi!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Manajemen Parkir</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <h2>Sistem Parkir</h2>
            <p>Silakan login untuk mengakses sistem</p>
        </div>

        <?php if (!empty($pesan_error)): ?>
            <div class="alert-error">
                <?= htmlspecialchars($pesan_error); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username" required autocomplete="off">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                    <button type="button" id="togglePassword" class="toggle-password" title="Tampilkan/Sembunyikan Password">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" name="login" class="btn-login">Login Masuk</button>
        </form>

        <div class="login-footer">
            &copy; <?= date('Y'); ?> E-Parkir System. All rights reserved.
        </div>
    </div>

    <script src="index.js"></script>
    <script>
        sessionStorage.removeItem('is_logged_in');
    </script>
</body>
</html>