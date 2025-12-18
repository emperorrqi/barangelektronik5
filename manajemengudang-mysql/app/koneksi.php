<?php
$host = 'db';
$user = 'user';
$pass = 'password';
$db = 'manajemengudang';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die("Gagal koneksi ke database: " . $mysqli->connect_error);
}
?>
