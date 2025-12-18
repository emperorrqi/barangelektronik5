<?php
include 'koneksi.php';

// ==========================
// Generate Kode Masuk Otomatis
// ==========================
function generateKodeMasuk($mysqli) {
    $result = $mysqli->query("SELECT MAX(kode_masuk) AS max_kode FROM trx_barang_masuk");
    $row = $result->fetch_assoc();
    $last = $row['max_kode'];

    if ($last) {
        $num = (int)substr($last, 3) + 1;
        return "BMS" . str_pad($num, 3, "0", STR_PAD_LEFT);
    } else {
        return "BMS001";
    }
}

$kode_masuk = generateKodeMasuk($mysqli);

// ==========================
// Tambah Barang Masuk
// ==========================
if (isset($_POST['tambah'])) {
    $kode      = $_POST['kode_masuk'];
    $tanggal   = $_POST['tanggal'];
    $id_admin  = $_POST['id_admin'];
    $id_barang = $_POST['id_barang'];
    $id_vendor = $_POST['id_vendor'];
    $jumlah    = $_POST['jumlah'];

    $stmt = $mysqli->prepare("
        INSERT INTO trx_barang_masuk 
        (kode_masuk, tanggal, id_admin, id_barang, id_vendor, jumlah)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssiiii", $kode, $tanggal, $id_admin, $id_barang, $id_vendor, $jumlah);
    $stmt->execute();
    $stmt->close();

    header("Location: entry_barang_masuk.php");
    exit;
}

// ==========================
// Hapus Data Barang Masuk
// ==========================
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $stmt = $mysqli->prepare("DELETE FROM trx_barang_masuk WHERE id_masuk=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: entry_barang_masuk.php");
    exit;
}

// ==========================
// Ambil Data Barang Masuk + Retur
// ==========================
$data = $mysqli->query("
    SELECT 
        m.*,
        b.kode_barang, b.nama_barang, b.spesifikasi,
        v.nama_vendor,
        a.kode_admin, a.nama_admin,

        IFNULL(SUM(r.jumlah),0) AS jumlah_retur,
        (m.jumlah - IFNULL(SUM(r.jumlah),0)) AS sisa_barang

    FROM trx_barang_masuk m
    LEFT JOIN master_barang_elektronik b ON m.id_barang = b.id_barang
    LEFT JOIN master_vendor v ON m.id_vendor = v.id_vendor
    LEFT JOIN master_administrasi a ON m.id_admin = a.id_admin
    LEFT JOIN trx_retur r ON m.id_barang = r.id_barang

    GROUP BY m.id_masuk
    ORDER BY m.id_masuk DESC
");

// Dropdown
$barang = $mysqli->query("SELECT * FROM master_barang_elektronik ORDER BY nama_barang ASC");
$vendor = $mysqli->query("SELECT * FROM master_vendor ORDER BY nama_vendor ASC");
$admin  = $mysqli->query("SELECT * FROM master_administrasi ORDER BY nama_admin ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>📥 Entry Barang Masuk</title>
    <style>
        body { font-family: Arial; background:#eef0f2; padding:25px; }
        .card { background:white; padding:25px; border-radius:12px; width:900px; margin:auto; box-shadow:0 3px 15px rgba(0,0,0,0.1); }
        input, select { width:100%; padding:10px; margin:7px 0 14px; border-radius:6px; border:1px solid #bbb; }
        button { padding:12px 20px; border:none; background:#007bff; color:white; border-radius:6px; cursor:pointer; }
        button:hover { background:#0056b3; }
        table { width:100%; border-collapse:collapse; margin-top:25px; }
        th,td { border:1px solid #ddd; padding:10px; }
        th { background:#007bff; color:white; }
        .btn-del { color:red; font-weight:bold; text-decoration:none; }
        .btn-del:hover { text-decoration:underline; }
        .btn-back, .btn-print {
            display:inline-block; margin-bottom:15px;
            padding:10px 18px; color:white;
            border-radius:6px; text-decoration:none;
        }
        .btn-back { background:#6c757d; }
        .btn-print { background:#28a745; }
    </style>
</head>
<body>

<div class="card">
    <h2>📥 Entry Barang Masuk</h2>

    <a href="index.php" class="btn-back">⬅ Kembali</a>
    <a href="cetak_barang_masuk.php" target="_blank" class="btn-print">🖨️ Cetak</a>

    <!-- FORM INPUT -->
    <form method="post">
        <label>Kode Masuk</label>
        <input type="text" name="kode_masuk" value="<?= $kode_masuk ?>" readonly>

        <label>Tanggal</label>
        <input type="date" name="tanggal" required>

        <label>Admin</label>
        <select name="id_admin" required>
            <option value="">-- Pilih Admin --</option>
            <?php while($a = $admin->fetch_assoc()): ?>
                <option value="<?= $a['id_admin'] ?>">
                    <?= $a['kode_admin'] ?> - <?= $a['nama_admin'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Barang</label>
        <select name="id_barang" required>
            <option value="">-- Pilih Barang --</option>
            <?php while($b = $barang->fetch_assoc()): ?>
                <option value="<?= $b['id_barang'] ?>">
                    <?= $b['kode_barang'] ?> - <?= $b['nama_barang'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Vendor</label>
        <select name="id_vendor" required>
            <option value="">-- Pilih Vendor --</option>
            <?php while($v = $vendor->fetch_assoc()): ?>
                <option value="<?= $v['id_vendor'] ?>"><?= $v['nama_vendor'] ?></option>
            <?php endwhile; ?>
        </select>

        <label>Jumlah Masuk</label>
        <input type="number" name="jumlah" min="1" required>

        <button name="tambah">+ Tambah Barang Masuk</button>
    </form>

    <!-- TABEL DATA -->
    <h3>📄 Data Barang Masuk</h3>
    <table>
        <tr>
            <th>Kode</th>
            <th>Tanggal</th>
            <th>Admin</th>
            <th>Barang</th>
            <th>Vendor</th>
            <th>Masuk</th>
            <th>Retur</th>
            <th>Sisa</th>
            <th>Aksi</th>
        </tr>

        <?php while ($row = $data->fetch_assoc()): ?>
        <tr>
            <td><?= $row['kode_masuk'] ?></td>
            <td><?= $row['tanggal'] ?></td>
            <td><?= $row['kode_admin'] ?> - <?= $row['nama_admin'] ?></td>
            <td>
                <?= $row['kode_barang'] ?> - <?= $row['nama_barang'] ?>
                <br><small><?= $row['spesifikasi'] ?></small>
            </td>
            <td><?= $row['nama_vendor'] ?></td>
            <td><?= $row['jumlah'] ?></td>
            <td><?= $row['jumlah_retur'] ?></td>
            <td><strong><?= $row['sisa_barang'] ?></strong></td>
            <td>
                <a class="btn-del" 
                   href="?hapus=<?= $row['id_masuk'] ?>" 
                   onclick="return confirm('Hapus data ini?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
