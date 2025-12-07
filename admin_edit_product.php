<?php
// admin_edit_product.php
session_start();
require 'koneksi.php';
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php"); exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: page_admin.php"); exit; }

// ambil produk
$stmt = mysqli_prepare($koneksi, "SELECT id, nama_produk, deskripsi, harga, gambar FROM produk WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$product) { header("Location: page_admin.php"); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_produk'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga = (int) ($_POST['harga'] ?? 0);
    $gambar = trim($_POST['gambar'] ?? '');

    if ($nama === '') $errors[] = "Nama produk wajib diisi.";
    if ($harga <= 0) $errors[] = "Harga harus lebih dari 0.";

    if (empty($errors)) {
        $stmt = mysqli_prepare($koneksi, "UPDATE produk SET nama_produk = ?, deskripsi = ?, harga = ?, gambar = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssisi", $nama, $deskripsi, $harga, $gambar, $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($ok) {
            header("Location: page_admin.php");
            exit;
        } else {
            $errors[] = "Gagal update database.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit Produk</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <div class="navbar">
      <div class="brand">Admin - Edit</div>
      <div class="nav-right">
        <a class="btn" href="page_admin.php">Kembali</a>
      </div>
    </div>

    <div class="form-box" style="margin-top:18px">
      <h2>Edit Produk</h2>

      <?php if (!empty($errors)): ?>
        <div style="background:#fee;padding:10px;border-radius:8px;margin-bottom:12px;color:#900">
          <?php foreach ($errors as $e) echo "<div>" . htmlspecialchars($e) . "</div>"; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label>Nama Produk</label>
          <input class="form-input" type="text" name="nama_produk" value="<?= htmlspecialchars($_POST['nama_produk'] ?? $product['nama_produk']) ?>" required>
        </div>

        <div class="form-group">
          <label>Deskripsi</label>
          <textarea class="form-input" name="deskripsi" rows="4"><?= htmlspecialchars($_POST['deskripsi'] ?? $product['deskripsi']) ?></textarea>
        </div>

        <div class="form-group">
          <label>Harga (angka)</label>
          <input class="form-input" type="number" name="harga" min="0" value="<?= htmlspecialchars($_POST['harga'] ?? $product['harga']) ?>" required>
        </div>

        <div class="form-group">
          <label>URL Gambar (opsional)</label>
          <input class="form-input" type="text" name="gambar" value="<?= htmlspecialchars($_POST['gambar'] ?? $product['gambar']) ?>">
        </div>

        <button class="btn btn-add" type="submit">Update Produk</button>
      </form>
    </div>
  </div>
</body>
</html>
