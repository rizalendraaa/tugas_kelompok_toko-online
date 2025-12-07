<?php
session_start();
require 'koneksi.php';

// ==========================================================
// HANYA ADMIN YANG BOLEH MASUK
// ==========================================================
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// ==========================================================
// FUNCTION UPLOAD GAMBAR FIX
// ==========================================================
function upload_gambar() {
    if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== 0) {
        return "";
    }

    $folder = "uploads/";
    if (!is_dir($folder)) mkdir($folder, 0777, true);

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        return "";
    }

    $newFile = $folder . time() . "_" . uniqid() . "." . $ext;

    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $newFile)) {
        return $newFile;
    }

    return "";
}

// ==========================================================
// HAPUS PRODUK
// ==========================================================
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);

    $q = mysqli_query($koneksi, "SELECT gambar FROM produk WHERE id=$id");
    $img = mysqli_fetch_assoc($q);

    if (!empty($img['gambar']) && file_exists($img['gambar'])) {
        unlink($img['gambar']);
    }

    mysqli_query($koneksi, "DELETE FROM produk WHERE id=$id");
    header("Location: page_admin.php");
    exit;
}

// ==========================================================
// TAMBAH PRODUK
// ==========================================================
if (isset($_POST['tambah_produk'])) {
    $nama  = $_POST['nama'];
    $harga = $_POST['harga'];
    $stok  = $_POST['stok'];
    $desk  = $_POST['deskripsi'];

    $gambar = upload_gambar();

    mysqli_query($koneksi,
        "INSERT INTO produk (nama_produk, harga, stok, deskripsi, gambar)
         VALUES ('$nama', '$harga', '$stok', '$desk', '$gambar')"
    );

    header("Location: page_admin.php");
    exit;
}

// ==========================================================
// UPDATE PRODUK
// ==========================================================
if (isset($_POST['update_produk'])) {

    $id    = $_POST['id'];
    $nama  = $_POST['nama'];
    $harga = $_POST['harga'];
    $stok  = $_POST['stok'];
    $desk  = $_POST['deskripsi'];

    $gambar_lama = $_POST['gambar_lama'];
    $gambar_baru = upload_gambar();

    // Jika upload gambar baru, hapus yang lama
    if ($gambar_baru !== "") {
        if (!empty($gambar_lama) && file_exists($gambar_lama)) {
            unlink($gambar_lama);
        }
        $gambar = $gambar_baru;
    } else {
        $gambar = $gambar_lama;
    }

    mysqli_query($koneksi,
        "UPDATE produk 
         SET nama_produk='$nama', harga='$harga', stok='$stok',
             deskripsi='$desk', gambar='$gambar'
         WHERE id=$id"
    );

    header("Location: page_admin.php");
    exit;
}

// ==========================================================
// LOAD PRODUK
// ==========================================================
$produk = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id DESC");

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Panel Admin</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <div class="navbar">
        <div class="brand">Panel Admin</div>
        <div class="nav-right">
            <button class="btn" onclick="location.href='produk.php'">Lihat Web</button>
            <button class="btn btn-danger" onclick="location.href='logout.php'">Logout</button>
        </div>
    </div>

    <!-- ============ FORM TAMBAH PRODUK ============ -->
    <h2 style="margin-top:20px;color:#0f61c9;">Tambah Produk Baru</h2>

    <div class="form-box">
        <form method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" name="nama" class="form-input" required>
            </div>

            <div class="form-group">
                <label>Harga</label>
                <input type="number" name="harga" class="form-input" required>
            </div>

            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stok" class="form-input" required>
            </div>

            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-input" required></textarea>
            </div>

            <div class="form-group">
                <label>Gambar Produk</label>
                <input type="file" name="gambar" class="form-input">
            </div>

            <button class="btn btn-add" type="submit" name="tambah_produk">Tambah Produk</button>
        </form>
    </div>

    <!-- ============ DAFTAR PRODUK ============ -->
    <h2 style="margin-top:30px;color:#0f61c9;">Daftar Produk</h2>

    <div class="products-grid">

        <?php while ($p = mysqli_fetch_assoc($produk)) { ?>
            <div class="product-card">

                <img src="<?= $p['gambar'] ?>" class="product-img" style="object-fit:cover">

                <h3 class="product-name"><?= $p['nama_produk'] ?></h3>
                <p class="product-price">Rp <?= number_format($p['harga']) ?></p>
                <p style="color:#444;">Stok: <b><?= $p['stok'] ?></b></p>

                <form method="POST" enctype="multipart/form-data" style="width:100%;margin-top:10px;">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="gambar_lama" value="<?= $p['gambar'] ?>">

                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="nama" class="form-input" value="<?= $p['nama_produk'] ?>">
                    </div>

                    <div class="form-group">
                        <label>Harga</label>
                        <input type="number" name="harga" class="form-input" value="<?= $p['harga'] ?>">
                    </div>

                    <div class="form-group">
                        <label>Stok</label>
                        <input type="number" name="stok" class="form-input" value="<?= $p['stok'] ?>">
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-input"><?= $p['deskripsi'] ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Ganti Gambar</label>
                        <input type="file" name="gambar" class="form-input">
                    </div>

                    <button class="btn btn-checkout" name="update_produk">Update</button>
                </form>

                <a href="page_admin.php?hapus=<?= $p['id'] ?>"
                   class="btn btn-danger" style="margin-top:10px;"
                   onclick="return confirm('Hapus produk ini?')">
                    Hapus
                </a>

            </div>
        <?php } ?>

    </div>

</div>

</body>
</html>
