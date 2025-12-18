<?php
include 'koneksi.php';

// Nama file CSV
$filename = "export_serial_number_" . date('Ymd_His') . ".csv";

// Header download
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=$filename");
header("Content-Type: application/csv; charset=utf-8");

$output = fopen("php://output", "w");

// Ambil semua pesanan untuk membuat header dinamis
$pesanan = [];
$rPesanan = $mysqli->query("SELECT DISTINCT kode_pesanan FROM trx_barang_pesanan ORDER BY kode_pesanan ASC");
while ($p = $rPesanan->fetch_assoc()) {
    $pesanan[] = $p['kode_pesanan'];  // psn01, psn02, psn03, ...
}

// ================================
// Tulis header CSV
// ================================
$header = array_merge(['serial_number'], $pesanan);
fputcsv($output, $header);

// ================================
// Ambil semua serial number unik
// ================================
$sn_list = [];
$rSN = $mysqli->query("SELECT serial_number, kode_pesanan FROM trx_barang_pesanan ORDER BY serial_number ASC");

while ($row = $rSN->fetch_assoc()) {
    $sn = $row['serial_number'];
    $kode_pesanan = $row['kode_pesanan'];

    // Jika serial belum ada, buat array dasar
    if (!isset($sn_list[$sn])) {
        $sn_list[$sn] = array_fill_keys($pesanan, "");
        $sn_list[$sn]['serial_number'] = $sn;
    }

    // Isi nomor urut berdasarkan pesanan
    // Hitung urutan SN dalam pesanan tersebut
    $count = $mysqli->query("
        SELECT COUNT(*) AS total 
        FROM trx_barang_pesanan 
        WHERE kode_pesanan = '$kode_pesanan' 
        AND serial_number <= '$sn'
    ")->fetch_assoc()['total'];

    $sn_list[$sn][$kode_pesanan] = $count;
}

// ================================
// Tulis ke CSV
// ================================
foreach ($sn_list as $sn => $cols) {
    // Urutkan sesuai header
    $rowOut = [$sn];
    foreach ($pesanan as $kd) {
        $rowOut[] = $cols[$kd];
    }
    fputcsv($output, $rowOut);
}

fclose($output);
exit;
?>
