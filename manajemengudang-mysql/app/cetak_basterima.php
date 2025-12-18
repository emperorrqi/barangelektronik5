<?php
include 'koneksi.php';

// Ambil filter dari GET
$kode_basterima = $_GET['kode_basterima'] ?? '';
$hariIni = isset($_GET['hari_ini']) ? true : false;
$today = date('Y-m-d');

$where = '';
if ($hariIni) {
    $where .= "WHERE t.tanggal = '$today'";
} else {
    $where .= "WHERE 1";
}

// Tambahkan filter kode_basterima jika diisi
if ($kode_basterima != '') {
    $where .= " AND t.kode_basterima LIKE '%$kode_basterima%'";
}

// Query data basterima termasuk sn_perangkat
$sql = "
    SELECT t.*, b.kode_barang, b.nama_barang
    FROM trx_berita_serah_terima t
    LEFT JOIN master_barang_elektronik b ON t.id_barang = b.id_barang
    $where
    ORDER BY t.id_serah DESC
";
$data = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>🖨️ Cetak Basterima</title>
<style>
body { font-family: Arial; padding:20px; }
h2 { text-align:center; margin-bottom:20px; }
form { margin-bottom:15px; }
input { padding:6px; margin-right:10px; }
button { padding:6px 12px; cursor:pointer; background:#8e44ad; color:white; border:none; border-radius:5px; }
button:hover { background:#732d91; }
table { width:100%; border-collapse:collapse; margin-bottom:50px; }
th, td { border:1px solid #000; padding:8px; text-align:left; font-size:14px; }
th { background:#8e44ad; color:white; }
@media print { .no-print { display:none; } }
.btn-back { display:inline-block; padding:8px 16px; background:#6c757d; color:white; border-radius:6px; text-decoration:none; margin-bottom:15px; }
.btn-back:hover { background:#495057; }

/* Area tanda tangan */
.ttd-container { width:100%; display:flex; justify-content:space-between; margin-top:50px; }
.ttd { width:40%; text-align:center; }
.ttd p { margin-bottom:80px; }
</style>
</head>
<body>

<a href="index.php" class="btn-back no-print">⬅ Kembali ke Halaman Utama</a>

<h2>📄 Cetak Berita Serah Terima (<?= date('d-m-Y') ?>)</h2>

<!-- Form Filter -->
<form method="get" class="no-print">
    <label>Kode Basterima:</label>
    <input type="text" name="kode_basterima" value="<?= htmlspecialchars($kode_basterima) ?>" placeholder="Kosongkan untuk semua">

    <label>
        <input type="checkbox" name="hari_ini" value="1" <?= $hariIni ? 'checked' : '' ?>> Hari Ini
    </label>

    <button type="submit">Filter</button>
    <button type="button" onclick="window.print()">🖨️ Print</button>
</form>

<table>
<tr>
<th>No</th>
<th>Kode Basterima</th>
<th>Tanggal</th>
<th>Penerima</th>
<th>Barang</th>
<th>Jumlah</th>
<th>SN Perangkat</th>
</tr>
<?php $no=1; while($row = $data->fetch_assoc()): ?>
<tr>
<td><?= $no++ ?></td>
<td><?= $row['kode_basterima'] ?></td>
<td><?= $row['tanggal'] ?></td>
<td><?= $row['penerima'] ?></td>
<td><?= $row['kode_barang'] ?> - <?= $row['nama_barang'] ?></td>
<td><?= $row['jumlah'] ?></td>
<td><?= $row['sn_perangkat'] ?></td>
</tr>
<?php endwhile; ?>
</table>

<!-- Area Tanda Tangan -->
<div class="ttd-container">
    <div class="ttd">
        <p>Pihak Pertama</p>
        __________________________
    </div>
    <div class="ttd">
        <p>Pihak Kedua</p>
        __________________________
    </div>
</div>

</body>
</html>
