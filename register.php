<?php
session_start();
require 'koneksi.php';

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm']);

    if ($username === "" || $password === "" || $confirm === "") {
        $errors[] = "Semua field wajib diisi.";
    }

    if ($password !== $confirm) {
        $errors[] = "Password dan konfirmasi tidak sama.";
    }

    // Cek username sudah ada atau belum
    $stmt = mysqli_prepare($koneksi, "SELECT id_user FROM user WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $errors[] = "Username sudah digunakan!";
    }
    mysqli_stmt_close($stmt);

    // Jika tidak ada error → simpan
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $role = "pembeli";

        $stmt = mysqli_prepare($koneksi, 
            "INSERT INTO user (username, password, role) VALUES (?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "sss", $username, $hashed, $role);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Akun berhasil dibuat! Silakan login.";
        } else {
            $errors[] = "Gagal menyimpan ke database.";
        }

        mysqli_stmt_close($stmt);
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Registrasi - TokoKu</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="navbar">
        <div class="brand">TokoKu</div>
        <div class="nav-right">
            <button class="btn" onclick="location.href='login.php'">Login</button>
        </div>
    </div>

    <div class="form-box" style="margin-top:28px">
        <h2>Registrasi Akun</h2>

        <?php if (!empty($errors)): ?>
            <div style="background:#ffd5d5;padding:10px;border-radius:8px;color:#900;margin-bottom:12px">
                <?php foreach($errors as $e){ echo "<div>".htmlspecialchars($e)."</div>"; } ?>
            </div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div style="background:#d5ffe0;padding:10px;border-radius:8px;color:#006622;margin-bottom:12px">
                <?= $success ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>Username</label>
                <input class="form-input" type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input class="form-input" type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input class="form-input" type="password" name="confirm" required>
            </div>

            <button class="btn btn-add" type="submit">Daftar Sekarang</button>
        </form>

        <p style="margin-top:12px;text-align:center;">
            Sudah punya akun? <a href="login.php" style="color:#0f61c9">Login</a>
        </p>
    </div>
</div>

</body>
</html>
