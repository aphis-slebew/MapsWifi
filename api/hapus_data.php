<?php
session_start();
// Gunakan ../ untuk memanggil koneksi karena file ini ada di dalam folder api
include '../config/koneksi.php';

if(isset($_GET['id']) && isset($_GET['tipe'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $tipe = $_GET['tipe'];

    if($tipe == 'pelanggan') {
        // 1. Cari nama foto pelanggan ini dulu
        $q_foto = mysqli_query($koneksi, "SELECT foto FROM pelanggan WHERE id='$id'");
        if(mysqli_num_rows($q_foto) > 0) {
            $d_foto = mysqli_fetch_assoc($q_foto);
            // 2. Hapus file foto fisik dari folder uploads (jika ada)
            if(!empty($d_foto['foto']) && file_exists('../uploads/'.$d_foto['foto'])) {
                unlink('../uploads/'.$d_foto['foto']);
            }
        }
        
        // 3. Hapus data dari database
        $hapus = mysqli_query($koneksi, "DELETE FROM pelanggan WHERE id='$id'");
        
        if($hapus) {
            echo "<script>alert('Data pelanggan berhasil dihapus!'); window.location='../pelanggan.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus data!'); window.location='../pelanggan.php';</script>";
        }
    }
} else {
    echo "<script>window.location='../pelanggan.php';</script>";
}
?>