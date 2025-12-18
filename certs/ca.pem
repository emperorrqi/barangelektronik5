<?php
$DB_HOST = getenv("DB_HOST");
$DB_USER = getenv("DB_USER");
$DB_PASS = getenv("DB_PASS");
$DB_NAME = getenv("DB_NAME");
$DB_PORT = getenv("DB_PORT");
$DB_SSL_CA = getenv("DB_SSL_CA"); // path ke ca.pem

$mysqli = mysqli_init();

// Set SSL options
$mysqli->ssl_set(NULL, NULL, $DB_SSL_CA, NULL, NULL);
$mysqli->real_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT, NULL, MYSQLI_CLIENT_SSL);

if ($mysqli->connect_errno) {
    die("Koneksi ke database gagal: " . $mysqli->connect_error);
}
?>
