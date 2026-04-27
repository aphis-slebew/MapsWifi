<?php
session_start();
include '../config/koneksi.php';

if(isset($_POST['simpan_kabel'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $warna = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $tipe_garis = mysqli_real_escape_string($koneksi, $_POST['tipe_garis']);
    $koordinat = mysqli_real_escape_string($koneksi, $_POST['koordinat']);

    // Otomatis menyesuaikan ketebalan
    $ketebalan = ($tipe_garis == 'tebal') ? 6 : 3;

    $query = mysqli_query($koneksi, "INSERT INTO jalur_kabel (nama, koordinat, warna, ketebalan, tipe_garis) VALUES ('$nama', '$koordinat', '$warna', '$ketebalan', '$tipe_garis')");

    if($query) {
        header("Location: ../index.php");
        exit;
    } else {
        echo "Error database: " . mysqli_error($koneksi);
    }
}
?>