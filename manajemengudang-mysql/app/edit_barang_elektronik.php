<?php
include 'koneksi.php';

// ==========================
// Ambil kode_masuk dari GET
// ==========================
if (!isset($_GET['kode_masuk'])) {
    die("Kode Masuk tidak ditemukan.");
}
$kode_masuk = $_GET['kode_masuk'];

// ==========================
// Ambil data barang masuk
// ==========================
$q = $mysqli->query("
    SELECT m.*, b.nama_barang, b.kode_barang, b.spesifikasi, v.nama_vendor
    FROM trx_barang_masuk m
    LEFT JOIN master_barang_elektronik b ON m.id_barang = b.id_barang
    LEFT JOIN master_vendor v ON m.id_vendor = v.id_vendor
    WHERE m.kode_masuk = '$kode_masuk'
");
$data = $q->fetch_assoc();
if (!$data) die("Data tidak ditemukan.");

// ==========================
// Ambil daftar barang & vendor
// ==========================
$barangList = $mysqli->query("SELECT id_barang, nama_barang, kode_barang, spesifikasi FROM master_barang_elektronik ORDER BY nama_barang ASC");
$vendorList = $mysqli->query("SELECT id_vendor, nama_vendor FROM master_vendor ORDER BY nama_vendor ASC");

// ==========================
// Proses Update
// ==========================
if (isset($_POST['update'])) {
    $tanggal = $_POST['tanggal'];
    $id_barang = $_POST['id_barang'];
    $id_vendor = $_POST['id_vendor'];
    $jumlah = $_POST['jumlah'];
    $keterangan = $_POST['keterangan'];

    $update = $mysqli->query("
        UPDATE trx_barang_masuk SET 
            tanggal = '$tanggal',
            id_barang = '$id_barang',
            id_vendor = '$id_vendor',
            jumlah = '$jumlah',
            keterangan = '$keterangan'
        WHERE kode_masuk = '$kode_masuk'
    ");

    if ($update) {
        echo "<script>alert('Data berhasil diperbarui!'); window.location='barang_masuk.php';</script>";
    } else {
        echo "<script>alert('Gagal update: " . $mysqli->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Edit Barang Masuk</title>
<style>
body { font-family: Arial; padding:20px; }
.container { max-width:500px; margin:auto; border:1px solid #ccc; padding:20px; border-radius:8px; }
input, select, textarea { width:100%; padding:8px; margin-bottom:12px; border-radius:5px; border:1px solid #aaa; }
button { padding:10px 16px; border-radius:6px; cursor:pointer; border:none; }
.save { background:#007bff; color:white; }
.back { background:#6c757d; color:white; text-decoration:none; display:inline-block; padding:8px 14px; border-radius:6px; margin-top:5px; }
</style>
</head>
<body>

<h2>Edit Barang Masuk</h2>

<div class="container">
<form method="POST">

    <label>Kode Masuk</label>
    <input type="text" value="<?= $data['kode_masuk'] ?>" disabled>

    <label>Tanggal</label>
    <input type="date" name="tanggal" value="<?= $data['tanggal'] ?>" required>

    <label>Pilih Barang</label>
    <select name="id_barang" required>
        <option value="">-- Pilih Barang --</option>
        <?php while($b = $barangList->fetch_assoc()): ?>
            <option value="<?= $b['id_barang'] ?>" <?= ($b['id_barang']==$data['id_barang'])?'selected':'' ?>>
                <?= $b['kode_barang'] ?> - <?= $b['nama_barang'] ?> (<?= $b['spesifikasi'] ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <label>Pilih Vendor</label>
    <select name="id_vendor" required>
        <option value="">-- Pilih Vendor --</option>
        <?php while($v = $vendorList->fetch_assoc()): ?>
            <option value="<?= $v['id_vendor'] ?>" <?= ($v['id_vendor']==$data['id_vendor'])?'selected':'' ?>>
                <?= $v['nama_vendor'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Jumlah</label>
    <input type="number" name="jumlah" value="<?= $data['jumlah'] ?>" required>

    <label>Keterangan</label>
    <textarea name="keterangan"><?= $data['keterangan'] ?></textarea>

    <button type="submit" name="update" class="save">Update</button>
    <a href="barang_masuk.php" class="back">Kembali</a>
</form>
</div>

</body>
</html>
