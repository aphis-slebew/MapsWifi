<?php
session_start();
include '../config/koneksi.php';

// Pastikan yang mengakses adalah Admin dan ada ID yang dikirim
if(isset($_GET['id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    
    $id_target = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // PROTEKSI: Cegah Admin menghapus akunnya sendiri yang sedang dipakai login
    if($id_target == $_SESSION['id_user']) {
        echo "<script>alert('Gagal! Anda tidak bisa menghapus akun Anda sendiri saat sedang login.'); window.location='../users.php';</script>";
        exit;
    }

    // Eksekusi hapus dari database
    $hapus = mysqli_query($koneksi, "DELETE FROM users WHERE id='$id_target'");

    if($hapus) {
        echo "<script>alert('User berhasil dihapus secara permanen!'); window.location='../users.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus user: " . mysqli_error($koneksi) . "'); window.history.back();</script>";
    }

} else {
    // Jika bukan admin yang mencoba akses
    echo "<script>alert('Akses ilegal!'); window.location='../index.php';</script>";
}
?>