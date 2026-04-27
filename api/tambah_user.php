<?php
session_start();
include '../config/koneksi.php';

// Pastikan yang mengakses ini adalah Admin
if(isset($_POST['tambah_user']) && $_SESSION['role'] === 'admin') {
    
    // Ambil data dari form
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $role     = mysqli_real_escape_string($koneksi, $_POST['role']);
    
    // Enkripsi password menggunakan MD5
    $password = md5($_POST['password']); 

    // CEK DULU: Apakah username ini sudah dipakai orang lain?
    $cek_username = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
    if(mysqli_num_rows($cek_username) > 0) {
        echo "<script>alert('Gagal! Username \"$username\" sudah dipakai. Silakan gunakan username lain.'); window.history.back();</script>";
        exit;
    }

    // Jika username tersedia, masukkan ke database
    $query = mysqli_query($koneksi, "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password', '$role')");

    if($query) {
        echo "<script>alert('User baru berhasil ditambahkan!'); window.location='../users.php';</script>";
    } else {
        echo "<script>alert('Gagal menambah user: " . mysqli_error($koneksi) . "'); window.history.back();</script>";
    }
} else {
    // Jika bukan admin yang mencoba akses file ini
    echo "<script>alert('Akses ilegal!'); window.location='../index.php';</script>";
}
?>