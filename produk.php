<?php
session_start();
require __DIR__ . '/koneksi.php'; // FIX path koneksi.php

// Cek login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

// Ambil semua produk
$query = "SELECT id, nama_produk, deskripsi, harga, gambar 
          FROM produk 
          ORDER BY id ASC";

$res = mysqli_query($koneksi, $query);
$products = mysqli_fetch_all($res, MYSQLI_ASSOC);

// Hitung total item keranjang (untuk cart-count awal)
$id_user = $_SESSION['id_user'];
$countRes = mysqli_query($koneksi,
    "SELECT SUM(qty) AS total FROM keranjang WHERE id_user=$id_user"
);
$cartCount = mysqli_fetch_assoc($countRes)['total'] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Produk</title>

    <!-- FIX path CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <!-- NAVBAR -->
    <div class="navbar">
        <div class="brand" onclick="location.href='produk.php'">TokoKu</div>

        <div class="nav-right">

            <!-- Keranjang -->
            <a class="cart-icon" href="keranjang.php" style="display:inline-block">
                <img src="https://cdn-icons-png.flaticon.com/512/1170/1170678.png" alt="cart">
                <div class="cart-count"><?= $cartCount ?></div>
            </a>

            <button class="btn btn-danger" onclick="location.href='logout.php'">Logout</button>
        </div>
    </div>

    <h2 style="margin-top:16px">Daftar Produk</h2>

    <div id="productsWrap" class="products-grid">

        <?php foreach ($products as $row): ?>
            <div class="product-card">

                <!-- Gambar Produk -->
                <div class="product-img">
                    <?php 
                    $gambarPath = __DIR__ . "/" . $row['gambar']; 
                    ?>
                    <?php if (!empty($row['gambar']) && file_exists($gambarPath)): ?>
                        <img src="<?= htmlspecialchars($row['gambar']) ?>"
                             alt="<?= htmlspecialchars($row['nama_produk']) ?>"
                             style="width:100%;height:100%;object-fit:cover;border-radius:8px;">
                    <?php else: ?>
                        <div style="font-size:42px;text-align:center;padding-top:20px;color:#666;">
                            <?= htmlspecialchars(substr($row['nama_produk'],0,1)) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Info Produk -->
                <h3 class="product-name"><?= htmlspecialchars($row['nama_produk']) ?></h3>
                <p class="product-price">Rp <?= number_format($row['harga'], 0, ',', '.') ?></p>

                <!-- Form Tambah Ke Keranjang -->
                <form method="POST" action="add_to_cart.php" style="margin-top:8px;">
                    <input type="hidden" name="id_produk" value="<?= (int)$row['id'] ?>">

                    <input type="number" name="qty" value="1" min="1" class="qty-input" style="width:80px;">

                    <button class="btn btn-add" type="submit">Tambah ke Keranjang</button>
                </form>

            </div>
        <?php endforeach; ?>

    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const forms = document.querySelectorAll("form[action='add_to_cart.php']");

    forms.forEach(form => {
        form.addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch("add_to_cart.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    document.querySelector(".cart-count").textContent = data.cartCount;
                    showNotif(data.msg);
                } else {
                    showNotif(data.msg);
                }
            })
            .catch(err => {
                showNotif("Gagal menambahkan ke keranjang");
                console.error(err);
            });
        });
    });
});

function showNotif(message) {
    let notif = document.createElement("div");
    notif.className = "notif";
    notif.innerText = message;

    document.body.appendChild(notif);

    setTimeout(() => {
        notif.classList.add("hide");
        setTimeout(() => notif.remove(), 500);
    }, 1500);
}
</script>


</body>
</html>
