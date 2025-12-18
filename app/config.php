<?php
// Ambil environment variables
$host     = getenv('DB_HOST');
$port     = getenv('DB_PORT');
$user     = getenv('DB_USER');
$pass     = getenv('DB_PASS');
$db       = getenv('DB_NAME');
$ssl_ca   = getenv('DB_SSL_CA'); // path ke ca.pem

// Inisialisasi koneksi
$koneksi = mysqli_init();

// Set SSL options
$koneksi->ssl_set(NULL, NULL, $ssl_ca, NULL, NULL);

// Koneksi ke MySQL Aiven
$koneksi->real_connect($host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL);

if ($koneksi->connect_error) {
    die("Gagal koneksi ke database: " . $koneksi->connect_error);
}

// Optional: set charset
$koneksi->set_charset("utf8mb4");
?>
