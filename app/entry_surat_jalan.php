<?php
include 'koneksi.php';

// ==========================
// Generate Kode Surat Otomatis
// ==========================
function generateKodeSurat($mysqli) {
    $result = $mysqli->query("SELECT MAX(kode_surat) AS max_kode FROM trx_surat_jalan");
    $data = $result->fetch_assoc();
    $maxKode = $data['max_kode'];

    if ($maxKode) {
        $num = (int) substr($maxKode, 2);
        $num++;
        return "SJ" . str_pad($num, 3, "0", STR_PAD_LEFT);
    } else {
        return "SJ001";
    }
}

$kodeSuratOtomatis = generateKodeSurat($mysqli);

// ==========================
// Tambah Data
// ==========================
if (isset($_POST['tambah'])) {
    $kode_surat = $_POST['kode_surat'];
    $tanggal = $_POST['tanggal'];
    $id_driver = $_POST['id_driver'];
    $id_gudang = $_POST['id_gudang'];
    $keterangan = $_POST['keterangan'];

    $stmt = $mysqli->prepare("INSERT INTO trx_surat_jalan (kode_surat, tanggal, id_driver, id_gudang, keterangan) 
                              VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiis", $kode_surat, $tanggal, $id_driver, $id_gudang, $keterangan);
    $stmt->execute();
    $stmt->close();

    header("Location: entry_surat_jalan.php?success=1");
    exit;
}

// ==========================
// Ambil data driver & gudang
// ==========================
$driver = $mysqli->query("SELECT * FROM master_driver ORDER BY nama_driver ASC");
$gudang = $mysqli->query("SELECT * FROM master_gudang ORDER BY nama_gudang ASC");

// ==========================
// Ambil data surat jalan
// ==========================
$dataSurat = $mysqli->query("
    SELECT s.id_surat, s.kode_surat, s.tanggal, s.keterangan,
           d.nama_driver, d.kode_driver,
           g.kode_gudang, g.lokasi
    FROM trx_surat_jalan s
    JOIN master_driver d ON s.id_driver = d.id_driver
    JOIN master_gudang g ON s.id_gudang = g.id_gudang
    ORDER BY s.id_surat DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Entry Surat Jalan</title>
<style>
body { font-family: Arial; background:#eef1f7; padding:25px; }
.container { width:700px; margin:auto; background:#fff; padding:25px; border-radius:12px; box-shadow:0 3px 10px rgba(0,0,0,0.1); }
h2,h3{margin-bottom:10px;color:#2c3e50;}
label{font-weight:bold;margin-top:10px;display:block;}
input,select,textarea{width:100%;padding:10px;margin-top:5px;border-radius:6px;border:1px solid #cfcfcf;}
button{background:#3498db;padding:10px 18px;color:white;border:none;margin-top:12px;border-radius:6px;cursor:pointer;font-weight:bold;transition:0.2s;}
button:hover{background:#2980b9;}
table{width:100%;margin-top:25px;border-collapse:collapse;font-size:14px;}
th{background:#3498db;color:white;padding:9px;}
td{padding:8px;border:1px solid #ddd;text-align:center;}
.alert-success{background:#2ecc71;color:white;padding:10px;margin-bottom:15px;border-radius:6px;}
.btn-back, .btn-cetak {display:inline-block;margin-bottom:15px;padding:8px 16px;border-radius:6px;text-decoration:none;font-weight:bold;color:white;}
.btn-back {background:#6c757d;}
.btn-back:hover {background:#495057;}
.btn-cetak {background:#28a745;margin-left:10px;}
.btn-cetak:hover {background:#218838;}
</style>
</head>
<body>

<div class="container">

    <h2>Entry Surat Jalan</h2>

    <!-- Tombol Kembali & Cetak -->
    <div>
        <a href="index.php" class="btn-back">⬅ Kembali ke Halaman Utama</a>
        <a href="cetak_surat_jalan.php" target="_blank" class="btn-cetak">🖨 Cetak Surat Jalan Hari Ini</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert-success">✔ Data berhasil disimpan!</div>
    <?php endif; ?>

    <form method="POST">
        <label>Kode Surat Jalan</label>
        <input type="text" name="kode_surat" value="<?= $kodeSuratOtomatis ?>" readonly>

        <label>Tanggal</label>
        <input type="date" name="tanggal" required>

        <label>Driver</label>
        <select name="id_driver" required>
            <option value="">-- Pilih Driver --</option>
            <?php while($d = $driver->fetch_assoc()): ?>
                <option value="<?= $d['id_driver'] ?>"><?= $d['nama_driver'] ?> (<?= $d['kode_driver'] ?>)</option>
            <?php endwhile; ?>
        </select>

        <label>Gudang</label>
        <select name="id_gudang" required>
            <option value="">-- Pilih Gudang --</option>
            <?php while($g = $gudang->fetch_assoc()): ?>
                <option value="<?= $g['id_gudang'] ?>"><?= $g['kode_gudang'] ?> - <?= $g['lokasi'] ?></option>
            <?php endwhile; ?>
        </select>

        <label>Keterangan</label>
        <textarea name="keterangan" rows="3"></textarea>

        <button type="submit" name="tambah">Simpan</button>
    </form>

    <hr>

    <h3>Data Surat Jalan</h3>
    <table>
        <tr>
            <th>Kode Surat</th>
            <th>Tanggal</th>
            <th>Driver</th>
            <th>Gudang</th>
            <th>Keterangan</th>
        </tr>

        <?php while($row = $dataSurat->fetch_assoc()): ?>
            <tr>
                <td><?= $row['kode_surat'] ?></td>
                <td><?= $row['tanggal'] ?></td>
                <td><?= $row['nama_driver'] ?> (<?= $row['kode_driver'] ?>)</td>
                <td><?= $row['kode_gudang'] ?> - <?= $row['lokasi'] ?></td>
                <td><?= $row['keterangan'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
