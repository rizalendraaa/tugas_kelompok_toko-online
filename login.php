<?php
// tampilkan error agar tidak blank
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
require 'koneksi.php';

$error = "";

// --- tombol login ditekan ---
if (isset($_POST['btn_login'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // cek username
    $stmt = mysqli_prepare($koneksi, 
        "SELECT id_user, username, password, role FROM user WHERE username = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $res  = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($res);

    if ($user && password_verify($password, $user['password'])) {

        // simpan session
        $_SESSION['id_user']  = $user['id_user'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        // redirect sesuai role
        if ($user['role'] === "admin") {
            header("Location: page_admin.php");
            exit;
        } 
        else if ($user['role'] === "pembeli") {
            header("Location: produk.php");
            exit;
        }
        else {
            header("Location: index.php");
            exit;
        }

    } else {
        $error = "Username atau password salah.";
    }

    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login Toko</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="form-box" style="margin-top:80px;">
    <h2>Login User</h2>

    <?php if (!empty($error)): ?>
        <div style="background:#fee;padding:10px;border-radius:8px;color:#900;margin-bottom:12px">
            <?= htmlspecialchars($error) ?>
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

        <button class="btn btn-add" name="btn_login" type="submit">MASUK</button>
    </form>

    <p style="margin-top:12px;text-align:center;">
        Belum punya akun pembeli? <a href="register.php" style="color:#0f61c9">Daftar Pembeli</a><br>
        Admin? <a href="register_admin.php" style="color:#0f61c9">Daftar Admin</a>
    </p>

</div>

</body>
</html>
