<?php
include 'koneksi.php';

// ========================
// Generate Kode Driver Otomatis
// ========================
function generateKodeDriver($mysqli) {
    $result = $mysqli->query("SELECT MAX(kode_driver) AS kode FROM master_driver");
    $row = $result->fetch_assoc();

    if ($row['kode']) {
        $num = (int) substr($row['kode'], 3) + 1;
        return "DRV" . str_pad($num, 3, "0", STR_PAD_LEFT);
    }
    return "DRV001";
}

// ========================
// Tambah Driver
// ========================
if (isset($_POST['tambah'])) {
    $kode = generateKodeDriver($mysqli);
    $nama = $_POST['nama_driver'];
    $hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];

    $mysqli->query("INSERT INTO master_driver (kode_driver, nama_driver, no_hp, alamat)
                    VALUES ('$kode', '$nama', '$hp', '$alamat')");
    header("Location: driver.php");
    exit;
}

// ========================
// Update Driver
// ========================
if (isset($_POST['update'])) {
    $id = $_POST['id_driver'];
    $nama = $_POST['nama_driver'];
    $hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];

    $mysqli->query("UPDATE master_driver 
                    SET nama_driver='$nama', no_hp='$hp', alamat='$alamat'
                    WHERE id_driver=$id");

    header("Location: driver.php");
    exit;
}

// Ambil data driver
$data = $mysqli->query("SELECT * FROM master_driver ORDER BY id_driver DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Master Driver</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 30px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 700;
            color: #1f2937;
        }
        .btn {
            padding: 10px 16px;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }
        .btn-back { background:#6c757d; margin-right:10px; }
        .btn-back:hover { background:#495057; }
        .btn-add { background:#3b82f6; }
        .btn-add:hover { background:#2563eb; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }
        th { 
            background: #f3f4f6; 
            font-weight: 600;
        }
        a.edit {
            color: #10b981;
            font-weight: 600;
            text-decoration: none;
        }
        a.edit:hover { text-decoration: underline; }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            width: 450px;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 12px;
        }
    </style>
</head>

<body>

<div class="container">
    <h2>🚚 Master Driver</h2>

    <!-- Tombol Kembali & Tambah -->
    <a href="index.php" class="btn btn-back">⬅ Kembali ke Halaman Utama</a>
    <button class="btn btn-add" onclick="openTambah()">+ Tambah Driver</button>

    <table>
        <tr>
            <th>Kode</th>
            <th>Nama Driver</th>
            <th>No HP</th>
            <th>Alamat</th>
            <th>Aksi</th>
        </tr>

        <?php while ($row = $data->fetch_assoc()): ?>
        <tr>
            <td><?= $row['kode_driver'] ?></td>
            <td><?= $row['nama_driver'] ?></td>
            <td><?= $row['no_hp'] ?></td>
            <td><?= $row['alamat'] ?></td>
            <td>
                <a class="edit" href="#" onclick="openEdit(
                    '<?= $row['id_driver'] ?>',
                    '<?= $row['nama_driver'] ?>',
                    '<?= $row['no_hp'] ?>',
                    '<?= $row['alamat'] ?>'
                )">Edit</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- =================== MODAL TAMBAH =================== -->
<div class="modal" id="modalTambah">
    <div class="modal-content">
        <h3>Tambah Driver</h3>
        <form method="post">
            <input type="text" name="nama_driver" placeholder="Nama Driver" required>
            <input type="text" name="no_hp" placeholder="No HP">
            <textarea name="alamat" placeholder="Alamat"></textarea>

            <button class="btn btn-add" name="tambah">Simpan</button>
            <button class="btn" type="button" onclick="closeTambah()" style="background:#6b7280;">Batal</button>
        </form>
    </div>
</div>

<!-- =================== MODAL EDIT =================== -->
<div class="modal" id="modalEdit">
    <div class="modal-content">
        <h3>Edit Driver</h3>
        <form method="post">
            <input type="hidden" name="id_driver" id="edit_id">
            <input type="text" name="nama_driver" id="edit_nama" required>
            <input type="text" name="no_hp" id="edit_hp">
            <textarea name="alamat" id="edit_alamat"></textarea>

            <button class="btn btn-add" name="update">Update</button>
            <button class="btn" type="button" onclick="closeEdit()" style="background:#6b7280;">Batal</button>
        </form>
    </div>
</div>

<script>
function openTambah() {
    document.getElementById('modalTambah').style.display = 'flex';
}
function closeTambah() {
    document.getElementById('modalTambah').style.display = 'none';
}

function openEdit(id, nama, hp, alamat) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_hp').value = hp;
    document.getElementById('edit_alamat').value = alamat;

    document.getElementById('modalEdit').style.display = 'flex';
}
function closeEdit() {
    document.getElementById('modalEdit').style.display = 'none';
}
</script>

</body>
</html>
