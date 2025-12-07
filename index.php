<?php
session_start();
require "koneksi.php";

// ambil data produk
$produk = mysqli_query($koneksi, "SELECT * FROM produk");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Toko Komputer</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <!-- NAVBAR -->
    <div class="navbar">
        <div class="brand">Toko Komputer</div>

        <div class="nav-right">
            <a href="riwayat.php">
                <img src="img/history.png" class="history-icon">
            </a>

            <a href="keranjang.php" class="cart-icon">
                <img src="img/cart.png">
                <span class="cart-count">
                    <?php 
                    echo isset($_SESSION['cart_total']) ? $_SESSION['cart_total'] : 0;
                    ?>
                </span>
            </a>
        </div>
    </div>

    <!-- GRID PRODUK -->
    <div class="products-grid">

        <?php while ($row = mysqli_fetch_assoc($produk)) { ?>
            <div class="product-card">
                
                <div class="product-img">
                    <?php echo htmlspecialchars($row['nama_produk']); ?>
                </div>

                <div class="product-name">
                    <?php echo htmlspecialchars($row['nama_produk']); ?>
                </div>

                <div class="product-price">
                    Rp <?php echo number_format($row['harga']); ?>
                </div>

                <div class="product-stock">
                    Stok: —
                </div>

                <div class="inline-row">
                    <form action="tambah_keranjang.php" method="POST">
                        <input type="hidden" name="id_produk" value="<?php echo $row['id']; ?>">
                        <input type="number" name="qty" class="qty-input" min="1" value="1">
                        <button class="btn btn-add">Tambah</button>
                    </form>
                </div>

            </div>
        <?php } ?>

    </div>

</div>

</body>
</html>
