<?php
include 'koneksi.php';

// Ambil filter tanggal dari GET
$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';

// Bangun query dasar
$sql = "SELECT r.*, b.kode_barang, b.nama_barang
        FROM trx_retur r
        JOIN master_barang_elektronik b ON r.id_barang = b.id_barang
        WHERE 1=1";

// Filter berdasarkan tanggal
if ($tanggal_awal != '' && $tanggal_akhir != '') {
    $sql .= " AND r.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
    $judul = "Laporan Retur Barang ($tanggal_awal s/d $tanggal_akhir)";
} else {
    $judul = "Laporan Retur Barang";
}

$sql .= " ORDER BY r.id_retur DESC";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= $judul ?></title>
<style>
body { font-family: Arial; padding: 20px; }
h2 { text-align: center; text-transform: uppercase; margin-bottom: 5px; }
h4 { text-align: center; margin-top: 0; font-weight: normal; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #000; padding: 8px; font-size: 14px; text-align: center; }
th { background: #eee; }
.btn-print, .btn-back { display: inline-block; margin-bottom: 15px; padding: 8px 16px; border-radius:6px; text-decoration:none; font-weight:bold; color:white; }
.btn-print { background:#28a745; }
.btn-print:hover { background:#1e7e34; }
.btn-back { background:#6c757d; }
.btn-back:hover { background:#495057; }
form { margin-bottom: 15px; }
input[type=date] { padding:6px; margin-right:10px; }
button { padding:6px 12px; cursor:pointer; }
@media print { .no-print { display: none; } }
</style>
</head>
<body>

<a href="index.php" class="btn-back no-print">⬅ Kembali ke Halaman Utama</a>

<h2><?= $judul ?></h2>
<h4>Sistem Informasi Manajemen Barang Elektronik</h4>

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
        <th>Kode Retur</th>
        <th>Tanggal</th>
        <th>Barang</th>
        <th>Jumlah</th>
        <th>Alasan</th>
    </tr>

    <?php if($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['kode_retur'] ?></td>
            <td><?= $row['tanggal'] ?></td>
            <td><?= $row['nama_barang'] ?> (<?= $row['kode_barang'] ?>)</td>
            <td><?= $row['jumlah'] ?></td>
            <td><?= $row['alasan'] ?></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="5">Tidak ada data retur ditemukan.</td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>
