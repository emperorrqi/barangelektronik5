<?php
include 'koneksi.php';

// Ambil filter tanggal dari GET
$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';

// Bangun query
$sql = "SELECT t.*, b.nama_barang, b.kode_barang
        FROM trx_berita_serah_terima t
        JOIN master_barang_elektronik b ON t.id_barang = b.id_barang
        WHERE 1=1";

// Filter tanggal
if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $awal = $mysqli->real_escape_string($tanggal_awal);
    $akhir = $mysqli->real_escape_string($tanggal_akhir);
    $sql .= " AND t.tanggal BETWEEN '$awal' AND '$akhir'";
}

$sql .= " ORDER BY t.id_serah DESC";
$data = $mysqli->query($sql);

// Judul laporan
$judul = "Laporan Berita Serah Terima";
if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $judul .= " ($tanggal_awal s/d $tanggal_akhir)";
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>🖨️ <?= $judul ?></title>
<style>
body { font-family: Arial; padding:20px; }
h2 { text-align:center; margin-bottom:20px; }
form { margin-bottom:15px; text-align:center; }
input { padding:6px; margin-right:10px; }
button { padding:6px 12px; cursor:pointer; background:#28a745; color:white; border:none; border-radius:4px; }
button:hover { background:#218838; }
table { width:100%; border-collapse:collapse; margin-top:15px; }
th, td { border:1px solid #000; padding:8px; text-align:center; font-size:14px; }
th { background:#8e44ad; color:white; }
.no-print { margin-bottom:15px; text-align:center; }
.btn-back { display:inline-block; padding:8px 16px; background:#6c757d; color:white; border-radius:6px; text-decoration:none; margin-bottom:15px; }
.btn-back:hover { background:#495057; }
</style>
</head>
<body>

<a href="index.php" class="btn-back no-print">⬅ Kembali ke Halaman Utama</a>

<h2><?= $judul ?></h2>

<!-- Form Filter Tanggal -->
<form method="get" class="no-print">
    <label>Tanggal Awal:</label>
    <input type="date" name="tanggal_awal" value="<?= htmlspecialchars($tanggal_awal) ?>">
    <label>Tanggal Akhir:</label>
    <input type="date" name="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir) ?>">
    <button type="submit">Filter</button>
    <button type="button" onclick="window.print()">🖨️ Print</button>
</form>

<!-- Tabel Basterima -->
<table>
<tr>
<th>No</th>
<th>Kode Basterima</th>
<th>Tanggal</th>
<th>Penerima</th>
<th>Barang</th>
<th>Jumlah</th>
</tr>
<?php $no=1; while($r=$data->fetch_assoc()): ?>
<tr>
<td><?= $no++ ?></td>
<td><?= $r['kode_basterima'] ?></td>
<td><?= $r['tanggal'] ?></td>
<td><?= $r['penerima'] ?></td>
<td><?= $r['nama_barang'] ?> (<?= $r['kode_barang'] ?>)</td>
<td><?= $r['jumlah'] ?></td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
