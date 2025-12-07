<?php
// remove_cart.php
session_start();
require 'koneksi.php';
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit; }

$id_keranjang = isset($_POST['id_keranjang']) ? (int)$_POST['id_keranjang'] : 0;
$stmt = mysqli_prepare($koneksi, "DELETE FROM keranjang WHERE id_keranjang = ?");
mysqli_stmt_bind_param($stmt, "i", $id_keranjang);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: keranjang.php");
exit;
