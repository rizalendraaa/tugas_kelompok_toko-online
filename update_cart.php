<?php
// update_cart.php
session_start();
require 'koneksi.php';
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit; }

$id_keranjang = isset($_POST['id_keranjang']) ? (int)$_POST['id_keranjang'] : 0;
$qty = isset($_POST['qty']) ? max(1, (int)$_POST['qty']) : 1;

// ambil record keranjang dan produk
$stmt = mysqli_prepare($koneksi, "SELECT k.id_keranjang, k.id_produk, p.stok FROM keranjang k JOIN produk p ON k.id_produk = p.id WHERE k.id_keranjang = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id_keranjang);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$row) {
    header("Location: keranjang.php");
    exit;
}

if ($qty > (int)$row['stok']) {
    $_SESSION['flash_error'] = "Stok tidak cukup.";
    header("Location: keranjang.php");
    exit;
}

$stmt = mysqli_prepare($koneksi, "UPDATE keranjang SET qty = ? WHERE id_keranjang = ?");
mysqli_stmt_bind_param($stmt, "ii", $qty, $id_keranjang);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: keranjang.php");
exit;
