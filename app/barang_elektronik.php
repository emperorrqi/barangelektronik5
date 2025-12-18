<?php 
include 'koneksi.php';

// ===========================
// Generate Kode Barang Otomatis
// ===========================
function generateKodeBarang($mysqli) {
    $result = $mysqli->query("SELECT MAX(kode_barang) AS max_kode FROM master_barang_elektronik");
    if(!$result){ die("Query Error: ".$mysqli->error); }
    $data = $result->fetch_assoc();

    if ($data['max_kode']) {
        $num = (int) substr($data['max_kode'], 3) + 1;
        return "BRG" . str_pad($num, 3, "0", STR_PAD_LEFT);
    } else {
        return "BRG001";
    }
}

$kodeOtomatis = generateKodeBarang($mysqli);

// ===========================
// Tambah Data
// ===========================
if (isset($_POST['tambah'])) {
    $kode = $_POST['kode_barang'];
    $nama = $_POST['nama_barang'];
    $spek = $_POST['spesifikasi'];
    $kategori = $_POST['kategori'];

    $stmt = $mysqli->prepare("INSERT INTO master_barang_elektronik (kode_barang, nama_barang, spesifikasi, kategori) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $kode, $nama, $spek, $kategori);
    $stmt->execute();
    $stmt->close();

    header("Location: barang_elektronik.php");
    exit;
}

// ===========================
// Ambil Data
// ===========================
$data = $mysqli->query("SELECT * FROM master_barang_elektronik ORDER BY id_barang DESC");
if(!$data){ die("Query Error: ".$mysqli->error); }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Master Barang Elektronik</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            margin:0; padding:30px;
            background: #f3f4f6;
            color: #111827;
        }

        .container { max-width: 1100px; margin:auto; }

        .card {
            background:white;
            border-radius:12px;
            padding:25px 30px;
            box-shadow:0 6px 20px rgba(0,0,0,0.08);
        }

        h2 {
            text-align:center;
            margin-bottom:25px;
            font-size:24px;
            font-weight:700;
            color:#1f2937;
        }

        .btn-back {
            display:inline-block;
            padding:8px 16px;
            background:#28a745;
            color:white;
            text-decoration:none;
            border-radius:6px;
            font-size:14px;
            margin-bottom:20px;
            transition:0.2s;
        }
        .btn-back:hover { background:#218838; }

        form {
            display:grid;
            grid-template-columns: 1fr 1fr;
            gap:20px;
            margin-bottom:30px;
        }

        form label {
            display:block;
            font-weight:600;
            margin-bottom:6px;
            color:#374151;
        }

        form input, form select {
            width:100%;
            padding:12px;
            border-radius:8px;
            border:1px solid #d1d5db;
            font-size:14px;
            transition: 0.2s;
        }

        form input:focus, form select:focus {
            outline:none;
            border-color:#2563eb;
            box-shadow:0 0 0 2px rgba(37,99,235,0.2);
        }

        .full { grid-column: span 2; }

        button {
            background:#2563eb;
            color:white;
            border:none;
            border-radius:8px;
            padding:12px;
            font-weight:600;
            cursor:pointer;
            transition:0.2s;
        }

        button:hover { background:#1d4ed8; }

        table {
            width:100%;
            border-collapse:collapse;
            font-size:14px;
            overflow-x:auto;
        }

        th, td {
            padding:14px;
            text-align:left;
            border-bottom:1px solid #e5e7eb;
        }

        th {
            background:#f3f4f6;
            color:#374151;
            font-weight:600;
        }

        a.edit {
            color:#2563eb;
            font-weight:600;
            text-decoration:none;
            transition:0.2s;
        }

        a.edit:hover { text-decoration:underline; }

        @media (max-width:768px){
            form { grid-template-columns: 1fr; }
            .full { grid-column: span 1; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h2>📦 Master Barang Elektronik</h2>

        <a href="index.php" class="btn-back">← Kembali ke Halaman Utama</a>

        <!-- FORM INPUT -->
        <form method="post">
            <div class="full">
                <label>Kode Barang (Otomatis)</label>
                <input type="text" name="kode_barang" value="<?= $kodeOtomatis ?>" readonly>
            </div>

            <div class="full">
                <label>Nama Barang</label>
                <input type="text" name="nama_barang" placeholder="Contoh: Lenovo ThinkPad X280" required>
            </div>

            <div>
                <label>Spesifikasi</label>
                <select name="spesifikasi" required>
                    <option value="">-- Pilih Spesifikasi --</option>
                    <option value="High Tier">High Tier</option>
                    <option value="Mid Tier">Mid Tier</option>
                </select>
            </div>

            <div>
                <label>Kategori</label>
                <select name="kategori" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="Laptop">Laptop</option>
                    <option value="PC">PC</option>
                    <option value="Modem">Modem</option>
                </select>
            </div>

            <div class="full" style="text-align:right;">
                <button name="tambah">+ Tambah Barang</button>
            </div>
        </form>

        <!-- TABEL DATA -->
        <table>
            <tr>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Spesifikasi</th>
                <th>Kategori</th>
                <th>Aksi</th>
            </tr>

            <?php while ($row = $data->fetch_assoc()): ?>
            <tr>
                <td><?= $row['kode_barang'] ?></td>
                <td><?= $row['nama_barang'] ?></td>
                <td><?= $row['spesifikasi'] ?></td>
                <td><?= $row['kategori'] ?></td>
                <td>
                    <a class="edit" href="edit_barang_elektronik.php?id=<?= $row['id_barang'] ?>">Edit</a>
                </td>
            </tr>
            <?php endwhile; ?>

        </table>
    </div>
</div>

</body>
</html>
