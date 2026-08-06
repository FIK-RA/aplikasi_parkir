<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Alat Digital</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="https://unpkg.com/feather-icons"></script>
</head>

<body class="bg-light">
    <div class="vh-100 row justify-content-center align-items-center">
        <div class="col-md-2">
            <div class="card p-3 text-center">
                <h1><i data-feather="user"></i></h1>
                <h5>HALAMAN LOGIN ADMIN</h5>
                <a href="login-admin.php" class="btn btn-primary">Login <i data-feather="log-in"></i></a>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3 text-center">
                <h1><i data-feather="users"></i></h1>
                <h5>HALAMAN LOGIN PETUGAS</h5>
                <a href="login-petugas.php" class="btn btn-primary">Login <i data-feather="log-in"></i></a>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3 text-center">
                <h1><i data-feather="users"></i></h1>
                <h5>HALAMAN LOGIN PEMINJAM</h5>
                <a href="login-anggota.php" class="btn btn-primary">Login <i data-feather="log-in"></i></a>
            </div>
        </div>
    </div>
</body>

</html>
<script>
    feather.replace();
</script>