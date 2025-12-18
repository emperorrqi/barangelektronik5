<?php
include 'koneksi.php';

// ===== Generate Kode Gudang Otomatis =====
function generateKodeGudang($mysqli) {
    $r = $mysqli->query("SELECT MAX(kode_gudang) AS kode FROM master_gudang");
    $d = $r->fetch_assoc();

    if ($d['kode']) {
        $num = intval(substr($d['kode'], 3)) + 1;
        return "GDG" . str_pad($num, 3, "0", STR_PAD_LEFT);
    }
    return "GDG001";
}

// ===== Tambah Data Gudang =====
if (isset($_POST['tambah'])) {
    $kode = generateKodeGudang($mysqli);
    $nama = $_POST['nama_gudang'];
    $lokasi = $_POST['lokasi'];

    $mysqli->query("INSERT INTO master_gudang (kode_gudang, nama_gudang, lokasi)
                    VALUES ('$kode', '$nama', '$lokasi')");
    
    header("Location: gudang.php");
    exit;
}

// ===== Load Data =====
$data = $mysqli->query("SELECT * FROM master_gudang ORDER BY kode_gudang ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Master Gudang</title>

<style>
    body {
        font-family: "Segoe UI", Arial, sans-serif;
        background: #f5f6fa;
        margin: 0;
        padding: 30px;
    }
    .container {
        width: 900px;
        margin: auto;
        background: #fff;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    h2 {
        text-align: center;
        font-weight: 600;
        color: #333;
    }

    /* Form */
    .form-box {
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #ddd;
    }
    label {
        font-weight: 600;
        display: block;
        margin-bottom: 6px;
    }
    input[type=text], textarea {
        width: 98%;
        padding: 10px;
        border-radius: 6px;
        border: 1px solid #ccc;
        margin-bottom: 12px;
        font-size: 14px;
    }
    textarea {
        height: 70px;
    }
    button {
        padding: 10px 18px;
        background: #007bff;
        border: none;
        color: white;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
    }
    button:hover {
        background: #005fcc;
    }
    .back-btn {
        display: inline-block;
        margin-bottom: 15px;
        color: white;
        background: #6c757d;
        padding: 8px 14px;
        border-radius: 6px;
        text-decoration: none;
    }
    .back-btn:hover {
        background: #5a6268;
    }

    /* Table */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    th {
        background: #0d6efd;
        color: white;
        padding: 12px;
        text-align: center;
        font-size: 14px;
    }
    td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #eaeaea;
        font-size: 14px;
    }
    tr:hover td {
        background: #f3f8ff;
    }
    .edit {
        color: #ffc107;
        font-weight: bold;
        text-decoration: none;
    }
    .edit:hover {
        text-decoration: underline;
    }
</style>

</head>
<body>

<div class="container">
    <h2>🏭 Master Gudang</h2>

    <a href="index.php" class="back-btn">⬅ Kembali ke Menu Utama</a>

    <div class="form-box">
        <form method="POST">

            <label>Kode Gudang (Otomatis)</label>
            <input type="text" value="<?= generateKodeGudang($mysqli) ?>" disabled>

            <label>Nama Gudang</label>
            <input type="text" name="nama_gudang" required>

            <label>Lokasi / Alamat Gudang</label>
            <textarea name="lokasi" required></textarea>

            <button type="submit" name="tambah">+ Tambah Gudang</button>
        </form>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Kode Gudang</th>
            <th>Nama Gudang</th>
            <th>Lokasi</th>
            <th>Aksi</th>
        </tr>

        <?php while ($row = $data->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id_gudang'] ?></td>
            <td><?= $row['kode_gudang'] ?></td>
            <td><?= $row['nama_gudang'] ?></td>
            <td><?= nl2br($row['lokasi']) ?></td>
            <td>
                <a class="edit" href="edit_gudang.php?id=<?= $row['id_gudang'] ?>">Edit</a>
            </td>
        </tr>
        <?php } ?>

    </table>
</div>

</body>
</html>
