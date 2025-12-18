<?php
include 'koneksi.php';

// ==========================
// Fungsi Generate Kode Basterima Otomatis
// ==========================
function generateKodeBasterima($mysqli) {
    $result = $mysqli->query("SELECT MAX(kode_basterima) AS max_kode FROM trx_berita_serah_terima");
    $data = $result->fetch_assoc();
    $maxKode = $data['max_kode'];

    if ($maxKode) {
        $num = (int) substr($maxKode, 3); // 'BST' = 3 karakter
        $num++;
        return "BST" . str_pad($num, 3, "0", STR_PAD_LEFT);
    } else {
        return "BST001";
    }
}

$kodeBasterimaOtomatis = generateKodeBasterima($mysqli);
$notif = '';

// ==========================
// Tambah Data Berita Serah Terima
// ==========================
if (isset($_POST['tambah'])) {
    $kode_basterima = $_POST['kode_basterima'];
    $tanggal = $_POST['tanggal'];
    $penerima = $_POST['penerima'];
    $id_barang = $_POST['id_barang'];
    $jumlah = (int)$_POST['jumlah'];
    $sn_perangkat = $_POST['sn_perangkat']; // FIELD BARU

    // Cek jumlah barang dari entry pesanan
    $q = $mysqli->query("SELECT SUM(jumlah) AS total_pesanan FROM trx_barang_pesanan WHERE id_barang = $id_barang");
    $row = $q->fetch_assoc();
    $totalPesanan = (int)$row['total_pesanan'];

    // Cek jumlah yang sudah terkirim
    $q2 = $mysqli->query("SELECT SUM(jumlah) AS total_terkirim FROM trx_berita_serah_terima WHERE id_barang = $id_barang");
    $row2 = $q2->fetch_assoc();
    $totalTerkirim = (int)$row2['total_terkirim'];

    $sisa = $totalPesanan - $totalTerkirim;

    if ($jumlah > $sisa) {
        $notif = "⚠ Jumlah melebihi stok/pesanan tersedia ($sisa unit tersisa)";
    } else {
        $stmt = $mysqli->prepare("
            INSERT INTO trx_berita_serah_terima 
            (kode_basterima, tanggal, penerima, id_barang, jumlah, sn_perangkat)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssiss", $kode_basterima, $tanggal, $penerima, 
                          $id_barang, $jumlah, $sn_perangkat);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: entry_basterima.php?success=1");
            exit;
        } else {
            $notif = "Error: " . $mysqli->error;
        }
    }
}

// Ambil barang
$barang = $mysqli->query("
    SELECT b.id_barang, b.kode_barang, b.nama_barang, 
           SUM(p.jumlah) AS total_pesanan,
           IFNULL(SUM(bs.jumlah),0) AS total_terkirim
    FROM trx_barang_pesanan p
    JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    LEFT JOIN trx_berita_serah_terima bs ON b.id_barang = bs.id_barang
    GROUP BY b.id_barang
    HAVING total_pesanan > total_terkirim
    ORDER BY b.nama_barang ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Entry Berita Serah Terima</title>
<style>
body { font-family: Arial; background: #eef1f7; padding: 25px; }
.container { width: 750px; margin: auto; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 3px 15px rgba(0,0,0,0.1); }
h2,h3 { margin-bottom: 10px; color: #2c3e50; }
label { font-weight: bold; margin-top: 10px; display: block; }
input, select { width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #cfcfcf; }
button { background: #8e44ad; padding: 10px 18px; color: white; border: none; margin-top: 12px; border-radius: 6px; cursor: pointer; font-weight: bold; transition: 0.2s; }
button:hover { background: #732d91; }
table { width: 100%; margin-top: 25px; border-collapse: collapse; font-size: 14px; }
th { background: #8e44ad; color: white; padding: 9px; }
td { padding: 8px; border: 1px solid #ddd; text-align: center; }
.alert-success { background: #2ecc71; color: white; padding: 10px; margin-bottom: 15px; border-radius: 6px; }
.alert-warning { background: #e74c3c; color: white; padding: 10px; margin-bottom: 15px; border-radius: 6px; }
.btn-back, .btn-print { display:inline-block; margin-bottom:15px; padding:8px 16px; color:white; border-radius:6px; text-decoration:none; font-weight:bold; }
.btn-back { background:#6c757d; }
.btn-back:hover { background:#495057; }
.btn-print { background:#28a745; }
.btn-print:hover { background:#218838; }
@media print { button, .btn-back, .btn-print { display:none; } body { background:#fff; } }
</style>
</head>
<body>
<div class="container">

    <h2>Entry Berita Serah Terima</h2>

    <a href="index.php" class="btn-back">⬅ Kembali ke Halaman Utama</a>
    <a href="cetak_basterima.php" target="_blank" class="btn-print">🖨️ Cetak Basterima Hari ini</a>

    <?php if (!empty($notif)) echo "<div class='alert-warning'>$notif</div>"; ?>
    <?php if (isset($_GET['success'])) echo "<div class='alert-success'>✔ Data berhasil disimpan!</div>"; ?>

    <form method="POST">
        <label>Kode Basterima</label>
        <input type="text" name="kode_basterima" value="<?= $kodeBasterimaOtomatis ?>" readonly>

        <label>Tanggal</label>
        <input type="date" name="tanggal" required>

        <label>Penerima</label>
        <input type="text" name="penerima" placeholder="Nama penerima" required>

        <label>Barang (dari pesanan)</label>
        <select name="id_barang" required>
            <option value="">-- Pilih Barang --</option>
            <?php while ($b = $barang->fetch_assoc()): 
                $stok = $b['total_pesanan'] - $b['total_terkirim'];
            ?>
                <option value="<?= $b['id_barang'] ?>">
                    <?= $b['nama_barang'] ?> (<?= $b['kode_barang'] ?>) — Tersisa <?= $stok ?> unit
                </option>
            <?php endwhile; ?>
        </select>

        <label>Jumlah barang keluar</label>
        <input type="number" name="jumlah" min="1" required>

        <label>SN Perangkat</label>
        <input type="text" name="sn_perangkat" placeholder="Isi SN Perangkat" required>

        <button type="submit" name="tambah">Simpan</button>
    </form>

    <hr>

    <h3>Data Berita Serah Terima</h3>
    <table>
        <tr>
            <th>Kode Basterima</th>
            <th>Tanggal</th>
            <th>Penerima</th>
            <th>Barang</th>
            <th>Jumlah</th>
            <th>SN Perangkat</th>
        </tr>
        <?php
        $sql = "SELECT t.*, b.nama_barang, b.kode_barang
                FROM trx_berita_serah_terima t
                JOIN master_barang_elektronik b ON t.id_barang = b.id_barang
                ORDER BY id_serah DESC";
        $result = $mysqli->query($sql);
        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?= $row['kode_basterima'] ?></td>
            <td><?= $row['tanggal'] ?></td>
            <td><?= $row['penerima'] ?></td>
            <td><?= $row['nama_barang'] ?> (<?= $row['kode_barang'] ?>)</td>
            <td><?= $row['jumlah'] ?></td>
            <td><?= $row['sn_perangkat'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

</div>
</body>
</html>
