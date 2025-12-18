<?php 
include 'koneksi.php';

// Ambil filter dari GET
$kode_pesanan = $_GET['kode_pesanan'] ?? '';
$hariIni = isset($_GET['hari_ini']) ? true : false;
$today = date('Y-m-d');

// Build WHERE
$where = $hariIni ? "WHERE p.tanggal = '$today'" : "WHERE 1";

// Filter kode pesanan
if ($kode_pesanan != '') {
    $kode_pesanan_safe = $mysqli->real_escape_string($kode_pesanan);
    $where .= " AND p.kode_pesanan LIKE '%$kode_pesanan_safe%'";
}

// Query JOIN lengkap
$sql = "
    SELECT p.*, 
           b.kode_barang, b.nama_barang, b.spesifikasi,
           g.nama_gudang,
           a.nama_admin, a.kode_admin
    FROM trx_barang_pesanan p
    LEFT JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    LEFT JOIN master_gudang g ON p.id_gudang = g.id_gudang
    LEFT JOIN master_administrasi a ON p.id_admin = a.id_admin
    $where
    ORDER BY p.id_pesanan DESC
";

$data = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>🖨️ Cetak Barang Pesanan</title>

<style>
body { font-family: Arial; padding:20px; }
h2 { text-align:center; margin-bottom:20px; }
form { margin-bottom:15px; }
input { padding:6px; margin-right:10px; }
button {
    padding:6px 12px; cursor:pointer;
    background:#007bff; color:white;
    border:none; border-radius:5px;
}
button:hover { background:#0056b3; }
table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td {
    border:1px solid #000;
    padding:8px;
    font-size:14px;
}
th { background:#007bff; color:white; }
@media print { .no-print { display:none; } }

.btn-back {
    display:inline-block; padding:8px 16px;
    background:#6c757d; color:white;
    border-radius:6px;
    text-decoration:none;
    margin-bottom:15px;
}
.btn-back:hover { background:#495057; }

.tanda-tangan {
    margin-top: 50px;
    width: 300px;
    float: right;
    text-align: center;
}
.tanda-tangan p { margin-bottom: 80px; }
</style>
</head>

<body>

<a href="index.php" class="btn-back no-print">⬅ Kembali ke Halaman Utama</a>

<h2>📄 Cetak Pesanan Barang (<?= date('d-m-Y') ?>)</h2>

<!-- Form Filter -->
<form method="get" class="no-print">
    <label>Kode Pesanan:</label>
    <input type="text" name="kode_pesanan"
           value="<?= htmlspecialchars($kode_pesanan) ?>"
           placeholder="Kosongkan untuk semua">

    <label>
        <input type="checkbox" name="hari_ini" value="1" <?= $hariIni ? 'checked' : '' ?>>
        Hari Ini
    </label>

    <button type="submit">Filter</button>
    <button type="button" onclick="window.print()">🖨️ Print</button>
</form>

<table>
<tr>
    <th>No</th>
    <th>Kode Pesanan</th>
    <th>Tanggal</th>
    <th>Admin</th>
    <th>Barang</th>
    <th>Gudang</th>
    <th>Jumlah</th>
    <th>Serial Number</th>
</tr>

<?php 
$no = 1;
while($row = $data->fetch_assoc()):
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $row['kode_pesanan'] ?></td>
    <td><?= $row['tanggal'] ?></td>
    <td><?= $row['kode_admin'] ?> - <?= $row['nama_admin'] ?></td>
    <td>
        <?= $row['kode_barang'] ?> - <?= $row['nama_barang'] ?><br>
        <small><?= $row['spesifikasi'] ?></small>
    </td>
    <td><?= $row['nama_gudang'] ?></td>
    <td><?= $row['jumlah'] ?></td>
    <td><?= $row['serial_number'] ?></td>
</tr>
<?php endwhile; ?>
</table>

<div class="tanda-tangan">
    <p>Disetujui Oleh</p>
    __________________________
</div>

</body>
</html>
