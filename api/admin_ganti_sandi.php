<?php
session_start();
include '../config/koneksi.php';

// Pastikan yang nge-post ini beneran admin
if(isset($_POST['ganti_sandi']) && $_SESSION['role'] === 'admin') {
    $id_user = mysqli_real_escape_string($koneksi, $_POST['id_user']);
    
    // CATATAN: Ubah md5() jika di sistem login kamu pakai enkripsi lain, 
    // atau hilangkan md5() jika kamu simpan sandi tanpa enkripsi (plain text).
    $password_baru = md5($_POST['password_baru']); 

    $query = mysqli_query($koneksi, "UPDATE users SET password='$password_baru' WHERE id='$id_user'");

    if($query) {
        echo "<script>alert('Password user berhasil diubah!'); window.location='../users.php';</script>";
    } else {
        echo "<script>alert('Gagal mengubah password!'); window.history.back();</script>";
    }
}
?>