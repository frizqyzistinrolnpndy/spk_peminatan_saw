<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "db_spk_mapel";
$port = 3307; // Sesuaikan port kamu (hapus parameter port jika pakai default 3306)

$koneksi = mysqli_connect($host, $user, $pass, $db, $port);
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>