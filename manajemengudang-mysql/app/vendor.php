<?php
include 'koneksi.php';

// Fungsi untuk generate kode vendor otomatis
function generateKodeVendor($mysqli) {
    $result = $mysqli->query("SELECT MAX(kode_vendor) AS max_kode FROM vendor");
    $data = $result->fetch_assoc();
    $max_kode = $data['max_kode'];

    if ($max_kode) {
        $urutan = (int)substr($max_kode, 3) + 1;
    } else {
        $urutan = 1;
    }

    return 'VND' . str_pad($urutan, 3, '0', STR_PAD_LEFT);
}

// Handle Hapus
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $mysqli->prepare("DELETE FROM vendor WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: vendor.php");
    exit;
}

// Handle Tambah / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $telepon = $_POST['telepon'];

    if (isset($_POST['id'])) {
        // Edit
        $id = $_POST['id'];
        $stmt = $mysqli->prepare("UPDATE vendor SET nama=?, alamat=?, telepon=? WHERE id=?");
        $stmt->bind_param("sssi", $nama, $alamat, $telepon, $id);
    } else {
        // Tambah
        $kode_vendor = generateKodeVendor($mysqli);
        $stmt = $mysqli->prepare("INSERT INTO vendor (kode_vendor, nama, alamat, telepon) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $kode_vendor, $nama, $alamat, $telepon);
    }

    $stmt->execute();
    header("Location: vendor.php");
    exit;
}

// Ambil data untuk edit
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $stmt = $mysqli->prepare("SELECT * FROM vendor WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
}

$result = $mysqli->query("SELECT * FROM vendor ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Master Data Vendor</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        form {
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            text-align: left;
        }

        a.action {
            color: #007bff;
            margin-right: 10px;
            text-decoration: none;
        }

        a.action:hover {
            text-decoration: underline;
        }

        .delete-link {
            color: #e74c3c;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            font-weight: bold;
            color: #555;
        }

        .back-link:hover {
            color: #000;
        }

        @media (max-width: 600px) {
            th, td {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🏢 Master Data: Vendor</h2>

    <form method="post">
        <?php if ($edit_mode && $edit_data): ?>
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
            <label for="nama">Nama Vendor</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($edit_data['nama']) ?>" required>

            <label for="alamat">Alamat</label>
            <textarea name="alamat" rows="3"><?= htmlspecialchars($edit_data['alamat']) ?></textarea>

            <label for="telepon">Telepon</label>
            <input type="text" name="telepon" value="<?= htmlspecialchars($edit_data['telepon']) ?>">

            <button type="submit">💾 Simpan Perubahan</button>
            <a href="vendor.php" style="margin-left:10px;">❌ Batal</a>
        <?php else: ?>
            <label for="nama">Nama Vendor</label>
            <input type="text" name="nama" required>

            <label for="alamat">Alamat</label>
            <textarea name="alamat" rows="3"></textarea>

            <label for="telepon">Telepon</label>
            <input type="text" name="telepon">

            <button type="submit">+ Tambah Vendor</button>
        <?php endif; ?>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Kode</th>
            <th>Nama Vendor</th>
            <th>Alamat</th>
            <th>Telepon</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['kode_vendor'] ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['alamat']) ?></td>
                <td><?= htmlspecialchars($row['telepon']) ?></td>
                <td>
                    <a class="action" href="vendor.php?edit=<?= $row['id'] ?>">✏️ Edit</a>
                    <a class="action delete-link" href="vendor.php?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus vendor ini?')">🗑️ Hapus</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <a href="index.php" class="back-link">← Kembali ke Menu</a>
</div>

</body>
</html>
