<?php
session_start();
require __DIR__ . '/koneksi.php';

header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['id_user'])) {
    echo json_encode(["status" => "error", "msg" => "Harus login dulu"]);
    exit;
}

$id_user   = intval($_SESSION['id_user']);
$id_produk = intval($_POST['id_produk'] ?? 0);
$qty       = intval($_POST['qty'] ?? 1);

// Validasi
if ($id_produk <= 0 || $qty <= 0) {
    echo json_encode(["status" => "error", "msg" => "Data tidak valid"]);
    exit;
}

// Cek apakah produk sudah ada
$qCheck = mysqli_query($koneksi,
    "SELECT qty FROM keranjang WHERE id_user=$id_user AND id_produk=$id_produk"
);

if (mysqli_num_rows($qCheck) > 0) {
    mysqli_query($koneksi,
        "UPDATE keranjang 
         SET qty = qty + $qty
         WHERE id_user=$id_user AND id_produk=$id_produk"
    );
} else {
    mysqli_query($koneksi,
        "INSERT INTO keranjang (id_user, id_produk, qty)
         VALUES ($id_user, $id_produk, $qty)"
    );
}

// Hitung total qty
$countRes = mysqli_query($koneksi,
    "SELECT SUM(qty) AS total FROM keranjang WHERE id_user=$id_user"
);

$count = mysqli_fetch_assoc($countRes)['total'] ?? 0;

echo json_encode([
    "status" => "success",
    "msg" => "Produk berhasil ditambahkan!",
    "cartCount" => $count
]);
