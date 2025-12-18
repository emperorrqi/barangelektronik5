<?php
include 'koneksi.php';

// ===== Ambil ID dari URL =====
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: gudang.php");
    exit;
}

// ===== Ambil Data Gudang =====
$result = $mysqli->query("SELECT * FROM master_gudang WHERE id_gudang = $id");
if ($result->num_rows == 0) {
    header("Location: gudang.php");
    exit;
}
$gudang = $result->fetch_assoc();

// ===== Update Data Gudang =====
if (isset($_POST['update'])) {
    $nama = $_POST['nama_gudang'];
    $lokasi = $_POST['lokasi'];

    $stmt = $mysqli->prepare("UPDATE master_gudang SET nama_gudang = ?, lokasi = ? WHERE id_gudang = ?");
    $stmt->bind_param("ssi", $nama, $lokasi, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: gudang.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Gudang</title>
<style>
    body {
        font-family: "Segoe UI", Arial, sans-serif;
        background: #f5f6fa;
        margin: 0;
        padding: 30px;
    }
    .container {
        width: 600px;
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
    label {
        display: block;
        font-weight: 600;
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
        background: #28a745;
        border: none;
        color: white;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
    }
    button:hover {
        background: #218838;
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
</style>
</head>
<body>

<div class="container">
    <h2>✏ Edit Gudang</h2>

    <a href="gudang.php" class="back-btn">⬅ Kembali ke Master Gudang</a>

    <form method="POST">
        <label>Kode Gudang</label>
        <input type="text" value="<?= $gudang['kode_gudang'] ?>" disabled>

        <label>Nama Gudang</label>
        <input type="text" name="nama_gudang" value="<?= htmlspecialchars($gudang['nama_gudang']) ?>" required>

        <label>Lokasi / Alamat Gudang</label>
        <textarea name="lokasi" required><?= htmlspecialchars($gudang['lokasi']) ?></textarea>

        <button type="submit" name="update">Update Gudang</button>
    </form>
</div>

</body>
</html>
