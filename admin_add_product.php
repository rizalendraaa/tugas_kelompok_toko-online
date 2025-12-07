<?php
// admin_add_product.php
session_start();
require 'koneksi.php';
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php"); exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_produk'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga = (int) ($_POST['harga'] ?? 0);
    $gambar = trim($_POST['gambar'] ?? '');

    if ($nama === '') $errors[] = "Nama produk wajib diisi.";
    if ($harga <= 0) $errors[] = "Harga harus lebih dari 0.";

    if (empty($errors)) {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO produk (nama_produk, deskripsi, harga, gambar) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssis", $nama, $deskripsi, $harga, $gambar);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($ok) {
            header("Location: page_admin.php");
            exit;
        } else {
            $errors[] = "Gagal menyimpan ke database.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Tambah Produk</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <div class="navbar">
      <div class="brand">Admin - Tambah</div>
      <div class="nav-right">
        <a class="btn" href="page_admin.php">Kembali</a>
      </div>
    </div>

    <div class="form-box" style="margin-top:18px">
      <h2>Tambah Produk Baru</h2>

      <?php if (!empty($errors)): ?>
        <div style="background:#fee;padding:10px;border-radius:8px;margin-bottom:12px;color:#900">
          <?php foreach ($errors as $e) echo "<div>" . htmlspecialchars($e) . "</div>"; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label>Nama Produk</label>
          <input class="form-input" type="text" name="nama_produk" value="<?= htmlspecialchars($_POST['nama_produk'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label>Deskripsi</label>
          <textarea class="form-input" name="deskripsi" rows="4"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label>Harga (angka)</label>
          <input class="form-input" type="number" name="harga" min="0" value="<?= htmlspecialchars($_POST['harga'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label>URL Gambar (opsional)</label>
          <input class="form-input" type="text" name="gambar" value="<?= htmlspecialchars($_POST['gambar'] ?? '') ?>">
        </div>

        <button class="btn btn-add" type="submit">Simpan Produk</button>
      </form>
    </div>
  </div>
</body>
</html>
