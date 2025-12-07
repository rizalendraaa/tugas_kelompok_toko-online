<?php
// keranjang.php
session_start();
require 'koneksi.php';
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}
$id_user = (int)$_SESSION['id_user'];

// ambil data keranjang join produk
$stmt = mysqli_prepare($koneksi, "SELECT k.id_keranjang, k.id_produk, k.qty, p.nama_produk, p.harga, p.gambar, p.stok FROM keranjang k LEFT JOIN produk p ON k.id_produk = p.id WHERE k.id_user = ?");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$items = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Keranjang</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <div class="navbar">
      <div class="brand" onclick="location.href='produk.php'">TokoKu</div>
      <div class="nav-right">
        <div class="cart-icon" onclick="location.href='keranjang.php'">
          <img src="https://cdn-icons-png.flaticon.com/512/1170/1170678.png" alt="cart">
          <div class="cart-count"><?= array_sum(array_column($items, 'qty')) ?></div>
        </div>
        <button class="btn btn-danger" onclick="location.href='logout.php'">Logout</button>
      </div>
    </div>

    <h2 style="margin-top:16px">Keranjang Belanja</h2>

    <div id="cartWrap" class="cart-list">
      <?php if (empty($items)): ?>
        <div style="text-align:center;padding:30px;background:#fff;border-radius:12px;">Keranjang kosong</div>
      <?php else: ?>
        <?php foreach ($items as $it): ?>
          <div class="cart-item">
            <div class="mini-img">
              <?php if (!empty($it['gambar'])): ?>
                <img src="<?= htmlspecialchars($it['gambar']) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
              <?php else: ?>
                <?= htmlspecialchars(substr($it['nama_produk'],0,1)) ?>
              <?php endif; ?>
            </div>

            <div class="cart-details">
              <div style="font-weight:800;color:#0f61c9"><?= htmlspecialchars($it['nama_produk']) ?></div>
              <div>Harga: Rp <?= number_format($it['harga'],0,',','.') ?></div>
              <div>Stok: <?= (int)$it['stok'] ?></div>
            </div>

            <div class="cart-actions">
              <form method="POST" action="update_cart.php">
                <input type="hidden" name="id_keranjang" value="<?= (int)$it['id_keranjang'] ?>">
                <input type="number" name="qty" value="<?= (int)$it['qty'] ?>" min="1" max="<?= (int)$it['stok'] ?>" class="qty-input">
                <div style="margin-top:6px">
                  <button class="btn btn-add" type="submit">Update</button>
                </div>
              </form>

              <form method="POST" action="remove_cart.php" style="margin-top:6px">
                <input type="hidden" name="id_keranjang" value="<?= (int)$it['id_keranjang'] ?>">
                <button class="btn btn-danger" type="submit">Hapus</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div id="summaryBox" class="summary-box">
      <?php
        $total = 0;
        foreach ($items as $it) $total += $it['harga'] * $it['qty'];
      ?>
      <div class="summary-row"><div>Subtotal</div><div>Rp <?= number_format($total,0,',','.') ?></div></div>
      <div class="summary-row"><div>Ongkos Kirim</div><div>Rp 0</div></div>
      <div class="summary-row total-amount"><div>Total</div><div>Rp <?= number_format($total,0,',','.') ?></div></div>
      <div style="margin-top:12px">
        <form method="POST" action="checkout.php">
          <button class="btn btn-checkout" type="submit">Checkout</button>
        </form>
      </div>
    </div>

  </div>
</body>
</html>
