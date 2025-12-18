<?php
include 'koneksi.php';

/* ==========================
   GENERATE KODE PESANAN
========================== */
function generateKodePesanan($mysqli) {
    $q = $mysqli->query("SELECT MAX(kode_pesanan) AS max_kode FROM trx_barang_pesanan");
    $d = $q->fetch_assoc()['max_kode'] ?? null;

    if ($d) {
        $n = (int)substr($d, 3) + 1;
        return "PSN" . str_pad($n, 3, "0", STR_PAD_LEFT);
    }
    return "PSN001";
}

$kode_pesanan = generateKodePesanan($mysqli);

/* ==========================
   TAMBAH PESANAN
========================== */
if (isset($_POST['tambah'])) {

    $kode         = $_POST['kode_pesanan'];
    $tanggal      = $_POST['tanggal'];
    $id_admin     = $_POST['id_admin'];
    $id_barang    = $_POST['id_barang'];
    $id_gudang    = $_POST['id_gudang'];
    $jumlah       = (int)$_POST['jumlah'];
    $serial       = $_POST['serial_number'];

    /* ==========================
       HITUNG STOK SISA (FINAL)
    ========================== */

    // TOTAL MASUK
    $qMasuk = $mysqli->prepare("
        SELECT IFNULL(SUM(jumlah),0) total_masuk
        FROM trx_barang_masuk
        WHERE id_barang=?
    ");
    $qMasuk->bind_param("i", $id_barang);
    $qMasuk->execute();
    $total_masuk = $qMasuk->get_result()->fetch_assoc()['total_masuk'];
    $qMasuk->close();

    // TOTAL RETUR
    $qRetur = $mysqli->prepare("
        SELECT IFNULL(SUM(jumlah),0) total_retur
        FROM trx_retur
        WHERE id_barang=?
    ");
    $qRetur->bind_param("i", $id_barang);
    $qRetur->execute();
    $total_retur = $qRetur->get_result()->fetch_assoc()['total_retur'];
    $qRetur->close();

    // TOTAL PESANAN SEBELUMNYA
    $qPesanan = $mysqli->prepare("
        SELECT IFNULL(SUM(jumlah),0) total_pesanan
        FROM trx_barang_pesanan
        WHERE id_barang=?
    ");
    $qPesanan->bind_param("i", $id_barang);
    $qPesanan->execute();
    $total_pesanan = $qPesanan->get_result()->fetch_assoc()['total_pesanan'];
    $qPesanan->close();

    // HITUNG SISA STOK
    $stok_sisa = $total_masuk - $total_retur - $total_pesanan;

    if ($jumlah > $stok_sisa) {
        echo "<script>
            alert('❌ Pesanan melebihi stok tersedia!\\nSisa stok: $stok_sisa');
            window.history.back();
        </script>";
        exit;
    }

    /* ==========================
       INSERT PESANAN
    ========================== */
    $stmt = $mysqli->prepare("
        INSERT INTO trx_barang_pesanan
        (kode_pesanan, tanggal, id_admin, id_barang, id_gudang, jumlah, serial_number)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "ssiiiis",
        $kode, $tanggal, $id_admin, $id_barang, $id_gudang, $jumlah, $serial
    );
    $stmt->execute();
    $stmt->close();

    header("Location: entry_barang_pesanan.php");
    exit;
}

/* ==========================
   HAPUS PESANAN
========================== */
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $mysqli->prepare("DELETE FROM trx_barang_pesanan WHERE id_pesanan=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: entry_barang_pesanan.php");
    exit;
}

/* ==========================
   DATA TAMPIL
========================== */
$data = $mysqli->query("
    SELECT p.*, 
           b.kode_barang, b.nama_barang, b.spesifikasi,
           g.nama_gudang,
           a.kode_admin, a.nama_admin
    FROM trx_barang_pesanan p
    LEFT JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    LEFT JOIN master_gudang g ON p.id_gudang = g.id_gudang
    LEFT JOIN master_administrasi a ON p.id_admin = a.id_admin
    ORDER BY p.id_pesanan DESC
");

$barang = $mysqli->query("SELECT * FROM master_barang_elektronik ORDER BY nama_barang");
$gudang = $mysqli->query("SELECT * FROM master_gudang ORDER BY nama_gudang");
$admin  = $mysqli->query("SELECT * FROM master_administrasi ORDER BY nama_admin");
?>

<!DOCTYPE html>
<html>
<head>
<title>📦 Entry Barang Pesanan</title>
<style>
body { font-family:Arial; background:#eef0f2; padding:25px; }
.card { background:#fff; padding:25px; width:800px; margin:auto; border-radius:12px; box-shadow:0 3px 15px rgba(0,0,0,.1);}
input,select,textarea{width:100%;padding:10px;margin:7px 0 14px;border-radius:6px;border:1px solid #bbb;}
button{padding:12px 20px;border:none;background:#007bff;color:#fff;border-radius:6px;cursor:pointer}
table{width:100%;border-collapse:collapse;margin-top:25px}
th,td{border:1px solid #ddd;padding:10px}
th{background:#007bff;color:#fff}
.btn-del{color:red;font-weight:bold;text-decoration:none}
</style>
</head>
<body>

<div class="card">
<h2>📦 Entry Barang Pesanan</h2>
<!-- Tombol Kembali dan Cetak -->
<div class="no-print" style="margin-bottom:15px;">
    <a href="index.php" style="padding:10px 18px;background:#6c757d;color:white;border-radius:6px;text-decoration:none;margin-right:10px;">⬅ Kembali ke Halaman Utama</a>
    <a href="cetak_barang_pesanan.php" target="_blank" style="padding:10px 18px;background:#28a745;color:white;border-radius:6px;text-decoration:none;">🖨️ Cetak Pesanan</a>
</div>

<form method="post">
<label>Kode Pesanan</label>
<input type="text" name="kode_pesanan" value="<?= $kode_pesanan ?>" readonly>

<label>Tanggal</label>
<input type="date" name="tanggal" required>

<label>Admin</label>
<select name="id_admin" required>
<option value="">-- Pilih Admin --</option>
<?php while($a=$admin->fetch_assoc()): ?>
<option value="<?= $a['id_admin'] ?>"><?= $a['kode_admin'].' - '.$a['nama_admin'] ?></option>
<?php endwhile; ?>
</select>

<label>Barang</label>
<select name="id_barang" required>
<option value="">-- Pilih Barang --</option>
<?php while($b=$barang->fetch_assoc()): ?>
<option value="<?= $b['id_barang'] ?>"><?= $b['kode_barang'].' - '.$b['nama_barang'] ?></option>
<?php endwhile; ?>
</select>

<label>Gudang</label>
<select name="id_gudang" required>
<option value="">-- Pilih Gudang --</option>
<?php while($g=$gudang->fetch_assoc()): ?>
<option value="<?= $g['id_gudang'] ?>"><?= $g['nama_gudang'] ?></option>
<?php endwhile; ?>
</select>

<label>Jumlah</label>
<input type="number" name="jumlah" min="1" required>

<label>Serial Number</label>
<textarea name="serial_number"></textarea>

<button name="tambah">+ Simpan Pesanan</button>
</form>

<h3>📄 Data Pesanan</h3>
<table>
<tr>
<th>Kode</th><th>Tanggal</th><th>Admin</th><th>Barang</th><th>Gudang</th><th>Jumlah</th><th>Serial</th><th>Aksi</th>
</tr>
<?php while($r=$data->fetch_assoc()): ?>
<tr>
<td><?= $r['kode_pesanan'] ?></td>
<td><?= $r['tanggal'] ?></td>
<td><?= $r['kode_admin'].' - '.$r['nama_admin'] ?></td>
<td><?= $r['kode_barang'].' - '.$r['nama_barang'] ?><br><small><?= $r['spesifikasi'] ?></small></td>
<td><?= $r['nama_gudang'] ?></td>
<td><?= $r['jumlah'] ?></td>
<td><?= $r['serial_number'] ?></td>
<td><a class="btn-del" href="?hapus=<?= $r['id_pesanan'] ?>" onclick="return confirm('Hapus?')">Hapus</a></td>
</tr>
<?php endwhile; ?>
</table>
</div>

</body>
</html>
