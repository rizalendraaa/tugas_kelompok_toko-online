<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pemilik') {
    die("Anda bukan pemilik!");
}
?>
<h1>SELAMAT DATANG PEMILIK</h1>
<p>Anda dapat mengontrol laporan, stok, admin dll.</p>

<a href="page_admin.php">Masuk Panel Admin</a><br>
<a href="logout.php">Logout</a>
