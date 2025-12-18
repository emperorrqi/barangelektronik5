<?php
include 'koneksi.php';

/* ================= FILTER ================= */
$kode_masuk = $_GET['kode_masuk'] ?? '';
$hariIni    = isset($_GET['hari_ini']);
$id_admin   = $_GET['id_admin'] ?? '';

$where = '';
$today = date('Y-m-d');
$judul = "Laporan Stok Barang";

if ($hariIni) {
    $where .= "WHERE m.tanggal = '$today'";
    $judul = "Laporan Stok Barang ($today)";
}

if ($kode_masuk != '') {
    $kode = $mysqli->real_escape_string($kode_masuk);
    $where .= ($where == '' ? "WHERE " : " AND ") . "m.kode_masuk LIKE '%$kode%'";
}

if ($id_admin != '') {
    $where .= ($where == '' ? "WHERE " : " AND ") . "m.id_admin = ".(int)$id_admin;
}

/* ================= DATA ADMIN ================= */
$admin = $mysqli->query("SELECT * FROM master_administrasi ORDER BY nama_admin");

/* ================= DATA LAPORAN ================= */
$data = $mysqli->query("
    SELECT 
        m.id_masuk,
        m.kode_masuk,
        m.tanggal,
        m.id_barang,
        m.jumlah_masuk,
        m.jumlah_retur,

        b.kode_barang,
        b.nama_barang,
        b.spesifikasi,

        v.nama_vendor,
        a.kode_admin,
        a.nama_admin,

        IFNULL(SUM(p.jumlah),0) AS jumlah_pesanan,

        (IFNULL(m.jumlah_masuk,0) - IFNULL(m.jumlah_retur,0)) AS stok_awal,
        ((IFNULL(m.jumlah_masuk,0) - IFNULL(m.jumlah_retur,0)) - IFNULL(SUM(p.jumlah),0)) AS sisa_final

    FROM v_barang_masuk_final m
    LEFT JOIN master_barang_elektronik b ON m.id_barang = b.id_barang
    LEFT JOIN master_vendor v ON m.id_vendor = v.id_vendor
    LEFT JOIN master_administrasi a ON m.id_admin = a.id_admin
    LEFT JOIN trx_barang_pesanan p ON p.id_barang = m.id_barang
    $where
    GROUP BY m.id_masuk
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
h2 { text-align: center; margin-bottom: 15px; }
form { margin-bottom: 15px; }
input, select { padding: 6px; margin-right: 6px; }
button { padding: 6px 12px; border:none; border-radius:5px; cursor:pointer; }
.btn-filter { background:#007bff; color:#fff; }
.btn-print { background:#28a745; color:#fff; }
.btn-back { display:inline-block; padding:8px 14px; background:#6c757d; color:#fff; text-decoration:none; border-radius:6px; margin-bottom:10px; }
table { width:100%; border-collapse: collapse; }
th, td { border:1px solid #000; padding:8px; font-size:13px; }
th { background:#007bff; color:#fff; }
@media print { .no-print { display:none; } }
</style>
</head>

<body>

<a href="index.php" class="btn-back no-print">⬅ Kembali</a>

<h2><?= $judul ?></h2>

<!-- ================= FILTER ================= -->
<form method="get" class="no-print">

    <label>Tanggal Awal</label>
    <input type="date" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>">

    <label>Tanggal Akhir</label>
    <input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>">

    

    <button class="btn-filter">Filter</button>
    <button type="button" class="btn-print" onclick="window.print()">🖨 Print</button>
</form>


<!-- ================= TABEL ================= -->
<table>
<thead>
<tr>
    <th>No</th>
    <th>Kode Masuk</th>
    <th>Tanggal</th>
    <th>Admin</th>
    <th>Barang</th>
    <th>Vendor</th>
    <th>Masuk</th>
    <th>Retur</th>
    <th>Pesanan</th>
    <th>Sisa</th>
</tr>
</thead>

<tbody>
<?php $no=1; while($r = $data->fetch_assoc()): ?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $r['kode_masuk'] ?></td>
    <td><?= $r['tanggal'] ?></td>
    <td><?= $r['kode_admin'].' - '.$r['nama_admin'] ?></td>
    <td>
        <?= $r['kode_barang'].' - '.$r['nama_barang'] ?><br>
        <small><?= $r['spesifikasi'] ?></small>
    </td>
    <td><?= $r['nama_vendor'] ?></td>
    <td><?= $r['jumlah_masuk'] ?></td>
    <td><?= $r['jumlah_retur'] ?></td>
    <td><?= $r['jumlah_pesanan'] ?></td>
    <td><strong><?= $r['sisa_final'] ?></strong></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

</body>
</html>
