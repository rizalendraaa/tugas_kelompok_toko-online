<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
	header('Location: login.php');
	exit;
}

$id_user = (int)$_SESSION['id_user'];

// ambil item di keranjang beserta stok produk
$stmt = mysqli_prepare($koneksi, "SELECT k.id_keranjang, k.id_produk, k.qty, p.stok, p.nama_produk FROM keranjang k JOIN produk p ON k.id_produk = p.id WHERE k.id_user = ?");
mysqli_stmt_bind_param($stmt, 'i', $id_user);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$items = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

if (empty($items)) {
	echo "Keranjang kosong. Tidak ada yang dicekout.";
	exit;
}

// mulai transaksi supaya update stok + hapus keranjang atomik
mysqli_begin_transaction($koneksi);
try {
	// cek stok untuk setiap item
	foreach ($items as $it) {
		$id_produk = (int)$it['id_produk'];
		$qty = (int)$it['qty'];
		$stok = (int)$it['stok'];
		$nama = $it['nama_produk'];

		if ($qty > $stok) {
			throw new Exception("Stok tidak cukup untuk produk: $nama (tersedia: $stok, dipesan: $qty)");
		}

		// kurangi stok
		$stmtUp = mysqli_prepare($koneksi, "UPDATE produk SET stok = stok - ? WHERE id = ?");
		mysqli_stmt_bind_param($stmtUp, 'ii', $qty, $id_produk);
		mysqli_stmt_execute($stmtUp);
		mysqli_stmt_close($stmtUp);
	}

	// hapus keranjang user
	$stmtDel = mysqli_prepare($koneksi, "DELETE FROM keranjang WHERE id_user = ?");
	mysqli_stmt_bind_param($stmtDel, 'i', $id_user);
	mysqli_stmt_execute($stmtDel);
	mysqli_stmt_close($stmtDel);

	mysqli_commit($koneksi);

	echo "Checkout berhasil! Terima kasih sudah belanja.";

} catch (Exception $e) {
	mysqli_rollback($koneksi);
	// tampilkan pesan error yang ramah
	echo 'Checkout gagal: ' . htmlspecialchars($e->getMessage());
}
