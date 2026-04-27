<?php
session_start();
include 'config/koneksi.php';

if(isset($_SESSION['user_id'])) {
    catat_log($koneksi, $_SESSION['user_id'], "Melakukan logout dari sistem");
}

session_destroy();
header("Location: login.php");
exit;
?>