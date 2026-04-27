<?php
include '../config/koneksi.php';
$id = $_GET['id']; $lat = $_GET['lat']; $lng = $_GET['lng']; $tipe = $_GET['tipe'];
$tabel = ($tipe == 'perangkat') ? 'perangkat' : 'pelanggan';
mysqli_query($koneksi, "UPDATE $tabel SET lat='$lat', lng='$lng' WHERE id='$id'");
header("Location: ../index.php");