<?php
include 'koneksi.php';

// ================= FILTER =================
$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';

$where = '';
$judul = "Laporan Barang Masuk";

// Filter Tanggal
if ($tanggal_awal != '' && $tanggal_akhir != '') {
    $awal_esc = $mysqli->real_escape_string($tanggal_awal);
    $akhir_esc = $mysqli->real_escape_string($tanggal_akhir);
    $where .= "WHERE m.tanggal BETWEEN '$awal_esc' AND '$akhir_esc'";
    $judul = "Barang Masuk ($awal_esc s/d $akhir_esc)";
}

// ================= DATA BARANG MASUK =================
$data = $mysqli->query("
    SELECT 
        m.*,
        b.nama_barang, b.kode_barang, b.spesifikasi,
        v.nama_vendor
    FROM v_barang_masuk_final m
    LEFT JOIN master_barang_elektronik b ON m.id_barang = b.id_barang
    LEFT JOIN master_vendor v ON m.id_vendor = v.id_vendor
    $where
    ORDER BY m.tanggal ASC, m.id_masuk ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>🖨️ <?= $judul ?></title>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h2 { text-align: center; margin-bottom: 20px; }
form { margin-bottom: 15px; }
input { padding: 6px; margin-right: 8px; }
button { padding: 6px 12px; border: none; border-radius: 5px; cursor: pointer; }
.btn-filter { background:#007bff; color:#fff; }
.btn-print  { background:#28a745; color:#fff; }
.btn-back { display:inline-block; padding:8px 14px; background:#6c757d; color:#fff; text-decoration:none; border-radius:6px; margin-bottom:10px; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 1px solid #000; padding: 8px; font-size: 13px; }
th { background: #007bff; color: #fff; }
tfoot td { font-weight: bold; background: #f2f2f2; }
@media print { .no-print { display: none; } }
</style>
</head>

<body>

<a href="index.php" class="btn-back no-print">⬅ Kembali</a>

<h2><?= $judul ?></h2>

<!-- ================= FORM FILTER ================= -->
<form method="get" class="no-print">
    <label>Tanggal Awal:
        <input type="date" name="tanggal_awal" value="<?= htmlspecialchars($tanggal_awal) ?>">
    </label>

    <label>Tanggal Akhir:
        <input type="date" name="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir) ?>">
    </label>

    <button type="submit" class="btn-filter">Filter</button>
    <button type="button" class="btn-print" onclick="window.print()">🖨 Print</button>
</form>

<!-- ================= TABEL ================= -->
<table>
<thead>
<tr>
    <th>No</th>
    <th>Kode Masuk</th>
    <th>Tanggal</th>
    <th>Barang</th>
    <th>Vendor</th>
    <th>Masuk</th>
    <th>Retur</th>
    <th>Sisa</th>
</tr>
</thead>

<tbody>
<?php
$no = 1;
$totalMasuk = $totalRetur = $totalSisa = 0;

while($row = $data->fetch_assoc()):
    $totalMasuk += $row['jumlah_masuk'];
    $totalRetur += $row['jumlah_retur'];
    $totalSisa  += $row['sisa_barang'];
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $row['kode_masuk'] ?></td>
    <td><?= $row['tanggal'] ?></td>
    <td><?= $row['kode_barang'].' - '.$row['nama_barang'] ?><br><small><?= $row['spesifikasi'] ?></small></td>
    <td><?= $row['nama_vendor'] ?></td>
    <td><?= $row['jumlah_masuk'] ?></td>
    <td><?= $row['jumlah_retur'] ?></td>
    <td><strong><?= $row['sisa_barang'] ?></strong></td>
</tr>
<?php endwhile; ?>
</tbody>

<tfoot>
<tr>
    <td colspan="5" align="right">TOTAL</td>
    <td><?= $totalMasuk ?></td>
    <td><?= $totalRetur ?></td>
    <td><?= $totalSisa ?></td>
</tr>
</tfoot>
</table>

</body>
</html>
