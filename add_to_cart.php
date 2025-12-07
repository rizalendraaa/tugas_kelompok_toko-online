<?php
// add_to_cart.php
session_start();
require 'koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    // jika request via JS, kirim JSON, kalau tidak redirect ke login
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        echo json_encode(['status' => 'error', 'msg' => 'Silakan login terlebih dahulu.']);
        exit;
    }
    header('Location: login.php');
    exit;
}

$id_user = (int)$_SESSION['id_user'];
$id_produk = isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0;
$qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

if ($id_produk <= 0 || $qty <= 0) {
    echo json_encode(['status' => 'error', 'msg' => 'Data produk tidak valid.']);
    exit;
}

// pastikan produk ada dan ambil stok
$stmt = mysqli_prepare($koneksi, 'SELECT stok FROM produk WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id_produk);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$prod = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$prod) {
    echo json_encode(['status' => 'error', 'msg' => 'Produk tidak ditemukan.']);
    exit;
}

$stok = (int)$prod['stok'];
if ($qty > $stok) {
    echo json_encode(['status' => 'error', 'msg' => 'Qty melebihi stok.']);
    exit;
}

// jika sudah ada di keranjang, update qty, jika belum insert
$stmt = mysqli_prepare($koneksi, 'SELECT id_keranjang, qty FROM keranjang WHERE id_user = ? AND id_produk = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'ii', $id_user, $id_produk);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if ($row) {
    $newQty = (int)$row['qty'] + $qty;
    if ($newQty > $stok) $newQty = $stok;
    $stmt = mysqli_prepare($koneksi, 'UPDATE keranjang SET qty = ? WHERE id_keranjang = ?');
    mysqli_stmt_bind_param($stmt, 'ii', $newQty, $row['id_keranjang']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} else {
    $stmt = mysqli_prepare($koneksi, 'INSERT INTO keranjang (id_user, id_produk, qty) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'iii', $id_user, $id_produk, $qty);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// hitung kembali jumlah item di keranjang
$res = mysqli_query($koneksi, "SELECT SUM(qty) AS total FROM keranjang WHERE id_user=$id_user");
$cartCount = (int)mysqli_fetch_assoc($res)['total'];

echo json_encode(['status' => 'success', 'msg' => 'Berhasil menambahkan ke keranjang', 'cartCount' => $cartCount]);
exit;

?>
