<?php
// histori.php
session_start();
require 'koneksi.php';
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit; }

$id_user = (int)$_SESSION['id_user'];

// ambil transaksi user
$stmt = mysqli_prepare($koneksi, "SELECT * FROM transaksi WHERE id_user = ? ORDER BY tanggal DESC");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$trans = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Histori Pembayaran</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="navbar">
    <div class="brand">TokoKu</div>
    <div class="nav-right">
      <button class="btn" onclick="location.href='produk.php'">Kembali</button>
      <button class="btn btn-danger" onclick="location.href='logout.php'">Logout</button>
    </div>
  </div>

  <div class="container">
    <h2>Histori Pembayaran</h2>
    <div id="historyWrap">
      <?php if (empty($trans)): ?>
        <p>Belum ada histori pembayaran.</p>
      <?php else: ?>
        <?php foreach ($trans as $t): ?>
          <div class="history-card">
            <h3>Transaksi #<?= (int)$t['id_transaksi'] ?></h3>
            <p><b>Tanggal:</b> <?= htmlspecialchars($t['tanggal']) ?></p>
            <p><b>Total:</b> Rp <?= number_format($t['total'],0,',','.') ?></p>
            <p><b>Daftar Barang:</b></p>
            <div>
              <?php
                $stmt = mysqli_prepare($koneksi, "SELECT td.*, p.nama_produk FROM transaksi_detail td JOIN produk p ON td.id_produk = p.id WHERE td.id_transaksi = ?");
                mysqli_stmt_bind_param($stmt, "i", $t['id_transaksi']);
                mysqli_stmt_execute($stmt);
                $res2 = mysqli_stmt_get_result($stmt);
                $details = mysqli_fetch_all($res2, MYSQLI_ASSOC);
                mysqli_stmt_close($stmt);
              ?>
              <?php foreach ($details as $d): ?>
                <div class="item">• <?= htmlspecialchars($d['nama_produk']) ?> (x<?= (int)$d['qty'] ?>) — Rp <?= number_format($d['harga'] * $d['qty'],0,',','.') ?></div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
