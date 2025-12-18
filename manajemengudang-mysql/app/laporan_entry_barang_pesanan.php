<?php
include 'koneksi.php';

// Ambil filter tanggal dari GET
$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';

// Bangun query dasar
$sql = "
    SELECT p.*, 
           b.kode_barang, b.nama_barang, b.spesifikasi,
           g.nama_gudang
    FROM trx_barang_pesanan p
    LEFT JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    LEFT JOIN master_gudang g ON p.id_gudang = g.id_gudang
    WHERE 1=1
";

// Filter berdasarkan tanggal
if ($tanggal_awal != '' && $tanggal_akhir != '') {
    $sql .= " AND p.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
    $judul = "Laporan Barang Pesanan ($tanggal_awal s/d $tanggal_akhir)";
} else {
    $judul = "Laporan Barang Pesanan";
}

$sql .= " ORDER BY p.id_pesanan DESC";
$data = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?= $judul ?></title>
<style>
body { font-family: Arial; padding:20px; }
h2 { text-align:center; margin-bottom:20px; }
form { margin-bottom:15px; }
input[type=date] { padding:6px; margin-right:10px; }
button { padding:6px 12px; cursor:pointer; background:#007bff; color:white; border:none; border-radius:5px; }
button:hover { background:#0056b3; }
table { width:100%; border-collapse:collapse; }
th, td { border:1px solid #000; padding:8px; text-align:left; font-size:14px; }
th { background:#007bff; color:white; }
@media print { .no-print { display:none; } }
.btn-back { display:inline-block; padding:8px 16px; background:#6c757d; color:white; border-radius:6px; text-decoration:none; margin-bottom:15px; }
.btn-back:hover { background:#495057; }
.btn-print { padding:6px 12px; margin-left:5px; cursor:pointer; background:#28a745; color:white; border:none; border-radius:5px; }
.btn-print:hover { background:#218838; }
</style>
</head>
<body>

<a href="index.php" class="btn-back no-print">⬅ Kembali ke Halaman Utama</a>

<h2><?= $judul ?></h2>

<!-- Form Filter Tanggal -->
<form method="get" class="no-print">
    <label>Tanggal Awal:</label>
    <input type="date" name="tanggal_awal" value="<?= htmlspecialchars($tanggal_awal) ?>" required>
    <label>Tanggal Akhir:</label>
    <input type="date" name="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir) ?>" required>
    <button type="submit">Filter</button>
    <button type="button" onclick="window.print()" class="btn-print">🖨️ Print</button>
</form>

<table>
    <tr>
        <th>No</th>
        <th>Kode Pesanan</th>
        <th>Tanggal</th>
        <th>Barang</th>
        <th>Gudang</th>
        <th>Jumlah</th>
    </tr>
    <?php $no=1; if($data->num_rows > 0): ?>
        <?php while($row = $data->fetch_assoc()): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $row['kode_pesanan'] ?></td>
            <td><?= $row['tanggal'] ?></td>
            <td><?= $row['kode_barang'] ?> - <?= $row['nama_barang'] ?><br><small><?= $row['spesifikasi'] ?></small></td>
            <td><?= $row['nama_gudang'] ?></td>
            <td><?= $row['jumlah'] ?></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" style="text-align:center;">Tidak ada data pesanan ditemukan.</td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>
