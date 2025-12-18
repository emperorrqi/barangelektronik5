<?php
include 'koneksi.php';

// Ambil daftar barang
$barang = $mysqli->query("SELECT id_barang, nama_barang FROM master_barang_elektronik ORDER BY nama_barang ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Entry Barang Pesanan</title>
</head>
<body>

<h2>Entry Barang Pesanan</h2>

<form action="entry_barang_pesanan_process.php" method="POST">

    <label>Pilih Barang</label><br>
    <select name="id_barang" required>
        <option value="">-- Pilih Barang --</option>
        <?php while ($row = $barang->fetch_assoc()): ?>
            <option value="<?= $row['id_barang'] ?>"><?= $row['nama_barang'] ?></option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <label>Masukkan Serial Number</label><br>
    <small>Pisahkan dengan koma atau baris baru<br>
    Sistem akan membuat 1 row per 100 serial</small><br>
    <textarea name="serial_numbers" rows="6" cols="60" required></textarea>
    <br><br>

    <button type="submit">Simpan</button>

</form>

</body>
</html>
