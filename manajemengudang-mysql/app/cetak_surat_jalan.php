<?php
include 'koneksi.php';

// Ambil filter dari GET
$kode_surat = $_GET['kode_surat'] ?? '';
$hariIni    = isset($_GET['hari_ini']) ? true : false;

// Bangun query dasar
$sql = "
    SELECT s.kode_surat, s.tanggal, s.keterangan,
           d.nama_driver, d.kode_driver,
           g.kode_gudang, g.lokasi
    FROM trx_surat_jalan s
    JOIN master_driver d ON s.id_driver = d.id_driver
    JOIN master_gudang g ON s.id_gudang = g.id_gudang
    WHERE 1=1
";

// Filter hari ini
if ($hariIni) {
    $today = date('Y-m-d');
    $sql .= " AND s.tanggal = '$today'";
    $judul = "Surat Jalan Hari Ini ($today)";
} else {
    $judul = "Cetak Surat Jalan";
}

// Filter kode surat jalan
if ($kode_surat != '') {
    $sql .= " AND s.kode_surat LIKE '%$kode_surat%'";
}

$sql .= " ORDER BY s.id_surat ASC";
$dataSurat = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>🖨️ <?= $judul ?></title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { text-align: center; }
form { margin-bottom: 15px; }
input { padding: 6px; margin-right: 10px; }
button { padding: 6px 12px; cursor:pointer; background:#28a745; color:white; border:none; border-radius:4px; }
button:hover { background:#218838; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #000; padding: 8px; text-align: center; }
th { background-color: #3498db; color: #fff; }
@media print { .no-print { display: none; } }
.btn-back { display:inline-block; padding:8px 16px; background:#6c757d; color:white; border-radius:6px; text-decoration:none; margin-bottom:15px; }
.btn-back:hover { background:#495057; }
.btn-filter { display:inline-block; padding:6px 12px; border-radius:5px; text-decoration:none; color:white; margin-right:5px; }
.btn-hariini { background:#28a745; }
.btn-hariini:hover { background:#218838; }
.tanda-tangan { margin-top: 50px; width: 300px; float: right; text-align: center; }
.tanda-tangan p { margin-bottom: 80px; } /* space untuk tanda tangan */
</style>
</head>
<body>

<a href="index.php" class="btn-back no-print">⬅ Kembali ke Halaman Utama</a>

<h2><?= $judul ?></h2>

<!-- Tombol Cepat Filter Hari Ini -->
<div class="no-print" style="margin-bottom: 15px;">
    <a href="?hari_ini=1" class="btn-filter btn-hariini">📅 Hari Ini</a>
</div>

<!-- Form Filter Kode Surat -->
<form method="get" class="no-print">
    <label>Kode Surat Jalan:</label>
    <input type="text" name="kode_surat" value="<?= htmlspecialchars($kode_surat) ?>" placeholder="Kosongkan untuk semua">

    <?php if ($hariIni): ?>
        <input type="hidden" name="hari_ini" value="1">
    <?php endif; ?>

    <button type="submit">Filter</button>
    <button type="button" onclick="window.print()">🖨 Cetak</button>
</form>

<table>
    <tr>
        <th>Kode Surat</th>
        <th>Tanggal</th>
        <th>Driver</th>
        <th>Gudang</th>
        <th>Keterangan</th>
    </tr>
    <?php if($dataSurat->num_rows > 0): ?>
        <?php while($row = $dataSurat->fetch_assoc()): ?>
            <tr>
                <td><?= $row['kode_surat'] ?></td>
                <td><?= $row['tanggal'] ?></td>
                <td><?= $row['nama_driver'] ?> (<?= $row['kode_driver'] ?>)</td>
                <td><?= $row['kode_gudang'] ?> - <?= $row['lokasi'] ?></td>
                <td><?= $row['keterangan'] ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="5">Tidak ada surat jalan yang ditemukan.</td>
        </tr>
    <?php endif; ?>
</table>

<!-- Area Tanda Tangan -->
<div class="tanda-tangan">
    <p>Driver</p>
    __________________________
</div>

</body>
</html>
