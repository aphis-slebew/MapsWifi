<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_maps";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

function catat_log($koneksi, $user_id, $aksi) {
    $user_id = mysqli_real_escape_string($koneksi, $user_id);
    $aksi = mysqli_real_escape_string($koneksi, $aksi);
    mysqli_query($koneksi, "INSERT INTO log_aktivitas (user_id, aksi) VALUES ('$user_id', '$aksi')");
}
?>