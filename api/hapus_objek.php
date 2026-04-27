<?php
include '../config/koneksi.php';
$id = $_GET['id']; $tipe = $_GET['tipe'];
if($tipe == 'kabel') mysqli_query($koneksi, "DELETE FROM jalur_kabel WHERE id='$id'");
else if($tipe == 'perangkat') mysqli_query($koneksi, "UPDATE perangkat SET lat=NULL, lng=NULL WHERE id='$id'");
else if($tipe == 'pelanggan') mysqli_query($koneksi, "UPDATE pelanggan SET lat=NULL, lng=NULL WHERE id='$id'");
header("Location: ../index.php");
