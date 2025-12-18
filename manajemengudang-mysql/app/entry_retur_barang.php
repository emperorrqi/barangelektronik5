<?php
include 'koneksi.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* =========================
   Generate Kode Retur Otomatis
   ========================= */
function generateKodeRetur($mysqli) {
    $q = $mysqli->query("SELECT MAX(kode_retur) AS max_kode FROM trx_retur");
    $d = $q->fetch_assoc();
    $max = $d['max_kode'];

    if ($max) {
        $num = (int) substr($max, 2) + 1;
        return "RT" . str_pad($num, 3, "0", STR_PAD_LEFT);
    }
    return "RT001";
}

$kodeReturOtomatis = generateKodeRetur($mysqli);
$errorMessage = "";

/* =========================
   Simpan Retur
   ========================= */
if (isset($_POST['tambah'])) {
    try {
        $stmt = $mysqli->prepare(
            "INSERT INTO trx_retur (kode_retur, tanggal, id_barang, jumlah, alasan)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssiis",
            $_POST['kode_retur'],
            $_POST['tanggal'],
            $_POST['id_barang'],
            $_POST['jumlah'],
            $_POST['alasan']
        );
        $stmt->execute();

        header("Location: entry_retur_barang.php?success=1");
        exit;
    } catch (mysqli_sql_exception $e) {
        $errorMessage = $e->getMessage();
    }
}

/* =========================
   Data Barang (stok > 0)
   ========================= */
$barang = $mysqli->query("
    SELECT id_barang, nama_barang, kode_barang, stok
    FROM master_barang_elektronik
    WHERE stok > 0
    ORDER BY nama_barang ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Entry Retur Barang</title>
<style>
body { font-family: Arial; background:#eef1f7; padding:25px; }
.container {
    width:760px;margin:auto;background:white;
    padding:25px;border-radius:12px;
    box-shadow:0 3px 10px rgba(0,0,0,.1);
}
h2,h3 { color:#2c3e50;margin-bottom:10px }
label { font-weight:bold;margin-top:10px;display:block }
input,select,textarea {
    width:100%;padding:10px;margin-top:5px;
    border-radius:6px;border:1px solid #ccc;
}
button {
    background:#e74c3c;color:white;
    padding:10px 18px;border:none;
    margin-top:15px;border-radius:6px;
    font-weight:bold;cursor:pointer;
}
button:hover { background:#c0392b }

table { width:100%;border-collapse:collapse;margin-top:25px }
th { background:#e74c3c;color:white;padding:9px }
td { padding:8px;border:1px solid #ddd;text-align:center }

.alert-success {
    background:#2ecc71;color:white;
    padding:10px;border-radius:6px;margin-bottom:15px;
}
.alert-error {
    background:#e74c3c;color:white;
    padding:10px;border-radius:6px;margin-bottom:15px;
}

.btn {
    display:inline-block;margin-bottom:15px;
    padding:8px 16px;color:white;
    border-radius:6px;text-decoration:none;font-weight:bold;
}
.btn-back { background:#6c757d }
.btn-print { background:#28a745 }
.btn-back:hover { background:#495057 }
.btn-print:hover { background:#218838 }
</style>
</head>

<body>
<div class="container">

<h2>Entry Retur Barang</h2>

<a href="index.php" class="btn btn-back">⬅ Kembali</a>
<a href="cetak_retur_barang.php" target="_blank" class="btn btn-print">🖨️ Cetak Retur</a>

<?php if (isset($_GET['success'])): ?>
<div class="alert-success">✔ Retur berhasil disimpan</div>
<?php endif; ?>

<?php if ($errorMessage): ?>
<div class="alert-error">❌ <?= htmlspecialchars($errorMessage) ?></div>
<?php endif; ?>

<form method="POST">
    <label>Kode Retur</label>
    <input type="text" name="kode_retur" value="<?= $kodeReturOtomatis ?>" readonly>

    <label>Tanggal</label>
    <input type="date" name="tanggal" required>

    <label>Barang (stok tersedia)</label>
    <select name="id_barang" required>
        <option value="">-- Pilih Barang --</option>
        <?php while ($b = $barang->fetch_assoc()): ?>
            <option value="<?= $b['id_barang'] ?>">
                <?= $b['nama_barang'] ?> (<?= $b['kode_barang'] ?> | stok: <?= $b['stok'] ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <label>Jumlah Retur</label>
    <input type="number" name="jumlah" min="1" required>

    <label>Alasan Retur</label>
    <textarea name="alasan" rows="3" required></textarea>

    <button type="submit" name="tambah">💾 Simpan Retur</button>
</form>

<hr>

<h3>Riwayat Retur Barang</h3>

<table>
<tr>
    <th>Kode</th>
    <th>Tanggal</th>
    <th>Barang</th>
    <th>Jumlah</th>
    <th>Alasan</th>
</tr>

<?php
$q = $mysqli->query("
    SELECT r.*, b.nama_barang, b.kode_barang
    FROM trx_retur r
    JOIN master_barang_elektronik b ON r.id_barang = b.id_barang
    ORDER BY r.id_retur DESC
");
while ($r = $q->fetch_assoc()):
?>
<tr>
    <td><?= $r['kode_retur'] ?></td>
    <td><?= $r['tanggal'] ?></td>
    <td><?= $r['nama_barang'] ?> (<?= $r['kode_barang'] ?>)</td>
    <td><?= $r['jumlah'] ?></td>
    <td><?= $r['alasan'] ?></td>
</tr>
<?php endwhile; ?>

</table>

</div>
</body>
</html>
