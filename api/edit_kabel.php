<?php
session_start();
include '../config/koneksi.php';

if(isset($_POST['edit_kabel'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $warna = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $tipe_garis = mysqli_real_escape_string($koneksi, $_POST['tipe_garis']);
    
    // Tentukan ketebalan otomatis berdasarkan tipe garis
    $ketebalan = ($tipe_garis == 'tebal') ? 6 : 3;

    $query = mysqli_query($koneksi, "UPDATE jalur_kabel SET nama='$nama', warna='$warna', ketebalan='$ketebalan', tipe_garis='$tipe_garis' WHERE id='$id'");

    if($query) {
        header("Location: ../index.php");
        exit;
    } else {
        echo "Error database: " . mysqli_error($koneksi);
    }
}
?>