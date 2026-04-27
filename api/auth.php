<?php
session_start();
include '../config/koneksi.php';

$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$password = $_POST['password'];

$query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($query);

if ($user) {
    if (password_verify($password, $user['password'])) {
        $_SESSION['login'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];
        
        $uid = $user['id'];
        $aksi = "Melakukan login ke dalam sistem";
        mysqli_query($koneksi, "INSERT INTO log_aktivitas (user_id, aksi) VALUES ('$uid', '$aksi')");
        
        header("Location: ../index.php");
        exit;
    } else {
        header("Location: ../login.php?pesan=gagal");
        exit;
    }
} else {
    header("Location: ../login.php?pesan=gagal");
    exit;
}
?>