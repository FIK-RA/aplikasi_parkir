<?php
session_set_cookie_params(0);
session_start();
if (isset($_SESSION['id_peminjam'])) {
    header("Location: peminjam/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Peminjam Peminjaman Alat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h4>Login Peminjam</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['pesan'])): ?>
                        <div class="alert alert-danger text-center"><?= $_GET['pesan']; ?></div>
                    <?php endif; ?>
                    
                    <form action="proses_login.php" method="POST">
                        <input type="hidden" name="role_login" value="peminjam">
                        <div class="mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>